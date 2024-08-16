<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;

class WaiterShift extends Model
{
    protected $table = "waiters_shift";

    /**
     * @var array
     */
    protected $fillable = [
        'waiter_id',
        'work_day',	
        'start_time',	
        'end_time',
        'blocked'	
    ];

    protected $casts = [
        'work_day'   => 'date',
        //'start_time' => 'time',
        //'end_time'   => 'time'
    ];

    public $timestamps = false;
}