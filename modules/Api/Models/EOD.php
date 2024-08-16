<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TableTransaction;

class EOD extends Model
{
    use TableTransaction;
    
    protected $table = "pos_end_of_days";

    /**
     * @var array
     */
    protected $fillable = [
        'branch_id',
        'from_date',	
        'to_date',	
        'created_at',	
        'unpaid_orders'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'from_date'  => 'date',	
        'to_date'    => 'date',	
        'created_at' => 'datetime',	
        'unpaid_orders' => 'array'
    ];

    public $timestamps = false;
}