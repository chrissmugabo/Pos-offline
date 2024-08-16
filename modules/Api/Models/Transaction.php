<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_date',
        'action',
        'type',
        'origin',
        'reference_id',
        'account_id',
        'payment_mode',
        'amount',	
        'previous_balance',	
        'running_balance',	
        'description'
    ];

    protected $casts = [
        'transaction_date' => 'date'
    ];
}