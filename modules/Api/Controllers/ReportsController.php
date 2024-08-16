<?php
namespace Modules\Api\Controllers;

use Illuminate\Http\Request;
use Modules\Api\Models\Order;
use Illuminate\Support\Facades\DB;
use Modules\Api\Models\Payment;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Modules\Api\Models\OrderItem;
use Modules\Api\Models\OrderDestination;

class ReportsController extends Controller
{
    /**
     * @var \Datetime
     */
    private $from;

    /**
     * @var \Datetime
     */
    private $to;

     /**
     * @var $branch
     * For Filtering suppliers
     */
    private $branch;

    /**
     * Set default date as from date when it empty
     */
    public function __construct()
    {
        $this->from = getSystemDate();
        if(!empty($from = \request()->get('from'))) {
            $this->from = $from;
        }
        $this->to = \request()->get('to');
        $this->middleware('auth:api');
        $this->middleware(function ($request, $next) {
            $this->branch = $request->get('branch_id') ?? Auth::user()->branch_id;    
            return $next($request);
        });
    }

    /**
     * Get Sales report with all posible filters
     * @param \Illumninate\Http\Request $request
     * @return \Illumninate\Http\JsonResponse
     */
    public function getSalesReport(Request $request)
    {
        $result = Order::selectRaw('pos_orders.*');
        if ($request->has('category')) {
            $category = $request->get('category');
            if ($category == 'invoiced') {
                $result->where('printed', 1);
            } elseif ($category == 'credited') {
                $result->where(function($query) {
                    $query->whereNotNull('client_id');
                    $query->Where('resolved', 1);
                });
            } 
        } else {
            $result->where(function($query) {
                $query->where('paid', 1);
                $query->orWhere('resolved', 1);
            });
        }
                    
        if ($request->has('waiter_reference')) {
            $waiter = \App\User::getByReference($request->input('waiter_reference'));
        }
        
        if ($request->has('waiter')) {
            $waiter = $request->input('waiter');
        }

        if (!empty($waiter)) {
            $result->where('waiter_id', $waiter);
        }

        if ($request->has('cashier_reference')) {
            $cashier = \App\User::getByReference($request->input('cashier_reference'));
        }
        if ($request->has('cashier')) {
            $cashier = $request->input('cashier');
        }

        //Filter By Cashier who processed the order
        if(!empty($cashier)) {
            $result->where('cashier_id', $cashier);
        }
        
        if ($request->has('client')) {
            $client = $request->input('client');
           if ($client != 'walk-in') {
              $result->where('client_id', $client);
           } else {
              $result->whereNull('client_id');
           }
        }

        if($request->has('is-front')) {
            $user_branch = auth()->user()->branch_id;
            if($user_branch) {
                $result->where('branch_id', $user_branch);
                if ($request->get('is-cashier') != 'true') {
                    $result->where('waiter_id', auth()->id());
                } else {
                    $result->where('cashier_id', auth()->id());
                }
            }
        } else {
            if ($request->get('is-cashier') == 'true') {
                $result->where('cashier_id', auth()->id());
            }
        }
        return response()->json([
            'status' => 1,
            'rows'   => $result->created()->with('payments', 'table', 'waiter', 'branch', 'client', 'cashier')
                                ->groupBy('pos_orders.id')
                                ->orderBy('pos_orders.id', 'DESC')
                                ->paginate($this->recordsLimit())
        ]);
    }

    /**
     * Get Orders Report
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrdersReport(Request $request)
    {
        $result = Order::select('*');
        if ($request->has('waiter_reference')) {
            $waiter = \App\User::getByReference($request->input('waiter_reference'));
        }

        if ($request->has('waiter')) {
            $waiter = $request->input('waiter');
        }

        if (!empty($waiter)) {
            $result->where('waiter_id', $waiter);
        }
        return response()->json([
            'status' => 1,
            'rows'   => $result->created()
                                ->with('table', 'waiter', 'branch', 'client')
                                ->orderBy('id', 'DESC')
                                ->paginate($this->recordsLimit())
        ]);
    }

    /**
     * Get Waiters Balance Report
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWaitersBalance(Request $request)
    {
        $result = Order::where('paid', 0)
                        ->where('resolved', 1)
                        ->whereNull('client_id')
                        ->selectRaw('waiter_id, SUM(`grand_total`) as balance');
        return response()->json([
            'status' => 1,
            'rows'   => $result->created()
                                ->groupBy('waiter_id')
                                ->with('waiter')
                                ->orderBy('balance', 'DESC')
                                ->paginate($this->recordsLimit())
        ]);                
    }

    /**
     * Get waiters Performance Report
     * @param Request $request
     * @return JsonResponse
     */
    public function getWaitersPerformanceReport(Request $request)
    {
        $dates = getMonthBoundaries('first day of this month', 'last day of this month');
        if(!empty($request->get('from'))) {
            $dates = [$request->get('from'), $request->get('to')];
        }
        //SUM(`grand_total`) as balance
        $waiters = \App\User::selectRaw('users.id, users.name, users.first_name, users.last_name')
                             ->whereIn('users.id', function($query) use($dates) {
                                $query->select('waiter_id')
                                      ->from('pos_orders')
                                      ->whereBetween(DB::raw('DATE(system_date)'), $dates)
                                      ->whereNull('deleted_at')
                                      ->where('resolved', 1);
                                if($this->branch) {
                                    $query->where('branch_id', $this->branch);
                                }
                             })->get();
                             
        $waiters->map(function($waiter) use($dates) {

            $waiter->work_days  = DB::table('pos_orders')->whereNull('deleted_at')->where('waiter_id', $waiter->id)
                                        ->whereBetween(DB::raw('DATE(system_date)'), $dates)
                                        ->selectRaw('COUNT(DISTINCT(date(system_date))) as days')
                                        ->first()
                                        ->days;

            $waiter->earnings = Order::where('resolved', 1)
                                       ->where('waiter_id', $waiter->id)
                                       ->whereBetween(DB::raw('DATE(system_date)'), $dates)
                                       ->sum('grand_total');

            $waiter->balance = Order::where('resolved', 1)
                                       ->where('paid', 0)
                                       ->whereNull('client_id')
                                       ->where('waiter_id', $waiter->id)
                                       ->whereBetween(DB::raw('DATE(system_date)'), $dates)
                                       ->sum('amount_remain');
            $waiter->orders = Order::where('resolved', 1)
                                       ->where('waiter_id', $waiter->id)
                                       ->whereBetween(DB::raw('DATE(system_date)'), $dates)
                                       ->count();
            return  $waiter;
        })->all();

        return response()->json([
            'status' => 1,
            'rows'   => $waiters
        ]);
    }

    /**
     * Get Waiters Performance Chart
     * @param Request $request
     * @return JsonResponse
     */
    public function getWaitersPerformanceChart()
    {
        $dates = getMonthBoundaries('first day of this month', 'last day of this month');
        if(!empty($request->get('from'))) {
            $dates = [$request->get('from'), $request->get('to')];
        }
        $days = getDatesBetween($dates[0], $dates[1]);
        // Left coz it can be achieve on front end
    }

    /**
     * Get Cashier Report
     * @param Request $request
     * @return JsonResponse
     */
    public function getCashierReport(Request $request)
    {
        $paymentMethods = Payment::selectRaw('DISTINCT(payments.payment_type) as id, payment_methods.name')
                                ->join('payment_methods', 'payments.payment_type', '=', 'payment_methods.id')
                                ->get();
        $dates = [date('Y-m-d'), date('Y-m-d')];
        if(!empty($request->get('from'))) {
            $dates = [$request->get('from'), $request->get('to')];
        }
        
        $cashiers = \App\User::selectRaw('users.id, users.reference, users.name, users.first_name, users.last_name')
                             ->whereIn('users.id', function($query) use($dates) {
                                $query->select('cashier_id')
                                      ->from('pos_orders')
                                      ->whereBetween(DB::raw('DATE(system_date)'), $dates)
                                      ->whereNull('deleted_at')
                                      ->where('paid', 1);
                                if($this->branch) {
                                    $query->where('branch_id', $this->branch);
                                }
                             })->get();
                             
        $cashiers->map(function($cashier) use($dates, $paymentMethods) {
            $cashier->total_sales = Order::where('paid', 1)
                                        ->where('cashier_id', $cashier->id)
                                        ->whereBetween(DB::raw('DATE(system_date)'), $dates)
                                        ->count();
            $cashier->total_amount = Order::where('paid', 1)
                                        ->where('cashier_id', $cashier->id)
                                        ->whereBetween(DB::raw('DATE(system_date)'), $dates)
                                        ->sum('amount_paid');

            $cashier->payments = $paymentMethods->map(function($mode) use($cashier, $dates) {
                $mode->amount = Order::join('payments', 'pos_orders.id', '=', 'payments.order_id')
                                    ->where('pos_orders.paid', 1)
                                    ->where('pos_orders.cashier_id', $cashier->id)
                                    ->where('payments.payment_type', $mode->id)
                                    ->whereBetween(DB::raw('DATE(pos_orders.system_date)'), $dates)
                                    ->sum('payments.amount_paid');
                return $mode->toArray();
            })->all();
            return  $cashier;
        })->all();

        return response()->json([
            'status' => 1,
            'rows'   => $cashiers
        ]);
    }

    /**
     * Get Items Details report
     * @param Request $request
     * @return JsonResponse
     */
    public function getDetailsReport(Request $request)
    {
        $result = OrderItem::selectRaw('pos_order_items.item_id, SUM(pos_order_items.quantity) as quantity, SUM(pos_order_items.amount) as amount, AVG(pos_order_items.price) as price')
                              ->join('products', 'pos_order_items.item_id', '=', 'products.id')
                              ->leftJoin('product_types', 'products.type_id', '=', 'product_types.id')
                              ->join('pos_orders', 'pos_order_items.order_id', '=', 'pos_orders.id')
                              ->whereNull('pos_orders.deleted_at');
        if (!empty($item = $request->get('item'))) {
            $result->where('item_id', $item);
        }
        if($request->has('items_type')) {
            $status = $request->get('items_type');
            if($status == 'FOOD') {
                $result->where('product_types.type', 'KITCHEN');
            } else if($status == 'DRINKS') {
                $result->where('product_types.type', 'BAR');
            } else if($status == 'ROOMS') {
                $result->where('products.status', 'SERVICE');
            }
        }

        if (empty($this->to)) {
            $result->whereDate('pos_orders.system_date', $this->from);
        } else {
            $result->whereRaw("date(pos_orders.system_date) >= '{$this->from}' and date(pos_orders.system_date) <= '{$this->to}'");
        }

        return response()->json([
            'status' => 1,
            'rows'   => $result
                            ->groupBy('pos_order_items.item_id')
                            ->with('item')
                            ->paginate($this->recordsLimit())
        ]);
    }

    /**
     * Get Payment Received
     * @param Request $request
     * @return JsonResponse
     */
    public function paymentsReceived(Request $request)
    {
        $result = Payment::select('payments.*', 'clients.name as client', 'pos_orders.id as voucher', 'users.name as creator')
                            ->join('pos_orders', 'payments.order_id', '=', 'pos_orders.id')
                            ->leftJoin('clients', 'pos_orders.client_id', '=', 'clients.id')
                            ->leftJoin('users', 'payments.create_user', '=', 'users.id')
                            ->whereNotNull('payments.order_id')
                            ->whereNull('payments.debt_id')
                            ->where('payments.amount_paid', '>', 0);
        if(empty($this->to))
            $result->where('payments.committed_date', $this->from);
        else
            $result->where('payments.committed_date', '>=', $this->from)
                   ->where('payments.committed_date', '<=', $this->to);
        /**
         * Filter by Client
         */
        if (!empty($client = $request->get('client'))) {
            $result->where('pos_orders.client_id', $client);
        }
        
        /**
         * Filter by user
         */
        if (!empty($user = $request->get('user'))) {
            $result->where('payments.create_user', $user);
        }
        /**
         * Filter by payment type
         */
        if (!empty($type = $request->get('type'))) {
            $result->where('payments.payment_type', $type);
        }

        /**
         * Filter by branch
         */
        if ($this->branch) {
            $result->where('pos_orders.branch_id', $this->branch);
        }

        return response()->json([
            'status' => 1,
            'rows'   =>  $result->orderBy('payments.id', 'DESC')
                                ->paginate($this->recordsLimit())
        ]);
    }
    public function trackPaidAfter(Request $request)
    {
        $result = Payment::select('payments.*', 'clients.name as client', 'pos_orders.id as voucher', 'users.name as creator')
                            ->join('pos_orders', 'payments.order_id', '=', 'pos_orders.id')
                            ->leftJoin('clients', 'pos_orders.client_id', '=', 'clients.id')
                            ->leftJoin('users', 'payments.create_user', '=', 'users.id')
                            ->whereNotNull('payments.order_id')
                            ->whereNotNull('payments.debt_id')
                            ->where('payments.amount_paid', '>', 0);
        if(empty($this->to))
            $result->where('payments.committed_date', $this->from);
        else
            $result->where('payments.committed_date', '>=', $this->from)
                   ->where('payments.committed_date', '<=', $this->to);
        /**
         * Filter by Client
         */
        if (!empty($client = $request->get('client'))) {
            $result->where('pos_orders.client_id', $client);
        }
        
        /**
         * Filter by user
         */
        if (!empty($user = $request->get('user'))) {
            $result->where('payments.create_user', $user);
        }
        /**
         * Filter by payment type
         */
        if (!empty($type = $request->get('type'))) {
            $result->where('payments.payment_type', $type);
        }

        /**
         * Filter by branch
         */
        if ($this->branch) {
            $result->where('pos_orders.branch_id', $this->branch);
        }

        return response()->json([
            'status' => 1,
            'rows'   =>  $result->orderBy('payments.id', 'DESC')
                                ->paginate($this->recordsLimit())
        ]);
    }
    
    /**
     * Get Cancellations report with all posible filters
     * @param \Illumninate\Http\Request $request
     * @return \Illumninate\Http\JsonResponse
     */
    public function getCancellationsReport(Request $request)
    {
        $result = Order::selectRaw('pos_orders.*, users.name as destroyer')
                        ->leftJoin('users', 'pos_orders.deleted_by', '=', 'users.id')
                        ->onlyTrashed();            
        if ($request->has('waiter_reference')) {
            $waiter = \App\User::getByReference($request->input('waiter_reference'));
        }
        
        if ($request->has('waiter')) {
            $waiter = $request->input('waiter');
        }

        if (!empty($waiter)) {
            $result->where('waiter_id', $waiter);
        }

        //Filter By Cashier who processed the order
        if(!empty($cashier = $request->input('cashier'))) {
            $result->where('cashier_id', $cashier);
        }
        
        if ($request->has('client')) {
            $client = $request->input('client');
           if ($client != 'walk-in') {
              $result->where('client_id', $client);
           } else {
              $result->whereNull('client_id');
           }
        }
        if($request->has('is-front')) {
            $result->where('branch_id', auth()->user()->branch_id);
            if ($request->get('is-cashier') != 'true') {
                $result->where('waiter_id', auth()->id());
            }
        }
        return response()->json([
            'status' => 1,
            'rows'   => $result->created()->with('table', 'waiter', 'branch', 'items')
                                ->groupBy('pos_orders.id')
                                ->orderBy('pos_orders.id', 'DESC')
                                ->paginate($this->recordsLimit())
        ]);
    }
}