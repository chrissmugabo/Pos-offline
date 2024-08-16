<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Api\Models\Branch;
use App\Traits\TableTransaction;

class Table extends Model
{
    use SoftDeletes, TableTransaction;

    protected $table = 'pos_tables';

    protected $fillable = [
        'name',	
        'code',	
        'capacity',	
        'description', 
        'branch_id', 
        'active'
    ];

    protected $casts = [
        //'active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator()
    {
        return $this->belongsTo('\App\User', 'create_user', 'id')
                    ->select('id', 'name')
                    ->withTrashed();
    }

    /**
     * Scope a query to only.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBranch($query)
    {
        if (auth()->user()) {
            $branch = \request()->get('current_branch') ?? auth()->user()->branch_id;
        } else {
            $branch = \request()->get('current_branch');
        }
        if(!empty($branch)) {
            $query->where('branch_id', $branch);
        }
    }
}