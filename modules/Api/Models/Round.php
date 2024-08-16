<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    protected $table = 'pos_rounds';

    /**
     * @var array
     */
    protected $fillable = [
        'order_id',
        'branch_id',	
        'round_no',	
        'destination',	
        'printed',
        'items',
        'category'	
    ];

    protected $casts = [
        'items' => 'array'
    ];
}