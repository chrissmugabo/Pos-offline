<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Api\Models\Order;
use Modules\Api\Models\Stock;
use Modules\Api\Models\Requisition;
use Modules\Api\Models\RequisitionItem;
use Modules\Api\Models\ConsumedIngredient;

class OrderCreated implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     /**
     * The order instance.
     * @var \Modules\Api\Models\Order
     * @var array $items
     */

    public $order;
    public $items;
 
    /**
     * Create a new event instance.
     *
     * @param  \Modules\Api\Models\Order  $order
     * @return void
     */
    public function __construct(Order $order, array $items)
    {
        $this->order = $order;
        $this->items = $items;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        $this->handleUsedStockQty($this->items);
    }

    private function handleUsedStockQty(array $items)
    {
        $outStockItems = [];
        // First get every item ingredients and takeway used quantity
        foreach ($items as $item) {
            $ingredients = $this->getItemIngredients($item->id);
            foreach ($ingredients as $row) {
                $stock = Stock::where('product_id', $row->item_id)
                                ->where('branch_id', $this->order->branch_id)
                                ->first();
                if(!$stock) {
                    $stock = new Stock();
                    $stock->product_id = $row->item_id;
                    $stock->branch_id  = $this->order->branch_id;
                    $stock->quantity = 0;
                }
               
                // Calculate actual quantity by ingredient->qty * item->qty
                $itemQty = $item->quantity;
                if (!empty($item->old_quantity)) {
                    // Means it has been edited
                    $itemQty -= $item->old_quantity;
                }
                $totalQuantity = $itemQty * $row->quantity;
                $row->needed_qty = $totalQuantity;
                array_push($outStockItems, $row);
                $stock->quantity -= $totalQuantity;
                $stock->save();
                if (!empty($item->old_quantity)) {
                    $consumbed = ConsumedIngredient::where('item_id', $row->item_id)
                                                 ->where('order_id', $this->order->id)
                                                 ->first();
                    $consumbed->parent_qty += $itemQty;
                    $consumbed->save();
                } else {
                    $currentDateTime = \Carbon\Carbon::now();

                    DB::table('consumed_ingredients')->insert([
                        'date_consumed' => $this->order->created_at,
                        'item_id'   => $row->item_id,
                        'quantity'  => $row->quantity,
                        'order_id'  => $this->order->id,
                        'menu_item_id' => $item->id,
                        'branch_id'    => $this->order->branch_id,
                        'parent_qty'   => $itemQty,
                        'created_at'   => $currentDateTime->format('Y-m-d H:i:s'),
                        'updated_at'   => $currentDateTime->format('Y-m-d H:i:s')
                    ]);
                }
            }
            // Check ingredients for add-ons and register their consumed Qty too.
            if (!empty($item->addons)) {
                $this->handleUsedStockQty($item->addons);
            }
        }
       if(sizeof($outStockItems)) {
            $this->handleAutoRequisitions($outStockItems, "STORABLE", "STOCK");
            $this->handleAutoRequisitions($outStockItems, "DIRECT_USE", "PERISHABLES");
            $this->handleAutoRequisitions($outStockItems, "PRODUCTION",  "PRODUCTION_STOCK");
        }
    }

    /**
     * Get Item Ingredients / Configuraions
     * @param int $itemId
     * @return \Illuminate\Support\Collection 
     */
    private function getItemIngredients(int $itemId)
    {
        return DB::table('configurations')
                   ->selectRaw('configurations.ingredient as item_id, configurations.quantity, products.source, products.status, products.cost * configurations.quantity as price')
                   ->leftJoin('products', 'configurations.ingredient', '=', 'products.id')
                   ->where('product_id', $itemId)
                   ->get();
    }

    /**
     * Get cost price for production items
     * @param string $itemId 
     * @return double
     */
    private function getItemCostPrice($itemId)
    {
        $tariff = 0;
        $ingredients = DB::select("SELECT GROUP_CONCAT(`ingredient`) AS ingredients FROM `configurations` WHERE `product_id` = ? AND `ingredient` IN(SELECT `product_id` FROM `configurations`)", [$itemId]);
        $ingredients = $ingredients[0]->ingredients;
        if($ingredients){
            $items = explode(",", $ingredients);
            $row = DB::table('products')->selectRaw('SUM(products.cost * configurations.quantity) as total_amount')
                        ->join('configurations', 'products.id', '=', 'configurations.ingredient')
                        ->whereIn('configurations.product_id', $items)
                        ->first();
            if(!is_null($row)){
                $data = DB::table('configurations')->selectRaw('SUM(quantity) as total_quantity')
                                                ->whereIn('ingredient', $items)
                                                ->first();
                if(!is_null($data))
                    $tariff = $row->total_amount * $data->total_quantity;
            } 
        }
        
        $result = DB::table('configurations')->selectRaw('SUM(products.cost * configurations.quantity) as total_amount')
                                ->leftJoin('products', 'configurations.ingredient', '=', 'products.id')
                                ->where('configurations.product_id', $itemId)
                                ->first();
        return !is_null($result) ? $result->total_amount + $tariff : $tariff;
    }

    /**
     * Handle automatic requisitions for low stock items
     * @param array $items
     * @param string $status
     * @param string $type
     * @return void
     */
    private function handleAutoRequisitions(array $items, string $status, string $type)
    {
        $finalItems = array_filter($items, function($item) use ($status) {
            return $item->status == $status;
        });
        if(count($finalItems)) {
            if($type == 'PRODUCTION_STOCK') {
                //Get All production sites and filter items to find their belongings
                $productions = DB::table('item_sources')
                                   ->whereNull('deleted_at')
                                   ->get();
                foreach($productions as $production) {
                    $productionItems = array_filter($finalItems, function($item) use ($production) {
                        return $item->source == $production->id;
                    });
                    if(count($productionItems)) {
                        $amount = array_reduce($productionItems, function($a, $b){
                            return $a + ($b->needed_qty * $this->getItemCostPrice($b->item_id));
                        }, 0);
                        $requisition = $this->getPendingRequistion($type, $production->id);
                        $requisition->total_amount += $amount;
                        $requisition->save();
                        foreach($finalItems as $item) {
                            $this->createRequisitionItem($item, $requisition);
                        }
                    }
                }
            } else {
                $amount = array_reduce($finalItems, function($a, $b) {
                    return $a + ($b->needed_qty * $b->price);
                }, 0);
                $requisition = $this->getPendingRequistion($type);
                $requisition->total_amount += $amount;
                $requisition->save();
    
                foreach($finalItems as $item) {
                    $this->createRequisitionItem($item, $requisition); 
                }  
            }
        }
    } 

    /**
     * Check if there is a pending requisition and then return it
     * @param string $type
     * @param $destination
     * @return \Illuminate\Support\Collection
     */
    private function getPendingRequistion(string $type, $destination =  NULL)
    {
        $result = Requisition::where('type', $type)
                      ->where('status', 'PENDING')
                      //->where('committed_date', date('Y-m-d'))
                      ->where('is_for_pos', 1)
                      ->where('branch_id', $this->order->branch_id);
        if(!is_null($destination)) {
            $result->where('destination', $destination);
        }

        $result = $result->first();
        
        if(is_null($result)) {
            $id = Requisition::create([
                        'reference'   => generateOrderCode(8),	
                        'type'        => $type,
                        'committed_date' => date('Y-m-d'),
                        'total_amount' => 0,
                        'status'       => "PENDING",	
                        'is_for_pos'   => 1,
                        'branch_id'    => $this->order->branch_id,
                        'destination'  => $destination,
                        'create_user'  => $this->order->waiter_id ?? $this->order->create_user
                    ])->id;
            $result = Requisition::find($id);
        }

        return $result;
    }

    /**
     * Check whether item already exist on requisitin order and then increase needed quantity
     * if else, create it
     * @param \Illuminate\Support\Collection $item
     * @param \Illuminate\Support\Collection $requisition
     * @return void
     */
    private function createRequisitionItem($item, $requisition)
    {
        $row = RequisitionItem::where('product_id', $item->item_id)
                                ->where('requisition_id', $requisition->id)
                                ->first();
        if($row) {
            $row->requested_quantity += $item->needed_qty;
            $row->save();
        } else {
            RequisitionItem::create([
                'requisition_id' => $requisition->id,
                'product_id'    => $item->item_id,
                'requested_quantity' => $item->needed_qty,
                'items_count'        =>  0,
                'received_quantity'  => 0
            ]);
        }
    }
}