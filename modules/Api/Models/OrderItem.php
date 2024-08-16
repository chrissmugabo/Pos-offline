<?php

namespace Modules\Api\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TableTransaction;

class OrderItem extends Model
{
    use SoftDeletes, TableTransaction;
    
    protected $table = 'pos_order_items';

    /**
     * @var array
     */
    protected $fillable = [
        'order_id',
        'item_id',
        'parent_id',
        'quantity',
        'price',
        'amount',
        'destination',
        'delivered',
        'round_key',
        'comment'
    ];

    /**
     * Here Product is for Inventory
     */
    protected $appends = ['addons', 'product'];

    protected $hidden = ['product'];

    /**
     * @return BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id')
                    ->select('products.id', 'products.cost', 'products.name', 'products.price', 'products.type_id', 'products.quantity', 'units.name As unit', 'product_types.name As type', 'product_types.type As group')
                    ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                    ->leftJoin('product_types', 'products.type_id', '=', 'product_types.id');
       
    }

    /**
     * Scope a query to only include orders matching period of time.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreated($query)
    {
        $from = \request()->get('from');
        if (empty($from)) {
            $from = date('Y-m-d');
        }
        $to = \request()->get('to');

        if (empty($to)) {
            return $query->where('pos_order_items.created_at', 'LIKE', "%{$from}%");
        } else {
            return $query->whereRaw("date(pos_order_items.created_at) >= '{$from}' and date(pos_order_items.created_at) <= '{$to}'");
        }
    }

    /**
     * Get Item Addons
     * @return \Collection
     */
    public function getAddonsAttribute()
    {
        return DB::table('pos_order_items')
                    ->select('pos_order_items.id','pos_order_items.item_id','pos_order_items.comment','products.name', 'products.unit_id', 'units.name As unit')
                    ->join('products', 'pos_order_items.item_id', '=', 'products.id')
                    ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                    ->where('pos_order_items.parent_id', $this->id)
                    ->whereNull('pos_order_items.deleted_at')
                    ->get();
    }

    /**
     * Get Production item details
     * @return \Illuminate\Support\Collection
     */
    public function getProductAttribute()
    {
        return Item::selectRaw('products.id, products.name, products.cost, products.price, products.quantity, products.status, products.store_purchase, products.source, units.name As unit, product_types.name As type, stock.quantity as stock_qty')
                        ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                        ->leftJoin('product_types', 'products.type_id', '=', 'product_types.id')
                        ->leftJoin('stock', function($join) {
                            $join->on('products.id', '=', 'stock.product_id')
                                 ->on('stock.source_id', '=', 'products.source');
                        })
                        ->where('products.id', $this->item_id)
                        ->first();
    }
}

