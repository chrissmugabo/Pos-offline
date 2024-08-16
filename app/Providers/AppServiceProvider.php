<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {

        if(env('APP_HTTPS')) {
            \URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS','on');
        }
        Schema::defaultStringLength(191);

        if(!app()->runningInConsole()) {
            // create symlink storage
            if (!is_link(public_path('storage'))) {
                if (!windows_os()) {
                    symlink(storage_path(), public_path('storage'));
                } else {
                    Artisan::call("storage:link", []);
                }
            }
        }

    
        // check deleted user
        if(auth()->id() and !auth()->check()){
            auth()->logout();
        }
    }
}
