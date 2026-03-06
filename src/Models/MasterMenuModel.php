<?php

namespace SajidJalal\PermissionKit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterMenuModel extends Model
{
    use SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('permission-kit.tables.permissions', 'rbac_master_menu'));
    }

    protected $table = 'master_menu';
    protected $guarded = [];

    public function rolePermissions()
    {
        return $this->hasMany(RolePermissionsModel::class, 'menu_id');
    }
}
