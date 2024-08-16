<?php
namespace Modules\Api\Controllers;

use Illuminate\Http\Request;
use Modules\Api\Models\EOD;
use Modules\Api\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function getAppSettings()
    {
        $today = date('Y-m-d');
        $systemDate = getSystemDate();
        $rows = Cache::rememberForever('core_settings', function () {
            return Setting::all()->pluck('value', 'key');
        });
        return response()->json([
            'status'   => 1, 
            'timezone' => config('app.timezone'),
            'pos_env'  => config('app.pos_env'),
            'sync_url' => config('app.sync_url'),
            'branch'   => isOffline() ? config('app.offline_branch_id') : NULL,
            'today'   => $systemDate,
            'result'  => (object)$rows,
            'system_date'  => $systemDate,
            'invalid_date' => new \DateTime($today) > new \DateTime($systemDate)
        ]);
    }

    /**
     * Close a day  record unpaid orders
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleEOD(Request $request)
    {
        $yesterday = \Carbon\Carbon::yesterday()->toDateString();
        $unpaidOrders =  $this->getUnResolvedOrder();
        $branch = $this->getCurrentBranch();
        $lastEOD = DB::table('pos_end_of_days')
                            ->where('branch_id', $branch)
                            ->orderBy('id', 'DESC')->first();
        if($lastEOD) {
            if($lastEOD->to_date == \Carbon\Carbon::today()->toDateString()) {
                return response()->json([
                    'status' => 0,
                    'error'  => 'End of day not allowed'
                ], 403);
            }
        }

        if(new \DateTime(date('Y-m-d', strtotime($request->input('from')))) > new \DateTime(date('Y-m-d'))) {
            return response()->json([
                'status' => 0,
                'error'  => 'System date must be a past date'
            ], 400);
        }

        if(date('Y-m-d', strtotime($request->input('to'))) < date('Y-m-d')) {
            return response()->json([
                'status' => 0,
                'error'  => __("Current date must be :today", ["today" => date('Y-m-d')])
            ], 400);
        }
        
        // Update pending orders
        Order::query()->whereIn('id', $unpaidOrders)
                     ->update(['resolved' => 1]);
        EOD::create([
            'branch_id' => $branch,
            'from_date' => $request->input('from'),	
            'to_date'   => $request->input('to'),	
            'created_at'     => date('Y-m-d H:i:s'),	
            'unpaid_orders'  => $unpaidOrders
        ]);

        // Delete Printed Orders
        DB::table('pos_printed_rounds')->whereDate('date_printed', $yesterday)->delete();

        return response()->json([
            'status'  => 1
        ]);
    }

    /**
     * Get Latest END OF DAY record
     */
    public function getLastEOD()
    {
        $lastEOD = DB::table('pos_end_of_days')
                            ->where('branch_id', $this->getCurrentBranch())
                            ->orderBy('id', 'DESC')->first();
        return response()->json([
            'status' => 1,
            'row'    => $lastEOD,
            'yesterday' => $lastEOD ? $lastEOD->to_date : \Carbon\Carbon::today()->toDateString(),
            'unpaid'    => $this->getUnResolvedOrder()
        ]);
    }
    
    /**
     * Get Unpaid orders or those that are not resolved
     * @return array
     */
    private function getUnResolvedOrder()
    {
        $branch = $this->getCurrentBranch();
        return  Order::whereDate("created_at", "<", date('Y-m-d'))
                        ->where("paid", 0)
                        ->where("resolved", 0)
                        ->where('branch_id', $branch)
                        ->pluck("id");
    }

     /**
     * Close a day  record unpaid orders
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function closeWorkingDay(Request $request)
    {
        $unpaidOrders =  $this->getUnResolvedOrder();
        $branch = $this->getCurrentBranch();
        Order::query()->whereIn('id', $unpaidOrders)
                      ->where('branch_id', $branch)
                     ->update(['resolved' => 1]);
        EOD::create([
            'from_date' => getSystemDate(),	
            'to_date'   => date('Y-m-d'),	
            'created_at'     => date('Y-m-d H:i:s'),	
            'unpaid_orders'  => $unpaidOrders
        ]);
        DB::table('pos_rounds')->where('branch_id', $branch)->delete();
        Cache::forget($branch . '_system_date');
        $systemDate = Cache::rememberForever($branch . '_system_date', function () {
            return date('Y-m-d');
        });
        return response()->json([
            'status'      => 1,
            'system_date' => $systemDate
        ]);
    }
}