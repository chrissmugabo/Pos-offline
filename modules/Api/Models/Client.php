<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use function Illuminate\Events\queueable;

class Client extends Model
{
    use SoftDeletes;

    protected static function searchField()
    {
        return "clients.name";
    } 

    protected static function handleLocalBooted()
    {
        static::created(queueable(function ($model) 
        {
            DB::table('table_transactions')->insert([
                'system_date'  => date('Y-m-d'),
                'object_id'	   => $model->id,
                'object_model' => get_class($model), 	
                'operation'	   => 'CREATE',
                'environment'  => setting_item('POS_ENV', 'ONLINE'),
                'created_at'  => \Carbon\Carbon::now(),
                'updated_at'  => \Carbon\Carbon::now()
            ]);
        }));

        static::updated(queueable(function ($model) 
        {
            DB::table('table_transactions')->insert([
                'system_date'  => date('Y-m-d'),
                'object_id'	   => $model->id,
                'object_model' => get_class($model), 	
                'operation'	   => 'DELETE',
                'environment'  => setting_item('POS_ENV', 'ONLINE'),
                'created_at'  => \Carbon\Carbon::now(),
                'updated_at'  => \Carbon\Carbon::now()
            ]);
        }));

        static::deleted(queueable(function ($model) 
        {
            DB::table('table_transactions')->insert([
                'system_date'  => date('Y-m-d'),
                'object_id'	   => $model->id,
                'object_model' => get_class($model), 	
                'operation'	   => 'UPDATE',
                'environment'  => setting_item('POS_ENV', 'ONLINE'),
                'created_at'  => \Carbon\Carbon::now(),
                'updated_at'  => \Carbon\Carbon::now()
            ]);
        }));
    }
    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'address',
        'create_user',
        'branch_id',
        'discount',
        'tin_number',
        'type',	
        'status',
        'nationality',	
        'id_type',	
        'id_no',	
        'gender',	
        'dob', 
        'balance'
    ];
}