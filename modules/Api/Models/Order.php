<?php
namespace Modules\Api\Models;

use Modules\Api\Models\Branch;
use Modules\Api\Models\Customer;
use App\Traits\TableTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes, TableTransaction;
    
    protected $table = "pos_orders";

    /**
     * @var array
     */
    protected $fillable = [
        'reference',
        'system_date',	
        'order_time',
        'table_id',	
        'waiter_id',	
        'amount',	
        'discount',	
        'grand_total',
        'amount_paid',
        'amount_remain',
        'payment_date',
        'status',
        'cashier_id',	
        'paid',	
        'printed',	
        'client_id',
        'room_id',	
        'branch_id',	
        'comment',	
        'category',
        'updated',
        'resolved',
        'receipts',
        'create_user',
        'deleted_by',
        'print_slips'	
    ];

    protected $casts = [
        'payment_date' => 'date',
        'system_date'  => 'date',
        'receipts'   => 'array'
    ];

    protected $appends = ['paid_amount', 'order_date'];

    /**
     * @return BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, "branch_id", "id")
                    ->select('branches.id', 'branches.name');
    }

    /**
     * @return BelongsTo
     */
    public function waiter()
    {
        return $this->belongsTo("\App\User", "waiter_id", "id")
                    ->select('users.id', 'users.name')
                    ->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function cashier()
    {
        return $this->belongsTo("\App\User", "cashier_id", "id")
                    ->select('users.id', 'users.name')
                    ->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function destroyer()
    {
        
    }

    /**
     * @return BelongsTo
     */
    public function table()
    {
        return $this->belongsTo(Table::class, "table_id")
                    ->select('pos_tables.id', 'pos_tables.name');
    }

    /**
     * @return BelongsTo
     */
    public function items()
    {
        $result = $this->hasMany(OrderItem::class, "order_id", "id")
                    ->select('pos_order_items.*', 'products.name')
                    ->leftJoin('products', 'pos_order_items.item_id', '=', 'products.id')
                    ->where('parent_id', 0);
        if(!empty($destination = \request()->get('destination'))) {
            $result->where('pos_order_items.destination', $destination);
        }
        return $result;
    }

    /**
     * @return BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Customer::class, "client_id")
                    ->select('clients.id', 'clients.name', 'clients.phone', 'clients.tin_number')
                    ->withTrashed();
    }

    /**
     * Get Total Paid Amount
     * @return double
     */
    public function payments() 
    {
        return $this->hasMany(Payment::class, 'order_id', 'id')
                    ->selectRaw('payments.id, payments.amount_paid, payments.payment_type, payments.account_id, payments.order_id');
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
        $to = \request()->get('to');
        if (empty($from)) {
            $from = $to = getSystemDate();
        } 
        if (empty($to)) {
            return $query->whereDate('pos_orders.system_date', $from);
        } else {
            return $query->whereRaw("date(pos_orders.system_date) >= '{$from}' and date(pos_orders.system_date) <= '{$to}'");
        }
    }

    /**
     * Get Paid Amount Attribute
     * @return double
     */
    public function getPaidAmountAttribute()
    {
        return Payment::where('order_id', $this->id)->sum('amount_paid');
    }

    /**
     * @return hasMany
     */
    public function details()
    {
        return $this->hasMany(OrderItem::class, "order_id", "id")
                    ->selectRaw('pos_order_items.id, pos_order_items.order_id, pos_order_items.quantity, pos_order_items.price, pos_order_items.amount, pos_order_items.destination, pos_order_items.comment, products.name, units.name as unit')
                    ->join('products', 'pos_order_items.item_id', '=', 'products.id')
                    ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                    ->where('parent_id', 0);
    }

    /**
     * @return BelongsTo
     */
    public function room()
    {
        return $this->belongsTo("\Modules\Api\Models\Accommodation\RoomAssignment", "room_id")
                    ->select('acco_room_assignments.id', 'acco_room_assignments.room_name',);
    }

    public function getOrderDateAttribute()
    {
        return date('Y-m-d', strtotime($this->system_date)) . ' ' . $this->order_time;
        //return $this->created_at;
    }
}

