<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use InsureTech\RolePermission\Models\MasterMenuModel;
use InsureTech\RolePermission\Models\RolePermissionsModel;
use InsureTech\RolePermission\Models\RolesModel;

if (!function_exists('addRolePermission')) {
    function addRolePermission($role_id, $user_id)
    {
        $menuList = MasterMenuModel::where([
            'status' => 1,
        ])
            ->whereNotIn('menu_for', ['super_admin'])
            ->whereNotIn('id', [36, 37, 38, 39])
            ->get();

        foreach ($menuList as $key => $value) {
            $attributes = [
                'role_id' => $role_id,
                'menu_id' => $value->id,
            ];

            $values = [
                'create' => 0,
                'read' => 0,
                'update' => 0,
                'delete' => 0,
                'created_by' => $user_id,
                'created_at' => now(),
            ];

            RolePermissionsModel::updateOrCreate($attributes, $values);
            flushQueryCache($role_id);
        }
    }
}

if (!function_exists('getRoleList')) {
    function getRoleList($userInfo, $id = false, $is_all = false)
    {
        $resultQuery = RolesModel::select('id', 'role_name', 'display_name')->where('status', 1);

        if ($userInfo['role_id'] != SUPER_ADMIN_ROLE_ID) {
            $excludedRoles = [SUPER_ADMIN_ROLE_ID, CUSTOMER_ROLE_ID, POS_ROLE_ID];

            if ($userInfo['is_admin']) {
                $excludedRoles = [SUPER_ADMIN_ROLE_ID, CUSTOMER_ROLE_ID];
                $resultQuery->whereNotIn('id', $excludedRoles);
            } else {
                $resultQuery->whereNotIn('id', $excludedRoles)
                    ->where(function ($query) use ($userInfo) {
                        $query->where('reporting_role_id', $userInfo['role_id'])
                            ->orWhere('id', $userInfo['role_id']);
                    });
            }
        }

        if ($is_all) {
            $resultQuery->whereNotIn('id', [CUSTOMER_ROLE_ID]);
        }

        if ($id) {
            $resultQuery->where('id', $id);
        }

        return $resultQuery->get();
    }
}

if (!function_exists('checkRoleHasPermission')) {
    function checkRoleHasPermission($request, $userInfo, $permissionName, $accessMenu = [], $check_status = false)
    {
        $cacheKey = 'cache_role_has_permission_' . $userInfo['role_id'] . '_' . $permissionName;

        $data = [];
        $result = false;

        if (Cache::has($cacheKey)) {
            $result = Cache::get($cacheKey);
        } else {
            try {
                $query = DB::table('role_permissions')
                    ->select('permissions_name', 'create', 'read', 'update', 'delete')
                    ->join('master_menu', 'master_menu.id', '=', 'role_permissions.menu_id')
                    ->where('role_permissions.role_id', $userInfo['role_id'])
                    ->where('master_menu.permissions_name', $permissionName);

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
                Cache::put($cacheKey, $result, now()->addMinutes(CACHE_KEY_CLEAR_TIME));
            } catch (\Exception $e) {
                Log::info('helper::cache_role_has_permission_()');
                Log::info('Exception : ' . $e);
            }
        }

        $status = 403;
        $message = UN_AUTHORIZE_MSG;

        $have_error = true;

        if ($result && ($result->create === 1 || $result->read === 1 || $result->update === 1 || $result->delete === 1)) {
            $status = 200;
            $message = 'Access granted';
            $have_error = false;
            $data['access_right'] = $result;
        }

        $data['check_status'] = false;

        if ($check_status) {
            $data['check_status'] = $have_error ? false : true;
            return response()->json(['status' => true, 'errors' => [], 'message' => $message, 'data' => $data], $status);
        }

        if ($request) {
            if (!$request->ajax() && $have_error) {
                abort($status, $message);
            }
        }

        return response()->json(['status' => true, 'errors' => [], 'message' => $message, 'data' => $data], $status);
    }
}

if (!function_exists('getRoleMenuPermissions')) {
    function getRoleMenuPermissions($userInfo, $request_role_id = false, $is_all_menu = false)
    {
        $brokerInfo = getBrokerInfo();
        $role_id = $userInfo['role_id'];

        if ($request_role_id) {
            $role_id = $request_role_id;
        }

        $cacheKey = 'cache_sidebar_menu_' . $role_id;
        if ($is_all_menu) {
            $cacheKey = 'cache_sidebar_menu_all_menu' . $role_id;
        }

        if (Cache::has($cacheKey)) {
            $result = Cache::get($cacheKey);
        } else {
            $permissionQuery = RolePermissionsModel::with('menu')
                ->join('master_menu', 'role_permissions.menu_id', '=', 'master_menu.id')
                ->where('master_menu.is_menu_show', 1)
                ->where('role_permissions.role_id', $role_id);

            if ($role_id != SUPER_ADMIN_ROLE_ID) {
                $restrictedBrokers = ['policyera_insurance'];

                if (in_array($brokerInfo->broker_code, $restrictedBrokers)) {
                    $excludedPermissions = [];

                    if (!empty($brokerInfo->excluded_permissions)) {
                        $decoded = json_decode($brokerInfo->excluded_permissions, true);

                        if (is_array($decoded)) {
                            $excludedPermissions = $decoded;
                        }
                    }

                    if (!empty($excludedPermissions)) {
                        $permissionQuery->whereNotIn('master_menu.permissions_name', $excludedPermissions);
                    }
                }
            }

            if (!$is_all_menu) {
                $permissionQuery->where(function ($query) {
                    $query->where('role_permissions.create', 1)
                        ->orWhere('role_permissions.read', 1)
                        ->orWhere('role_permissions.update', 1)
                        ->orWhere('role_permissions.delete', 1);
                });
            }

            if ($role_id == POS_ROLE_ID) {
                $permissionQuery->where('master_menu.group_name', '!=', 'POS');
            }

            if (!$brokerInfo->has_broker_engine) {
                $permissionQuery->where('master_menu.menu_name', '!=', 'Online Policy');
            }

            if ($brokerInfo['broker_code'] != 'bimavale_insurance') {
                $permissionQuery->whereNotIn('master_menu.permissions_name', ['bima_buddy_request', 'renew_request']);
            }

            $result = $permissionQuery->get()->map(function ($rp) {
                return [
                    'role_permission_id' => $rp->id,
                    'role_id' => $rp->role_id,
                    'create' => $rp->create,
                    'read' => $rp->read,
                    'update' => $rp->update,
                    'delete' => $rp->delete,
                    'menu_id' => $rp->menu->id,
                    'menu_name' => $rp->menu->menu_name,
                    'url' => $rp->menu->url,
                    'permissions_name' => $rp->menu->permissions_name,
                    'display_permissions_name' => $rp->menu->display_permissions_name,
                    'menu_for' => $rp->menu_for,
                    'group_name' => $rp->menu->group_name,
                    'icon' => $rp->menu->icon,
                    'fa_icon' => $rp->menu->fa_icon,
                    'parent_id' => $rp->menu->parent_id,
                    'sequence' => $rp->menu->sequence,
                    'is_menu_show' => $rp->menu->is_menu_show,
                    'created_at' => $rp->created_at,
                    'deleted_at' => $rp->deleted_at,
                ];
            });

            $result = $result->sortBy([
                ['parent_id', 'asc'],
                ['sequence', 'asc']
            ])->values();

            Cache::put($cacheKey, $result, now()->addMinutes(CACHE_KEY_CLEAR_TIME));
        }

        return $result;
    }
}

if (!function_exists('getRoleMenuPermissionsList')) {
    function getRoleMenuPermissionsList($userInfo, $request_role_id = false, $is_all_menu = false)
    {
        $brokerInfo = getBrokerInfo();
        $role_id = $request_role_id;

        $permissionQuery = RolePermissionsModel::with('menu')
            ->join('master_menu', 'role_permissions.menu_id', '=', 'master_menu.id')
            ->where('role_permissions.role_id', $role_id);

        if ($role_id != SUPER_ADMIN_ROLE_ID) {
            $restrictedBrokers = ['policyera_insurance'];

            if (in_array($brokerInfo->broker_code, $restrictedBrokers)) {
                $excludedPermissions = [];

                if (!empty($brokerInfo->excluded_permissions)) {
                    $decoded = json_decode($brokerInfo->excluded_permissions, true);

                    if (is_array($decoded)) {
                        $excludedPermissions = $decoded;
                    }
                }

                if (!empty($excludedPermissions)) {
                    $permissionQuery->whereNotIn('master_menu.permissions_name', $excludedPermissions);
                }
            }
        }

        if (!$is_all_menu) {
            $permissionQuery->where(function ($query) {
                $query->where('role_permissions.create', 1)
                    ->orWhere('role_permissions.read', 1)
                    ->orWhere('role_permissions.update', 1)
                    ->orWhere('role_permissions.delete', 1);
            });
        }

        if ($request_role_id == SUPER_ADMIN_ROLE_ID) {
            $permissionQuery->whereIn('master_menu.menu_for', ['admin', 'super_admin'])
                ->where(function ($query) {
                    $query->whereNull('master_menu.sub_menu_for')
                        ->orWhereIn('master_menu.sub_menu_for', ['group'])
                        ->orWhere('master_menu.sub_menu_for', '!=', 'pos');
                });
        } elseif ($request_role_id == ADMIN_ROLE_ID) {
            $permissionQuery->where('master_menu.menu_for', 'admin')
                ->where(function ($query) {
                    $query->whereNull('master_menu.sub_menu_for')
                        ->orWhereIn('master_menu.sub_menu_for', ['group'])
                        ->orWhere('master_menu.sub_menu_for', '!=', 'pos');
                });
        } elseif ($request_role_id == POS_ROLE_ID) {
            $permissionQuery->whereIn('master_menu.menu_for', ['admin'])
                ->where(function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->where('master_menu.group_name', 'POS')
                            ->whereIn('master_menu.sub_menu_for', ['pos', 'group']);
                    })
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('master_menu.group_name', '!=', 'POS')
                                ->orWhereNull('master_menu.group_name');
                        });
                });
        }

        if ($brokerInfo['broker_code'] != 'bimavale_insurance') {
            $permissionQuery->whereNotIn('master_menu.permissions_name', ['bima_buddy_request', 'renew_request']);
        }

        $permissionQuery->orderBy('master_menu.group_name');
        $permissionQuery->where('role_permissions.status', 1);
        $permissionQuery->where('master_menu.is_permission_show', 1);
        $result = $permissionQuery->get()->map(function ($rp) {
            return [
                'role_permission_id' => $rp->id,
                'role_id' => $rp->role_id,
                'create' => $rp->create,
                'read' => $rp->read,
                'update' => $rp->update,
                'delete' => $rp->delete,
                'menu_id' => $rp->menu->id,
                'menu_name' => $rp->menu->menu_name,
                'url' => $rp->menu->url,
                'permissions_name' => $rp->menu->permissions_name,
                'display_permissions_name' => $rp->menu->display_permissions_name,
                'menu_for' => $rp->menu_for,
                'group_name' => $rp->menu->group_name,
                'icon' => $rp->menu->icon,
                'fa_icon' => $rp->menu->fa_icon,
                'parent_id' => $rp->menu->parent_id,
                'sequence' => $rp->menu->sequence,
                'created_at' => $rp->created_at,
                'deleted_at' => $rp->deleted_at,
            ];
        });

        $existingMenuIds = $result->pluck('menu_id')->all();
        $missingParentIds = $result->pluck('parent_id')->filter(fn($id) => $id && !in_array($id, $existingMenuIds))->unique();

        if ($missingParentIds->isNotEmpty()) {
            $missingParents = MasterMenuModel::whereIn('id', $missingParentIds)
                ->where('is_permission_show', 1)
                ->get()
                ->map(function ($menu) {
                    return [
                        'role_permission_id' => null,
                        'role_id' => null,
                        'create' => 0,
                        'read' => 0,
                        'update' => 0,
                        'delete' => 0,
                        'menu_id' => $menu->id,
                        'menu_name' => $menu->menu_name,
                        'url' => $menu->url,
                        'permissions_name' => $menu->permissions_name,
                        'display_permissions_name' => $menu->display_permissions_name,
                        'menu_for' => $menu->menu_for,
                        'group_name' => $menu->group_name,
                        'icon' => $menu->icon,
                        'fa_icon' => $menu->fa_icon,
                        'parent_id' => $menu->parent_id,
                        'sequence' => $menu->sequence,
                        'created_at' => $menu->created_at,
                        'deleted_at' => $menu->deleted_at,
                    ];
                });

            $result = $result->merge($missingParents);
        }
        $finalResult = collect();

        $parentMenuIds = $result->where('parent_id', 0)->pluck('menu_id')->unique()->filter()->values();
        $parents = $result->whereIn('menu_id', $parentMenuIds)->sortBy('sequence');

        $pushWithChildren = function ($item, $result) use (&$finalResult, &$pushWithChildren) {
            $finalResult->push($item);
            $children = $result->where('parent_id', $item['menu_id'])->sortBy('sequence');

            foreach ($children as $child) {
                $pushWithChildren($child, $result);
            }
        };

        foreach ($parents as $parent) {
            $pushWithChildren($parent, $result);
        }

        $hierarchyIds = $finalResult->pluck('menu_id')->all();
        $remaining = $result->whereNotIn('menu_id', $hierarchyIds)->sortBy('sequence');
        foreach ($remaining as $item) {
            $finalResult->push($item);
        }

        return $finalResult->values();
    }
}

if (!function_exists('flushQueryCache')) {
    function flushQueryCache($roleId)
    {
        $sidebarMenuCacheKey = 'cache_sidebar_menu_' . $roleId;
        Cache::forget($sidebarMenuCacheKey);

        $roleHasPermissionCacheKey = 'cache_role_has_permission_' . $roleId;
        Cache::forget($roleHasPermissionCacheKey);

        $sideBarAllMenuCache = 'cache_sidebar_menu_all_menu' . $roleId;
        Cache::forget($sideBarAllMenuCache);

        Log::info('clearCache flushQueryCache :');
        Cache::flush();
    }
}

if (!function_exists('assignRolePermissions')) {
    function assignRolePermissions(int $role_id, int $user_id): void
    {
        $menuList = MasterMenuModel::get();

        foreach ($menuList as $menu) {
            $fields = [
                'role_id' => $role_id,
                'menu_id' => $menu->id,
                'create' => 0,
                'read' => 0,
                'update' => 0,
                'delete' => 0,
                'status' => 1,
                'updated_by' => $user_id,
                'updated_at' => now(),
            ];

            if ($role_id == SUPER_ADMIN_ROLE_ID && $menu->sub_menu_for != 'pos') {
                $fields['create'] = 1;
                $fields['read'] = 1;
                $fields['update'] = 1;
                $fields['delete'] = 1;
            }

            $rolePermission = RolePermissionsModel::updateOrCreate(
                [
                    'role_id' => $role_id,
                    'menu_id' => $menu->id,
                ],
                $fields
            );

            if (!$rolePermission->wasRecentlyCreated) {
                $fields['created_by'] = $user_id;
                $fields['created_at'] = now();
                $rolePermission->update($fields);
            }
        }
    }
}

if (!function_exists('getReportingRoleList')) {
    function getReportingRoleList($userInfo, $id = '')
    {
        $excludedRoles = [CUSTOMER_ROLE_ID, POS_ROLE_ID];

        $resultQuery = RolesModel::select('id', 'role_name', 'display_name')->where('status', 1)->whereNotIn('id', $excludedRoles);

        if ($userInfo['role_id'] != SUPER_ADMIN_ROLE_ID) {
            $resultQuery->whereNotIn('id', [SUPER_ADMIN_ROLE_ID]);
        }

        if ($id) {
            $resultQuery->where('id', $id);
        }

        return $resultQuery->get();
    }
}

if (!function_exists('getAllSubordinateRoleIds')) {
    function getAllSubordinateRoleIds($roleId)
    {
        $roles = DB::table('roles')->where('reporting_role_id', $roleId)->pluck('id');

        $subRoles = [];
        foreach ($roles as $subRoleId) {
            $subRoles[] = $subRoleId;
            $subRoles = array_merge($subRoles, getAllSubordinateRoleIds($subRoleId));
        }

        return $subRoles;
    }
}
