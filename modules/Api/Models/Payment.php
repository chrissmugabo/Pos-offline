<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TableTransaction;

class Payment extends Model
{
    use SoftDeletes, TableTransaction;
    
    protected $fillable = [
        'committed_date',	
        'transaction_id',
        'amount_paid',
        'order_id',
        'account_id',
        'payment_type',	
        'comment',
        'reference',
        'create_user',
        'debt_id'
    ];

    protected $casts = [
        'committed_date' => 'date'
    ];

    protected $appends = [ 'payment_mode' ];

     /**
     * Get Payment method name
     *  @return \Illuminate\Support\Collection
     */
    public function getPaymentModeAttribute()
    {
        $result = \Modules\Api\Models\PaymentMethod::select('id', 'name')->where('id', $this->payment_type)
                                                        ->withTrashed()->first();
        if (!$result) {
            $result = new \stdClass();
            $result->id = 1;
            $result->name = 'CASH'; 
        }

        return $result;
    }
}