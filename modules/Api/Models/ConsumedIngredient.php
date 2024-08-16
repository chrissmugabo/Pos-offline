<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TableTransaction;

class ConsumedIngredient extends Model
{
    use SoftDeletes, TableTransaction;


    public function __construct()
    {
        self::booted();
    }

    protected $fillable = [
        'date_consumed',
        'item_id',
        'quantity',
        'sale_id',
        'order_id',
        'menu_item_id',
        'parent_qty',
        'branch_id'
    ];

    protected $casts = [
        'date_consumed' => 'date'
    ];
}
