<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TableTransaction;

class Item extends Model
{
    use TableTransaction;
    
    protected $table = "products";

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'code',
        'name',
        'description',
        'cost',
        'price',
        'store',
        'type_id',
        'unit_id',
        'quantity',
        'count_unit',
        'status',
        'category',	
        'supplier',		
        'store_min_qty',
        'store_purchase',
        'branch_min_qty',	
        'branch_purchase',
        'create_user',
        'expiry_notification',
        'source',
        'has_addons',
        'initialized',
        'branch_id'
    ];
}