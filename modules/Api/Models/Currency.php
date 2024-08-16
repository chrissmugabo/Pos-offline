<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'currencies'; 

     /**
     * @var array
     */
    protected $fillable = [
        'title',
        'symbol',
        'is_default',
        'rate',
    ];

}
