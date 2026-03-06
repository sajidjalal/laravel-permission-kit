<?php

namespace SajidJalal\PermissionKit\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use SajidJalal\PermissionKit\Models\MasterMenuModel;


class PermissionHelper
{
    public function checkRoleHasPermission($roleId, $permissionName, $accessMenu = [])
    {
        $cacheKey = 'cache_role_has_permission_' . $roleId . '_' . $permissionName;

        $ttl = config('permission-kit.cache.ttl', 60);

        $had_access = false;
        $result = false;
        // 
        if (Cache::has($cacheKey)) {
            $result = Cache::get($cacheKey);
        } else {
            try {
                $query = DB::table('role_permissions')
                    ->select('permissions_name', 'create', 'read', 'update', 'delete')
                    ->join('master_menu', 'master_menu.id', '=', 'role_permissions.menu_id')
                    ->where('role_permissions.role_id', $roleId)
                    ->where('master_menu.permissions_name', $permissionName);

                // If $accessMenu is empty, grant access to all permissions
                if (empty($accessMenu)) {
                    $accessMenu = ['create', 'read', 'update', 'delete'];
                }

                if (count($accessMenu) == 1) {
                    $query->where(function ($query) use ($accessMenu) {
                        $query->orWhere("role_permissions.$accessMenu[0]", '=', 1);
                    });
                } else {
                    $query->where(function ($query) use ($accessMenu) {
                        foreach ($accessMenu as $menu) {
                            $query->orWhere("role_permissions.$menu", '=', 1);
                        }
                    });
                }
                $result = $query->whereNull('master_menu.deleted_at')->whereNull('role_permissions.deleted_at')->first();
                Cache::put($cacheKey, $result, now()->addMinutes($ttl));
            } catch (\Exception $e) {
                $result = false;
            }
        }

        if ($result && ($result->create === 1 || $result->read === 1 || $result->update === 1 || $result->delete === 1)) {
            $had_access = true;
        }

        return $had_access;
    }

    public function getAllMenus($roleId = null)
    {
        try {
            return MasterMenuModel::all();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get list of roles for a user
     */
    public function getRoleList($user = null, $includeAll = false, $asArray = true)
    {
        try {
            if ($includeAll) {
                $roles = \SajidJalal\PermissionKit\Models\RolesModel::all();
            } else {
                if (!$user) {
                    return $asArray ? [] : collect([]);
                }

                if (method_exists($user, 'roles')) {
                    $roles = $user->roles;
                } else {
                    return $asArray ? [] : collect([]);
                }
            }

            if ($asArray) {
                return $roles->pluck('name')->toArray();
            }

            return $roles;
        } catch (\Exception $e) {
            return $asArray ? [] : collect([]);
        }
    }

    /**
     * Get permission list
     */
    public function getPermissionList($user = null, $includeAll = false, $asArray = true)
    {
        try {
            if ($includeAll) {
                $permissions = \SajidJalal\PermissionKit\Models\RolePermissionsModel::all();
            } else {
                if (!$user) {
                    return $asArray ? [] : collect([]);
                }

                if (method_exists($user, 'permissions')) {
                    $permissions = $user->permissions;
                } else {
                    return $asArray ? [] : collect([]);
                }
            }

            if ($asArray) {
                return $permissions->pluck('name')->toArray();
            }

            return $permissions;
        } catch (\Exception $e) {
            return $asArray ? [] : collect([]);
        }
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($user, $permission)
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }

        return false;
    }

    /**
     * Check if user has role
     */
    public function hasRole($user, $role)
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole')) {
            return $user->hasRole($role);
        }

        return false;
    }

    /**
     * Get all roles from database
     */
    public function getAllRoles()
    {
        try {
            return \SajidJalal\PermissionKit\Models\RolesModel::all();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get all permissions from database
     */
    public function getAllPermissions()
    {
        try {
            return \SajidJalal\PermissionKit\Models\RolePermissionsModel::all();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Assign role to user
     */
    public function assignRole($user, $role)
    {
        if (!$user || !method_exists($user, 'assignRole')) {
            return false;
        }

        return $user->assignRole($role);
    }

    /**
     * Remove role from user
     */
    public function removeRole($user, $role)
    {
        if (!$user || !method_exists($user, 'removeRole')) {
            return false;
        }

        return $user->removeRole($role);
    }

    /**
     * Get single role by name
     */
    public function getRole($roleName)
    {
        try {
            return \SajidJalal\PermissionKit\Models\RolesModel::where('name', $roleName)->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get single permission by name
     */
    public function getPermission($permissionName)
    {
        try {
            return \SajidJalal\PermissionKit\Models\RolePermissionsModel::where('name', $permissionName)->first();
        } catch (\Exception $e) {
            return null;
        }
    }
}
