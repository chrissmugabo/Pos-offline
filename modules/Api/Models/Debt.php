<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TableTransaction;

class Debt extends Model
{
    use SoftDeletes, TableTransaction;

    protected $table = 'debts';

    protected $fillable = [
        'date_taken',
        'origin',	
        'origin_id',
        'amount',	
        'paid',	
        'client_id',	
        'notes'	
    ];

    protected $casts = [
        'date_taken' => 'date'
    ];

    public static function boot()
    {
        parent::boot();
        static::created(function ($debt) {
            $client = Client::find($debt->client_id);
            if($client) {
                $client->balance += $debt->amount;
                $client->save();
            }
        });

        static::deleted(function ($debt) {
            $client = Client::find($debt->client_id);
            if($client) {
                $client->balance -= $debt->amount;
                $client->save();
            }
        });
    }

}