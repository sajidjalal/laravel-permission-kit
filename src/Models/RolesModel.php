<?php

namespace SajidJalal\PermissionKit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class RolesModel extends Model
{
    use SoftDeletes;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('permission-kit.tables.permissions', 'rbac_roles'));
    }

    protected $guarded = [];

    const IS_ADMIN = 1;

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($model) {
            Cache::forget('get_role_list' . $model->id);
            Cache::forget('get_role_name' . $model->id);
        });

        static::created(function ($model) {
            Cache::forget('get_role_list' . $model->id);
            Cache::forget('get_role_name' . $model->id);
        });
    }

    public static function getRoleList($createdByRoleId)
    {
        $cacheKey = 'get_role_list' . $createdByRoleId;

        $ttl = config('permission-kit.cache.ttl', 60);

        return Cache::remember($cacheKey, now()->addMinutes($ttl), function () {
            return self::select('id', 'role_name', 'display_name')
                ->where('status', 1)
                ->whereNotIn('id', [SUPER_ADMIN_ROLE_ID])
                ->get();
        });
    }

    public static function getRoleName($roleId)
    {
        $cacheKey = 'get_role_name' . $roleId;
        $ttl = config('permission-kit.cache.ttl', 60);
        $roleList = Cache::remember($cacheKey, now()->addMinutes($ttl), function () {
            return self::select('id', 'role_name', 'display_name')
                ->where('status', 1)
                ->whereNotIn('id', [SUPER_ADMIN_ROLE_ID])
                ->get();
        });

        return $roleList->where('id', $roleId)->first()->display_name ?? '';
    }


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault();
    }
}
