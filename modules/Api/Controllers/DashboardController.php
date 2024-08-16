<?php
namespace Modules\Api\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Modules\Api\Models\Order;
use Modules\Api\Models\OrderItem;
use Modules\Api\Models\Item;
use Modules\Api\Models\ProductType;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get all charts and card for dashboard
     * @var $yearMonth;
     * @var $year;
     */
    private $yearMonth;
    private $year;

    /**
     * Initialize constructor with selected year
     * @param Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->yearMonth = date('Y-m', strtotime(getSystemDate()));
        $this->year = date('Y', strtotime(getSystemDate()));
        if(!empty($from = $request->get('yearMonth'))){
            $this->yearMonth = date('Y-m', strtotime($from));
            $this->year = date('Y', strtotime($from));
        }
    }

    /**
     * Get Default data for dashboard
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonRequest
     */
    public function index(Request $request)
    {
        $totalOrders = Order::count();
        $totalSalesAmount = Order::where('resolved', 1)->sum('grand_total');
        $paidOrders = Order::where('resolved', 1)->pluck('id');
        $soldItems = OrderItem::whereIn('order_id', $paidOrders)->count();
        return response()->json([
            'status' => 1,
            'total_orders' => $totalOrders,
            'sales_amount' => $totalSalesAmount,
            'sold_items'   => $soldItems,
            'menu_items'   => Item::where('status', 'MENU')->count(),
            'categories_chart' => $this->getCategoriesChart($paidOrders),
            'order_from'       => $this->getOrderFromData(),
            'top_selling'      => $this->getTopSellingItems(),
            'orders_chart'     => $this->getOrdersChart()
        ]);
    }


    /**
     * Calculate revenue
     * @return array
     */
    private function calculateRevenues()
    {
        $totalSalesAmount = Order::where('resolved', 1)->sum('grand_total');
        $orders = Order::where('resolved', 1)->pluck('id');
        $totalSalesUnitPrice = OrderItem::selectRaw('SUM(pos_order_items.quantity * products.price) as unit_price')
                                          ->leftJoin('products', 'pos_order_items.item_id', '=', 'products.id')
                                          ->whereIn('pos_order_items.order_id', $orders)
                                          ->first();
        return compact('totalSalesAmount', 'totalSalesUnitPrice');
    }

    /**
     * For each menu items type, get total sold products
     * @param array $orders 
     * @return array
     */
    private function getCategoriesChart($orders)
    {
        $result = OrderItem::selectRaw('COUNT(DISTINCT(`item_id`)) as freq, product_types.name')
                            ->leftJoin('products', 'pos_order_items.item_id', '=', 'products.id')
                            ->leftJoin('product_types', 'products.type_id', '=', 'product_types.id')
                            //->whereIn('pos_order_items.order_id', $orders)
                            ->groupBy('product_types.id')
                            ->orderBy('freq', 'DESC')
                            ->limit(10)
                            ->get()
                            ->pluck('freq', 'name');
        return $result;
    }

    /**
     * Based on where orders are from, get corresponding data
     * @return array
     */
    private function getOrderFromData()
    {
        $dineIn = Order::where('category', 'DINE IN')->count();
        $takeAway = Order::where('category', 'TAKE AWAY')->count();
        $delivery = Order::where('category', 'DELIVERY')->count();
        return [
            'dine_in'   => $dineIn,
            'take_away' => $takeAway,
            'delivery'  => $delivery
        ];
    }

    /**
     * Get Top selling items
     * @return \Illuminate\Support\Collection
     */
    private function getTopSellingItems()
    {
        $result = OrderItem::selectRaw('products.name, COUNT(pos_order_items.item_id) as freq')
                            ->leftJoin('products', 'pos_order_items.item_id', '=', 'products.id')
                            ->groupBy('pos_order_items.item_id')
                            ->orderBy('freq', 'DESC')
                            ->limit(6)
                            ->get()
                            ->pluck('freq', 'name');
        return $result;
    }

    /**
     * Get Chart of orders through a month
     * @param int $year
     * @param int $month
     * @return array
     */
    private function getOrdersChart($year = NULL, $month = NULL) : array
    {
        $days = getMonthDays(date('m', strtotime($this->yearMonth)), $this->year);
        $result = [];
        $labels = [];
        for($i = 0; $i < count($days); $i++) {
            if($i > 0) $labels[] = sprintf("%02d", $i);
            $result[] = Order::whereDate('system_date', $days[$i])->sum('grand_total');   
        }
        $where = [$days[0], $days[count($days) - 1]];
        return [
            'labels'  => $labels,
            'data'    => $result,
            'total_orders' => Order::whereBetween('system_date', $where)->count(),
            'total_amount' => Order::whereBetween('system_date', $where)->sum('grand_total')
        ];                                                 
    }

    /**
     * Get Chart of sales through a month
     * @param int $year
     * @param int $month
     * @return array
     */
    private function getSalesDashboard($year = NULL, $month = NULL) : array
    {
        $days = getMonthDays(date('m', strtotime($this->yearMonth)), $this->year);
        $result = [];
        $labels = [];
        for($i = 0; $i < count($days); $i++) {
            if($i > 0) $labels[] = sprintf("%02d", $i);
            $result[] = Order::whereDate('system_date', $days[$i])
                                  ->where('resolved', 1)->count();   
        }
        $where = [$days[0], $days[count($days) - 1]];
        return [
            'labels'  => $labels,
            'data'    => $result,
            'total_sales'  => Order::whereBetween('system_date', $where)->count(),
            'total_amount' => Order::whereBetween('system_date', $where)->sum('grand_total')
        ];                                                
    }
}