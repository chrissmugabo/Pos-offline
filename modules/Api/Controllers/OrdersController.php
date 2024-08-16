<?php
namespace Modules\Api\Controllers;

use Illuminate\Http\Request;
use App\Jobs\OrderCreated;
use Modules\Api\Models\Stock;
use Modules\Api\Models\Table;
use Modules\Api\Models\Item;
use Modules\Api\Models\Order;
use Modules\Api\Models\Round;
use App\Http\Controllers\Controller;
use Modules\Api\Models\Payment;
use Modules\Api\Models\OrderItem;
use App\Events\OrderCreatedEvent;
use App\Events\OrderDeletedEvent;
use Illuminate\Support\Facades\DB;
use Modules\Api\Models\ProductType;
use Modules\Api\Models\Requisition;
use Modules\Api\Models\Debt;
use Modules\Api\Models\RequisitionItem;
use Modules\Api\Models\PrintedRound;
use Modules\Api\Models\ConsumedIngredient;
use Modules\Api\Models\OrderDestination;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class OrdersController extends Controller
{
    private $systemDate;
    public function __construct()
    {
        $this->systemDate = getSystemDate();
        $this->middleware('auth:api', ['except' => [
                            'getNextOrderToPrint',
                            'updatePrintedRound'
                        ]
                    ]);
    }
    /**
     * List all Orders
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $result = Order::select('id', 'reference', 'category', 'system_date', 'order_time', 'table_id', 'waiter_id', 'grand_total', 'status', 'printed', 'client_id', 'branch_id', 'receipts', 'resolved', 'paid')
                         ->where('paid', 0)
                         ->where('resolved', 0)
                         ->where('status', '!=', 'DENIED');
        if($request->get('no_rooms')) {
            $result->whereNull('room_id');
        }
        if(!empty($latest = $request->get('latest'))) {
            $result->where('id', '>', $latest);
        }
        return response()->json([
            'status' => 1,
            'latest' => Order::max('id'),
            'rows'   => $result->with('table', 'waiter', 'client')
                                ->orderBy('id', 'DESC')
                                ->get()
        ]);
    }

    /**
     * Place a new Order
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
     public function store(Request $request)
     {
        $user = $this->currentUser();
        $branch = $user->branch_id;
        if(!$branch) {
            $waiter = \App\User::find($request->input('waiter_id'));
            $branch = $waiter->branch_id;
        }
        if(!$branch) {
            $branch = \request()->get('current_branch');
        }

        $roomId   = $request->input('room_id');
        $clientId = $request->input('client_id');
        if (empty($request->input('order_id'))) {
            /*$roomOrder = Order::where('client_id', $clientId)->where('room_id', $roomId)->where('resolved', 0)->where('category', 'ROOM SERVICES')->first();
            if($roomOrder) {
                $order = $roomOrder;
                $order->status = 'PENDING';
                $order->amount      += $request->input('amount');
                $order->discount    = $request->input('discount') ?? 0;
                $order->grand_total += $request->input('grand_total');
                $order->updated = 1;
                $order->save();
            } else { */
                $orderId = Order::create([
                    'system_date' => $this->systemDate,
                    'order_time'  => date('H:i:s'),
                    'reference'   => generateOrderCode(8),	
                    'table_id'    => $request->input('table_id'),	
                    'waiter_id'   => $request->input('waiter_id'),	
                    'amount'      => $request->input('amount'),	
                    'discount'    => $request->input('discount') ?? 0,	
                    'grand_total' => $request->input('grand_total'),	
                    'status'      => 'PENDING',
                    'paid'      => 0,	
                    'printed'   => 0,
                    'resolved'  => 0,
                    'amount_paid'   => 0,
                    'amount_remain' => 0,
                    'create_user' => $user->id,	
                    'client_id' => $clientId,
                    'room_id'	=> $roomId,
                    'branch_id' => $branch,	
                    'comment'   => $request->input('comment'),	
                    'category'  => $request->input('category')	
                ])->id;
                $order = Order::where('id', $orderId)->where('branch_id', $branch)->first();
            //}
        } else {
            $order = Order::find($request->input('order_id'));
            $order->status = 'PENDING';
            $order->amount      = $request->input('amount');
            $order->discount    = $request->input('discount') ?? 0;
            $order->grand_total = $request->input('grand_total');
            $order->updated = 1;
            $order->save();
        }

        $barItems = json_decode($request->input('bar_items'));
        $kitchenItems = json_decode($request->input('kitchen_items'));
        $roundKey = DB::table('pos_order_items')->whereNull('pos_order_items.deleted_at')->max('round_key') ?? 0;
        $orderCode = $roundKey + 1;
        $this->handleOrderDestination($order, 'BAR', $barItems, $orderCode);
        $this->handleOrderDestination($order, 'KITCHEN', $kitchenItems, $orderCode);
        //if(!$request->has('disable_print') || $request->input('disable_print') != 1) {
            if(sizeof($barItems)) {
                $this->storeRoundRefererence($order, $orderCode, 'BAR');
            }
            if(sizeof($kitchenItems)) {
                $this->storeRoundRefererence($order, $orderCode, 'KITCHEN');
            }
        //}
        if($request->has('print_round_slip')) {
            Round::create([
                'category'  => 'ROUND_SLIP',
                'order_id'  => $order->id,
                'branch_id'	=> $order->branch_id,
                'round_no'  => $orderCode,	
                'printed'   => 0
            ]);
        }
        event(new OrderCreatedEvent($order, array_merge($barItems, $kitchenItems)));
        return response()->json([
            'status'    => 1
        ]);
     }

     /**
      * Add order item based on destination (Kitchen or Bar)
      * @param int $orderId
      * @param string $destination
      * @param array $items
      * @return void
      */
     private function handleOrderDestination($order, $destination, array $items, $roundKey) : void
     {
        foreach($items as $item)
        {
            $price = $item->price ?? $item->cost;
            $quantity = $item->quantity;
            if (!empty($item->old_quantity)) {
                $quantity -= $item->old_quantity;
            }
            
            $row = OrderItem::create([
               'order_id' => $order->id,
               'item_id'  => $item->id,
               'comment'  => $item->comment ?? NULL,
               'quantity' => $quantity,
               'price'    => $price,
               'amount'   => $quantity * $price,
               'destination' => $destination,
               'round_key'   => $roundKey,
               'delivered'   => 0,
               'parent_id'   => 0
            ]);  
            
            if (!empty($item->addons)) {
                foreach ($item->addons as $addon) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id'  => $addon->id,
                        'comment'  => $addon->comment ?? NULL,
                        'quantity' => 1,
                        'price'    => 0,
                        'amount'   => 0,
                        'parent_id'   => $row->id
                     ]);  
                }
            }
        }
     }

     /**
      * Handle item addons
      */
      private function handleItemAddons(array $addons)
      {
        
      }

    /**
     * Show Destination orders
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
     public function showDestinationOrders(Request $request)
     {
        $destination = $request->get('destination');
        $latest = $request->get('latest');
        $result = OrderDestination::select('id', 'reference', 'category', 'system_date', 'order_time', 'table_id', 'waiter_id')
                                    ->whereDate('system_date', $this->systemDate)
                                    ->where('status', 'PENDING')
                                    ->whereIn('id', function($q) use($destination) {
                                        $q->select('order_id')
                                          ->from('pos_order_items')
                                          ->where('destination', $destination)
                                          ->where('delivered', 0);
                                        if(!empty($latest)) {
                                            $q->where('id', '>', $latest);
                                        }
                                    })->with('table', 'waiter')->get();
        return response()->json($result);
     }

     /**
      * Get Items with the same round Key
      * @param int $orderId
      * @param string $roundKey
      * @return \Illuminate\Support\Collection
      */
     private function getRoundItems($orderId, $roundKey)
     {
        $destination = \request()->get('destination');
        return OrderItem::select('pos_order_items.*', 'products.name')
                    ->leftJoin('products', 'pos_order_items.item_id', '=', 'products.id')
                    ->where('pos_order_items.destination', $destination)
                    ->where('pos_order_items.order_id', $orderId)
                    ->where('pos_order_items.round_key', $roundKey)
                    ->get();
     }

     /**
     * Handle Order Payment
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handlePayment(Request $request)
    {
        $order = Order::findOrFail($request->input('order'));
        if(is_null($order)) {
            return response()->json([
                'status' => 0,
                'error'  => 'Order not found.'
            ], 404);
        }

        $amountPaid = $request->input('amount_paid');
        $payment = Payment::create([
            'committed_date' => $this->systemDate,	
            'order_id'       => $order->id,
            'payment_type'   => $request->input('payment_method'),	
            'account_id'     => $request->input('account_id'),
            'amount_paid'    => $amountPaid,	
            'comment'        => $request->input('comment'),
            'reference'      => $request->input('payment_ref'),
            'create_user'    => $this->currentUser()->id
        ]);

        handleAccountBalance($request->input('account_id'), $amountPaid, ['reference_id' => $payment->id, 'type' => PAYMENT_RECEIVED, 'origin' => 'pos',  'transaction_date' => $payment->committed_date]);
        $resolved    = $request->input('amount_remain') == 0 ? 1 : 0;
        $order->amount_paid += $amountPaid;
        $order->amount_remain -= $amountPaid;
        $order->paid = $resolved;
        $order->resolved = $resolved;
        if($resolved) {
            $order->status = 'DELIVERED';
            // After saving order and payment, update its items.
            $items = OrderItem::where('order_id', $order->id)->get();
            foreach($items as $item) {
                $item->delivered = 1;
                $item->save();
            }
        }
        $order->cashier_id = $this->currentUser()->id;
        $order->save();
        return response()->json([
            'status'  => 1
        ]);
    }

    /**
     * Deliver one item on orders list
     * @param int $orderId
     * @param int $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deliverItem($orderId, $itemId)
    {
        $item = OrderItem::where('order_id', $orderId)
                            ->where('id', $itemId)
                            ->first();
        if(is_null($item)) {
            return response()->json([
                'status' => 0,
                'error'  => 'Item not found'
            ], 404);
        }
        $item->delivered = 1;
        $item->save();
        return response()->json([
            'status'  => 1
        ]);
    }

    /**
     * Delete Order Item
     * @param $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteOrderItem($itemId)
    {
        $item = OrderItem::findOrFail($itemId);
        if(!is_null($item)) {
            $order = Order::find($item->order_id);
            $itemTotal = $item->amount - (($item->amount * $order->discount) / 100);
            $order->grand_total -= $itemTotal;
            $order->save();  
            $item->delete();
        }
        return response()->json([
            'status'  => 1
        ]);
    }

    /**
     * List all Orders that have been paid (Sales)
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sales(Request $request)
    {
        $result = Order::select('pos_orders.id', 'pos_orders.reference', 'pos_orders.category', 'pos_orders.system_date', 'pos_orders.order_time', 'pos_orders.table_id', 'pos_orders.waiter_id', 'pos_orders.grand_total', 'pos_orders.paid', 'pos_orders.status', 'payments.payment_type', 'pos_orders.branch_id', 'pos_orders.client_id', 'pos_orders.room_id', 'pos_orders.resolved', 'pos_orders.receipts')
                        ->leftJoin('payments', 'pos_orders.id', '=', 'payments.order_id')
                        ->where(function($query){
                            $query->where('pos_orders.paid', 1);
                            $query->orWhere('pos_orders.resolved', 1);
                        });
                         
        return response()->json([
            'status' => 1,
            'rows'   => $result->with('table', 'waiter', 'items', 'branch', 'client', 'room')
                                ->orderBy('id', 'DESC')
                                ->paginate($this->recordsLimit())
        ]);
    }

    /**
     * Deliver order based on destination
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deliverOrder(Request $request)
    {
        $order = Order::find($request->input('order_id'));
        $destination = $request->input('destination');
        $items = OrderItem::where('order_id', $order->id)
                            ->where('destination', $destination)
                            ->get();

        foreach($items as $item) {
            $item->delivered = 1;
            $item->save();
        }

        /* Check if other destination has delivered and then update order status
        $row = OrderItem::where('order_id', $order->id)
                            ->where('destination', '!=' ,$destination)
                            ->first();

        if(!is_null($row)) {
            if($row->delivered == 1) {
                $order->status = 'DELIVERED';
                $order->save();
            }
        } else {
            //If order has one destination, the set status to DELIVERED
            $order->status = 'DELIVERED';
            $order->save();
        } */
        return response()->json([
            'status'  => 1
        ]);
    }

    /**
     * List waiter orders based on current day
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showWaiterOrders(Request $request)
    {
        $result = Order::selectRaw('id, reference, category, system_date, order_time, table_id, grand_total, printed, paid, status, waiter_id, client_id, room_id, resolved, receipts')
                         ->whereDate('system_date', $this->systemDate);
        if(!$request->has('is-cashier') || $request->get('is-cashier') != "true") {
            $result->where('waiter_id', $this->currentUser()->id);
        }
        if ($request->has('paid')) {
            $result->where('paid', $request->get('paid'))
                   ->where('resolved', 0);
        }
        if(!empty($latest = $request->get('latest'))) {
            $result->where('id', '>', $latest);
        }
        return response()->json([
            'status' => 1,
            'rows'   => $result->with('table', 'waiter', 'client', 'room')
                                ->orderBy('id', 'DESC')
                                ->paginate($this->recordsLimit())
        ]);
    }

    /**
     * Get order items for editing
     * @param string $reference
     * @return \Illuminate\Http\JsonResponse
     */
    public function showOrderItems($reference) 
    {
        $order = Order::where('reference', $reference)
                        ->select('id', 'reference', 'table_id', 'amount', 'category')
                        ->with('table')
                        ->first();
        if(is_null($order)) {
            return response()->json([
                'status' => 0,
                'error'  => 'Order not found'
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'record' => $order,
            'items'  => OrderItem::selectRaw('SUM(pos_order_items.quantity) as quantity, SUM(pos_order_items.quantity) AS old_quantity, pos_order_items.price, pos_order_items.destination AS `group`, SUM(pos_order_items.amount) as amount, pos_order_items.round_key, products.id, products.name, product_types.name As type, units.name As unit, pos_order_items.comment')
                                   ->join('products', 'pos_order_items.item_id', '=', 'products.id')
                                   ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                                   ->leftJoin('product_types', 'products.type_id', '=', 'product_types.id')
                                   ->where('pos_order_items.order_id', $order->id)
                                   ->where('parent_id', 0)
                                   ->groupBy('pos_order_items.item_id')
                                   ->get()
        ]);
    }

    /**
     * Get order items by id
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function showOrderItemsById(int $orderId) 
    {
        $result = DB::table('pos_order_items')->selectRaw('SUM(pos_order_items.quantity) as quantity, pos_order_items.item_id, pos_order_items.price, SUM(pos_order_items.amount) as amount, pos_order_items.id, products.name, pos_order_items.comment')
                        ->join('products', 'pos_order_items.item_id', '=', 'products.id')
                        ->where('pos_order_items.order_id', $orderId)
                        ->where('parent_id', 0)
                        ->groupBy('pos_order_items.item_id')
                        ->get();
        return response()->json($result);
    }

    /**
     * Print order invoice and make it as print by waiter
     * @param int $orderId
     * @return \Illumninate\Http\JsonResponse
     */
    public function handleOrderPrint($orderId)
    {
        $order = Order::findOrFail($orderId);
        if(!$order) {
            return response()->json([
                'status' => 0,
                'error'  => 'Order not found. Try again'
            ], 404);
        }
        $order->printed = 1;
        //$order->resolved = 1;
        $order->status = 'DELIVERED';
        $order->save();
        return response()->json([
            'status'  => 1,
        ]);
    }

    /**
     * Check if there are yesterday's pending orders
     * then, restrict waiters from making orders
     * @param \Illumninate\Http\Request
     * @return \Illumninate\Http\JsonResponse
     */
    public function getUnpaidOrders(Request $request)
    {
        $result = Order::whereDate('system_date', $this->systemDate)
                         ->where(function($query) {
                            $query->where("paid", 0);
                            $query->where("status", 'PENDING');
                         });
        if(!empty($branch)) {
            $result->where('branch_id', $branch);
        }
        return response()->json([
            'status' => 1,
            'orders' => $result->with('table', 'waiter', 'items')
                               ->orderBy('id', 'DESC')
                               ->get()
        ]);
    }

    /**
     * Assign Client to an order
     * @param \Illumninate\Http\Request
     * @return \Illumninate\Http\JsonResponse
     */
    public function assignClient(Request $request)
    {
        $order  = Order::findOrFail($request->input("order_id"));
        if(!$order) {
            return response()->json([
                'status' => 0,
                'error'  => 'Order not found' 
            ], 404);
        }
        if($request->has('client_id')) {
            $client = $request->input('client_id');
            $order->client_id = $client;
            Debt::create([
                'date_taken' => $order->system_date,
                'origin'     => 'POS',	
                'origin_id'  => $order->id,
                'amount'     => $order->grand_total - $order->amount_paid,
                'client_id'  => $client
            ]);
        }
        $order->status = "DELIVERED";
        $order->amount_remain = $request->input('amount_remain');
        $order->resolved = 1;
        $order->save();
        return response()->json([
            'status'  => 1
        ]);
    }

    /**
     * Delete order
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $id = $request->input('order_id');
        $comment = $request->input('comment');
        $order = Order::findOrFail($id);
        if(!$order) {
            return response()->json([
                'status' => 0,
                'error'  => 'Order not found' 
            ], 404);
        }
        if($order->amount_paid > 0) {
            $payments = Payment::where('order_id', $order->id)->get();
            foreach($payments as $payment) {
                handleAccountBalance($payment->account_id, -$payment->amount_paid, ['reference_id' => $payment->id, 'type' => PAYMENT_RECEIVED, 'origin' => 'pos', 'transaction_date' => $payment->committed_date]);
                $payment->delete();
            }
        }
        // Delete debt associated to this sales when available
        $debt = Debt::where('origin', 'POS')->where('origin_id', $order->id)->first();
        if($debt) {
            $debt->delete();
        }
        /**
         * Delete sent requisition too
         * $items = OrderItem::where('order_id', $order->id)->get();
         */
        $order->comment = $comment;
        $order->deleted_by = auth()->id();
        $order->save();

        ConsumedIngredient::where('order_id', $order->id)->delete();

        $order->delete();
        event(new OrderDeletedEvent($order));
        return response()->json([
            'status'  => 1
        ]);
    }

    /**
     * Get Waiter's unpaid orders
     * @param int $waiterId
     * @return JsonResponse
     */
    public function getWaiterAssignedOrders($waiterId)
    {
        return response()->json([
            'status' => 1,
            'rows'   => Order::where('paid', 0)
                            ->where('resolved', 1)
                            ->whereNull('client_id')
                            ->where('waiter_id', $waiterId)
                            ->orderBy('id', 'DESC')
                            ->get()
        ]);
    }

    /**
     * Store reference to the placed rounds
     * @param \Modules\Api\Models\Order $order
     * @param int $roundNo
     * @param string $destination
     * @return void
     */
    private function storeRoundRefererence(Order $order, int $roundNo, string $destination)
    {
        Round::create([
            'category'  => 'ORDER',
            'order_id'  => $order->id,
            'branch_id'	=> $order->branch_id,
            'round_no'  => $roundNo,	
            'destination' => $destination,	
            'printed'     => 0
        ]);
    }

    /**
     * Store Printable Order
     * @param Request $request
     * @return JsonResponse
     */
    public function storePrintableOrder(Request $request)
    {
        //$order = Order::find();
        $order = DB::table('pos_orders')->where('id', $request->input('order_id'))->first();
        $currentDateTime = \Carbon\Carbon::now();
        $data = [
            'category'  => $request->input('category'),
            'order_id'  => $request->input('order_id'),	
            'branch_id' => $order->branch_id,
            'printed'   => 0,
            'created_at'   => $currentDateTime->format('Y-m-d H:i:s'),
            'updated_at'   => $currentDateTime->format('Y-m-d H:i:s')
        ];
        if($request->has('items')) {
            $data['items'] = json_decode($request->input('items'));
        }
        //Round::create($data);
        DB::table('pos_rounds')->insert($data);
        return response()->json([
            'status' => 1
        ]);
    }

    /**
     * Get Pending round to print
     * @return JsonResponse
     */
    public function getNextOrderToPrint()
    {
        $today = date('Y-m-d');
        $branch  = \request()->get('branch_id');
        $content = \request()->get('content');
        $latest  = \request()->get('latest');
        $round = DB::table('pos_rounds')->where('branch_id', $branch)
                        ->whereDate('created_at', $today)
                        ->where('printed', 0)
                        ->whereNull('deleted_at');

        if(!empty($latest)) {
            //$round->where('id', '>', $latest);
            DB::table('pos_rounds')->where('id', $latest)->update(['printed' => 1]);
        }

        if(!empty($content)) {
            // KBI: K-> Kitchen orders, B: Bar orders, I: invoices
            if(strlen($content) === 1) {
                switch ($content) {
                    case 'K':
                        $round->where('category', 'ORDER')->where('destination', 'KITCHEN');
                        break;
                    case 'B':
                        $round->where('category', 'ORDER')->where('destination', 'BAR');
                        break;
                    case 'I':
                        $round->where('category', 'INVOICE');
                        break;
                    default:
                        # code...
                        break;
                }
            } elseif(strlen($content) === 2) {
                // Kicthek & BAR
                if($content === 'KB' || $content === 'BK'){
                    $round->where('category', 'ORDER');
                } elseif($content === 'KI' || $content === 'IK') { // Kitchen & Invoices
                    $round->where(function($q) {
                        $q->where('destination', 'KITCHEN')
                          ->Orwhere('category', 'INVOICE');
                    });        
                } elseif($content === 'BI' || $content === 'IB') { // BAR & Invoices
                    $round->where(function($q) {
                        $q->where('destination', 'BAR')
                          ->Orwhere('category', 'INVOICE');
                    });  
                }
            }
        }
                   
        $round = $round->first();

        if($round) {
            $items = DB::table('pos_order_items')
                        ->selectRaw('pos_order_items.quantity, pos_order_items.price, pos_order_items.amount, products.name, pos_order_items.comment')
                        ->leftJoin('products', 'pos_order_items.item_id', '=', 'products.id')
                        ->where('pos_order_items.order_id', $round->order_id)
                        ->whereNull('pos_order_items.deleted_at');

            if($round->category === 'ORDER') {
                $items->where('pos_order_items.destination', $round->destination)
                      ->where('pos_order_items.round_key', $round->round_no);
            } elseif($round->category === 'ROUND_SLIP') {
                $items->where('pos_order_items.round_key', $round->round_no);
            }
            
            $items = $items->get();
            
            $order = DB::table('pos_orders')
                        ->selectRaw('pos_orders.id, pos_orders.category, pos_orders.system_date, pos_orders.order_time, pos_orders.grand_total, pos_tables.name AS table_name, users.name AS waiter, clients.name AS client')
                        ->leftJoin('pos_tables', 'pos_orders.table_id', '=', 'pos_tables.id')
                        ->leftJoin('users', 'pos_orders.waiter_id', '=', 'users.id')
                        ->leftJoin('clients', 'pos_orders.client_id', '=', 'clients.id')
                        ->where('pos_orders.id', $round->order_id)
                        ->whereNull('pos_orders.deleted_at')
                        ->first();

            if($round && $order && sizeof($items)) {
                return response()->json([
                    'status' => 1,
                    'round'  => $round,
                    'order'  => $order,
                    'items'  => $items,
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'round'  => $round
                ]);
            }
        } else {
            return response()->json([
                'status' => 0
            ]);
        }
    }

    /**
     * Get Pending round to print
     * @param int $latest
     * @return JsonResponse
     */
    public function updatePrintedRound(int $id)
    {
        DB::table('pos_rounds')->where('id', $id)->update(['printed' => 1]);
        return response()->json([
            'status' => 1
        ]);
    }
    /**
     * Get Orders For Reprint Purpose
     * @param int $id
     * @return JsonResponse
     */
    public function getOrderRounds(int $id)
    {
        $rounds = Round::where('order_id', $id)->get();
        $rounds->map(function($round) {
            $items = OrderItem::select('pos_order_items.*', 'products.name')
                                    ->leftJoin('products', 'pos_order_items.item_id', '=', 'products.id')
                                    ->where('pos_order_items.order_id', $round->order_id);
                if($round->category == 'ORDER') {
                    $items->where('pos_order_items.destination', $round->destination)
                          ->where('pos_order_items.round_key', $round->round_no);
                }
            $round->items = $items->get();
            return $round;
        })->all();

        return response()->json([
            //'order'  => $order,
            'rounds' => $rounds
        ]);
    }

    /**
     * Get multiple orders
     * @param string $vouchers
     * @return JsonResponse
     */

    public function getVouchers(string $vouchers)
    {
        $ids = explode(",", $vouchers);
        return response()->json([
            'status'   => 1,
            'vouchers' => Order::whereIn('id', $ids)->with('client', 'waiter', 'items', 'table')->get()
        ]);
    }

    /**
     * Pay for multiple orders
     * @param Request $request
     * @return JsonResponse
     */
    public function handleBulkPayment(Request $request)
    {
        $items  = json_decode($request->input('items'));
        foreach($items as $item) {
            $voucher = Order::findOrFail($item->id);
            if(!empty($voucher)) {
                if($voucher->amount_remain > 0)
                {
                    $voucher->amount_paid += $item->amount;
                    $voucher->paid = $voucher->amount_remain <= $item->amount;
                    $voucher->amount_remain -= $item->amount;
                    $voucher->save();

                    //Update Taken Debt if it is available
                    $debt = Debt::where('origin', 'POS')->where('origin_id', $voucher->id)->first();
                    if($debt) {
                        $debt->paid += $item->amount;
                        $debt->save();
                        updateCustomerBalance([
                            'client_id' => $debt->client_id,
                            'balance'   => $item->amount,
                            'operation'   => '-',
                            'system_date' => $request->input('committed_date')
                        ]);
                    }

                    $payment = Payment::create([
                        'committed_date' => $request->input('committed_date'),	
                        'account_id'     => $request->input('account_id'),	
                        'order_id' => $voucher->id,
                        'payment_type'   => $request->input('payment_type'),	
                        'amount_paid'    => $item->amount ?? 0,	
                        'comment'        => "PARTIAL PAYMENT FOR PRODUCTION SALE",
                        'reference'      => $request->input('payment_ref'),
                        'create_user'    => auth()->id(),
                        'debt_id'        => $debt ? $debt->id : NULL
                    ]);
                    handleAccountBalance($request->input('account_id'), $item->amount ?? 0, ['reference_id' => $payment->id, 'type' => PAYMENT_RECEIVED, 'origin' => 'pos', 'transaction_date' => $payment->committed_date]);
                }
            }
        }
        return response()->json([
            'status'  => 1
        ]);
    }

    /**
     * Delete recorded payments
     * @param array $ids
     * @return JsonResponse
     */
    public function deletePayments($ids)
    {
        $ids = explode(",", $ids);
        $rows = Payment::whereIn('id', $ids)->get();
        foreach($rows as $row){
            $voucher = Order::findOrFail($row->transaction_id);
            handleAccountBalance($row->account_id, -$row->amount_paid, ['reference_id' => $row->id, 'type' => PAYMENT_RECEIVED, 'origin' => 'pos',  'transaction_date' => $row->committed_date]);
            if($voucher){
                $voucher->amount_paid -= $row->amount_paid;
                $voucher->amount_remain += $row->amount_paid;
                $voucher->paid = 0;
                $voucher->save();
                //Update Taken Debt if it is available
                $debt = Debt::where('origin', 'POS')->where('origin_id', $voucher->id)->first();
                if($debt) {
                    updateCustomerBalance([
                        'client_id' => $debt->client_id,
                        'balance'   => $row->amount_paid,
                        'operation'   => '+',
                        'system_date' => $row->committed_date
                    ]);
                    $debt->delete();
                }
            }
            $row->delete();
        }

        return response()->json([
            'status'  => 1
        ]);
    }

    /**
     * Delete order item
     * @param int OrderId
     * @param int itemId
     * @return JsonResponse
     */
    public function deleteItem($orderId, $itemId)
    {
        $order = Order::find($orderId);
        if ($order) {
            //$payment = Payment::where('order_id', $order->id)->first();
            //handleAccountBalance($payment->account_id, -$payment->amount_paid, ['reference_id' => $payment->id, 'type' => PAYMENT_RECEIVED, 'origin' => 'pos',  'transaction_date' => $payment->committed_date]);
            $item = OrderItem::where('order_id', $order->id)
                               ->where('item_id', $itemId)
                               ->first();
            $order->grand_total -= $item->amount;
            $order->amount -= $item->amount;
            $order->save();
            $item->delete();
            
            //Update Taken Debt if it is available
            $debt = Debt::where('origin', 'POS')->where('origin_id', $order->id)->first();
            if($debt) {
                $debt->amount -= $item->amount;
                if($debt->paid) {
                    $debt->paid -= $item->amount;
                }
                $debt->save();
                updateCustomerBalance([
                    'client_id' => $debt->client_id,
                    'balance'   => $item->amount,
                    'operation'   => '+',
                    'system_date' => $order->system_date
                ]);
            }
        }
        return response()->json([
            'status'  => 1
        ]);
    }

    /**
     * Split order receipts
     * @param Request $request
     * @return JsonResponse
     */
    public function splitOrder(Request $request, $orderId) 
    {
        $order = Order::find($orderId);
        $receipts = json_decode($request->input('receipts'));
        $order->receipts = $receipts;
        $order->save();
        return response()->json([
            'status'  => 1
        ]);
    }

    /**
     * Handle Order Spliting
     * @param Request $request
     * @return JsonResponse
     */
    public function splitOrderItems(Request $request, $orderId)
    {
        $order = Order::find($orderId);
        //$order->status = 'DELIVERED';
        $order->amount      -= $request->input('amount');
        $order->discount    -= $request->input('discount') ?? 0;
        $order->grand_total -= $request->input('grand_total');
        $order->updated = 1;
        $order->save();
        $splittedItems = json_decode($request->input('items'));
        $newOrder = Order::create([
            'system_date' => $order->system_date,
            'order_time'  => $order->order_time,
            'reference'   => generateOrderCode(8),	
            'table_id'    => $order->table_id,	
            'waiter_id'   => $order->waiter_id,	
            'amount'      => $request->input('amount'),	
            'discount'    => $request->input('discount') ?? 0,	
            'grand_total' => $request->input('amount'),	
            'status'      => 'DELIVERED',
            'paid'      => 0,	
            'printed'   => 1,
            'resolved'  => 0,
            'amount_paid'   => 0,
            'amount_remain' => 0,
            'create_user' => $order->create_user,	
            'client_id' => $order->client_id,	
            'branch_id' => $order->branch_id,		
            'category'  => $order->category
        ])->id;
        foreach($splittedItems as $item) {
            //$row = OrderItem::where('order_id', $order->id)->where('item_id', $item->item_id)->first();
            $row = OrderItem::find($item->id);
            $quantity = $row->quantity - $item->quantity;
            $row->quantity = $quantity;
    
            OrderItem::create([
               'order_id' => $newOrder,
               'item_id'  => $item->item_id,
               'quantity' => $item->quantity,
               'price'    => $row->price,
               'amount'   => $item->quantity * $row->price,
               'destination' => $row->destination,
               'round_key'   => $row->round_key,
               'delivered'   => 1
            ]); 
            if($quantity <= 0) {
                //$row->forceDelete();
                $row->delete();
            } else {
                $row->save();
            }
        }

        Round::create([
            'category'  => 'INVOICE',
            'order_id'  => $newOrder,	
            'branch_id' => $order->branch_id,
            'printed'   => 0
        ]);
        
        return response()->json([
            'success' => true,
            'row'     => Order::selectRaw('id, reference, category, system_date, order_time, table_id, grand_total, printed, paid, status, waiter_id, client_id, room_id, resolved, receipts')
                                ->with('table', 'waiter', 'items', 'client')
                                ->where('id', $order->id)
                                ->first(),
        ]);
    }
 
    private function handleUsedStockQty(Order $order, array $items)
    {
        foreach ($items as $item) {
            $ingredients = $this->getItemIngredients($item['id']);
            foreach ($ingredients as $row) {
                $stock = Stock::where('product_id', $row->item_id)
                                ->where('branch_id', $order->branch_id)
                                ->first();
                if(!$stock) {
                    $stock = new Stock();
                    $stock->product_id = $row->item_id;
                    $stock->branch_id  = $order->branch_id;
                    $stock->quantity = 0;
                }
               
                $itemQty = $item['quantity'];
                $totalQuantity = $itemQty * $row->quantity;
                $stock->quantity -= $totalQuantity;
                $stock->save();

                $currentDateTime = \Carbon\Carbon::now();
                
                DB::table('consumed_ingredients')->insert([
                    'date_consumed' => $order->system_date,
                    'item_id'   => $row->item_id,
                    'quantity'  => $row->quantity,
                    'order_id'  => $order->id,
                    'menu_item_id' => $item['id'],
                    'branch_id'    => $order->branch_id,
                    'parent_qty'   => $itemQty,
                    'created_at'   => $currentDateTime->format('Y-m-d H:i:s'),
                    'updated_at'   => $currentDateTime->format('Y-m-d H:i:s')
                ]);
            }
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
     * Get Bourbon Branch
     * @param int $id
     * @return int
     */
    private function getBourbonBranch($group, $id)
    {
        if($group == 'A') {
            switch($id) {
                case 2:
                    return 1;
                    break;
                case 3:
                    return 8;
                    break;
                case 8:
                    return 7;
                    break;
                case 9:
                    return 6;
                    break;
                case 10:
                    return 5;
                    break;
                case 6:
                    return 3;
                    break;
                case 7:
                    return 16;
                    break;
            }
        } else {
            switch($id) {
                case 8:
                    return 9;
                    break;
                case 9:
                    return 10;
                    break;
                case 11:
                    return 4;
                    break;
                case 14:
                    return 16;
                    break;
                case 15:
                    return 3;
                    break;
            }
        }
    }
}

