<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Api\Models\Branch;
use App\Traits\TableTransaction;
use Illuminate\Support\Facades\DB;

class OrderDestination extends Model
{
    use SoftDeletes, TableTransaction;
    
    protected $table = 'pos_orders';

    /**
     * @var array
     */
    protected $appends = ['items'];

    /**
     * @return BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id')
                    ->select('branches.id', 'branches.name');
    }

    /**
     * @return BelongsTo
     */
    public function waiter()
    {
        return $this->belongsTo('\App\User', 'waiter_id', 'id')
                    ->select('users.id', 'users.name')
                    ->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id')
                    ->select('pos_tables.id', 'pos_tables.name');
    }

    /**
     * @return \Illuminate\Support\Collection 
     */
    public function getItemsAttribute()
    {
        $destination = \request()->get('destination');
        return DB::table('pos_order_items')->select('pos_order_items.id', 'pos_order_items.quantity', 'products.name')
                    ->join('products', 'pos_order_items.item_id', '=', 'products.id')
                    ->where('pos_order_items.destination', $destination)
                    ->where('pos_order_items.order_id', $this->id)
                    ->where('pos_order_items.delivered', 0)
                    ->get();
    }
}