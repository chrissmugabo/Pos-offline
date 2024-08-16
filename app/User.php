<?php
namespace App;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Modules\Api\Models\WaiterShift;
use App\Traits\TableTransaction;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use SoftDeletes, Notifiable, TableTransaction;
    
    /**
     * The attributes that are mass assignable.r
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'address',
        'phone',
        'gender',
        'branch_id',
        'source_id',
        'last_login_at',
        'avatar',
        'status',
        'create_user',
        'role_id',
        'user_pin',
        'reference',
        'is_front_user'
    ];

    protected $casts = [
        'last_login_at' => 'datetime'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'user_pin', 'alerts'
    ];

    protected $appends = ['alerts', 'working'];

    public function getAlertsAttribute()
    {
        return $this->notifications()->latest()->take(10)->get();
    }

    public function role() 
    {
        return $this->belongsTo('\Modules\Api\Models\Role', 'role_id')
                    ->select('id', 'name', 'permissions', 'branch_permissions', 'pos_permissions');
    }

    public function branch()
    {
        return $this->belongsTo('\Modules\Api\Models\Branch', 'branch_id')
                    ->select('id', 'name');
    }

    public function creator()
    {
        return $this->belongsTo('\App\User', 'create_user', 'id')
                    ->select('id', 'name')
                    ->withTrashed();
    }
    
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get User By reference
     * @param string $reference
     * @return User
     */

    public static function getByReference(string $reference)
    {
        return self::where('reference', $reference)->first();
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
            $branch = auth()->user()->branch_id ?? \request()->get('current_branch');
        } else {
            $branch = \request()->get('current_branch');
        }
        if(!empty($branch)) {
            $query->where('branch_id', $branch);
        }
    }

    /**
     * Get User Source
     * @return BelongsTo
     */
    public function source() {
        return $this->belongsTo('\Modules\Api\Models\ItemSource', 'source_id')
                    ->select('id', 'name');
    }

    public function getWorkingAttribute()
    {
        return WaiterShift::where('waiter_id', $this->id)
                            ->whereDate('work_day', '>=', getSystemDate())
                            ->where('blocked', 0)
                            ->whereNull('end_time')
                            ->exists();
    }
}

