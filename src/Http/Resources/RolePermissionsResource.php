<?php

namespace InsureTech\RolePermission\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RolePermissionsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $created_by = "";
        if ($request->creator) {
            $created_by =  $request->creator->name;
        }

        return  [
            'menu_id' => $this['menu_id'],
            'parent_id' => $this['parent_id'],
            'menu_name' => $this['menu_name'],
            'group_name' => $this['group_name'],
            'created_by' => $created_by,
            'permissions_name' => $this['permissions_name'],
            'display_permissions_name' => $this['display_permissions_name'],
            'read' => $this['read'],
            'update' => $this['update'],
            'delete' => $this['delete'],
            'create' => $this['create'],
            "created_at"    => $this['created_at'] ? Carbon::parse($this['created_at'])->format(UI_DATE_FORMAT) : "",
            "deleted_at"    => $this['deleted_at'] ? Carbon::parse($this['deleted_at'])->format(UI_DATE_FORMAT) : "",

        ];
    }
}
