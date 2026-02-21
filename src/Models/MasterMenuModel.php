<?php

namespace InsureTech\RolePermission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterMenuModel extends Model
{
    use SoftDeletes;

    protected $table = 'master_menu';
    protected $guarded = [];

    public function rolePermissions()
    {
        return $this->hasMany(RolePermissionsModel::class, 'menu_id');
    }
}
