<?php

namespace SajidJalal\PermissionKit\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use SajidJalal\PermissionKit\Models\MasterMenuModel;
use SajidJalal\PermissionKit\Models\RolesModel;

class RolePermissionsModel extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('permission-kit.tables.role_permissions', 'rbac_role_permissions'));
    }
    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function role()
    {
        return $this->belongsTo(RolesModel::class, 'role_id', 'id');
    }

    public function menu()
    {
        return $this->belongsTo(MasterMenuModel::class, 'menu_id');
    }
}
