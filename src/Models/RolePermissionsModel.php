<?php

namespace SajidJalal\PermissionKit\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RolePermissionsModel extends Model
{
    protected $table = 'role_permissions';
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
