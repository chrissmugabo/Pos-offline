<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TableTransaction;

class Account extends Model
{
    use SoftDeletes, TableTransaction;

    protected $fillable = [
        'name',
        'currency_id',
        'initial_balance',
        'total_balance',
        'description',
        'is_default',
        'is_active',
        'branches'	
    ];

    protected $casts = [
        'branches'=> 'array',
    ];
}