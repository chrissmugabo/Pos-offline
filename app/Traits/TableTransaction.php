<?php

namespace App\Traits;
use function Illuminate\Events\queueable;
use Illuminate\Support\Facades\DB;

Trait TableTransaction
{
  protected static function booted()
  {
    /**
     * If there are other stuff to be called on booted in model,
     * then call them from here
     */
    if (method_exists(__CLASS__, 'handleLocalBooted')) {
        self::handleLocalBooted();
    }
   
    static::created(queueable(function ($model) 
    {
        if(config('app.has_offline_version') || isOffline()) {
            DB::table('table_transactions')->insert([
                'system_date'  => date('Y-m-d'),
                'object_id'	   => $model->id,
                'object_model' => get_class($model), 	
                'operation'	   => 'CREATE',
                'environment'  => setting_item('POS_ENV', 'ONLINE'),
                'created_at'  => \Carbon\Carbon::now(),
                'updated_at'  => \Carbon\Carbon::now()
            ]);
        }
    }));

    static::updated(queueable(function ($model) 
    {
        if(config('app.has_offline_version') || isOffline()) {
            DB::table('table_transactions')->insert([
                'system_date'  => date('Y-m-d'),
                'object_id'	   => $model->id,
                'object_model' => get_class($model), 	
                'operation'	   => 'DELETE',
                'environment'  => setting_item('POS_ENV', 'ONLINE'),
                'created_at'  => \Carbon\Carbon::now(),
                'updated_at'  => \Carbon\Carbon::now()
            ]);
        }
    }));

    static::deleted(queueable(function ($model) 
    {
        if(config('app.has_offline_version') || isOffline()) {
            DB::table('table_transactions')->insert([
                'system_date'  => date('Y-m-d'),
                'object_id'	   => $model->id,
                'object_model' => get_class($model), 	
                'operation'	   => 'UPDATE',
                'environment'  => setting_item('POS_ENV', 'ONLINE'),
                'created_at'  => \Carbon\Carbon::now(),
                'updated_at'  => \Carbon\Carbon::now()
            ]);   
        }
    }));

  }
}