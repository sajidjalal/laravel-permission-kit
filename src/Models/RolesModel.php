<?php

namespace InsureTech\RolePermission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class RolesModel extends Model
{
    use SoftDeletes;

    protected $table  = "roles";
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

        return Cache::remember($cacheKey, now()->addMinutes(CACHE_KEY_CLEAR_TIME), function () {
            return self::select('id', 'role_name', 'display_name')
                ->where('status', 1)
                ->whereNotIn('id', [SUPER_ADMIN_ROLE_ID])
                ->get();
        });
    }

    public static function getRoleName($roleId)
    {
        $cacheKey = 'get_role_name' . $roleId;

        $roleList = Cache::remember($cacheKey, now()->addMinutes(CACHE_KEY_CLEAR_TIME), function () {
            return self::select('id', 'role_name', 'display_name')
                ->where('status', 1)
                ->whereNotIn('id', [SUPER_ADMIN_ROLE_ID])
                ->get();
        });

        return $roleList->where('id', $roleId)->first()->display_name ?? '';
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by')->withDefault();
    }
}
