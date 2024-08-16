<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\OrderDeletedEvent;
use Illuminate\Support\Facades\DB;
use Modules\Api\Models\Order;
use Modules\Api\Models\OrderItem;
use Modules\Api\Models\Stock;

class OrderDeletedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(OrderDeletedEvent $event)
    {
        $order = $event->order;
        $items = OrderItem::where("order_id", $order->id)->get();
        foreach ($items as $item) {
            $ingredients = $this->getItemIngredients($item->id);
            foreach ($ingredients as $row) {
                $stock = Stock::where('product_id', $row->item_id)
                                ->where('branch_id', $order->branch_id)
                                ->first();
                $totalQuantity = $item->quantity * $row->quantity;
                $stock->quantity += $totalQuantity;
                $stock->save();
            }
            $item->delete();
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
}
