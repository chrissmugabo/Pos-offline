<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EndOfDayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $cacheKey = 'end_of_day_' . Carbon::now()->toDateString();
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, true, now()->endOfDay());
            $row = DB::table('product_statuses')->whereDate('system_date', date('Y-m-d'))->first();
            if(!$row) {
                ini_set('max_execution_time', 300); // 5 minutes
                Artisan::call('day:start');
            }
        }
    }
}
