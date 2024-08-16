<?php
namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'core_roles';
    protected $fillable = [
        'name',
        'permissions',
        'branch_permissions',
        'pos_permissions',
        'accommodation_permissions',
        'description',
        'status',
        'origin'
    ];

    protected $casts = [
        'permissions'        => 'object',
        'branch_permissions' => 'object',
        'pos_permissions'    => 'object',
        'accommodation_permissions' => 'object',
    ];
}