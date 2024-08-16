<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;

class PrintedRound extends Model
{
    protected $table = "pos_printed_rounds";

    /**
     * @var array
     */
    protected $fillable = [
        'round_id',
        'client_token',
        'frequency',	
        'date_printed'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'date_printed'  => 'date',
    ];

    public $timestamps = false;
}