<?php

namespace InsureTech\RolePermission\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $role_name = DB::table('roles')->where('id', $this->reporting_role_id)->pluck('display_name')->first();

        return [
            'sr_no' => $this->sr_number,
            'id' => $this->id,
            'reporting' => $role_name,
            'reporting_role_id'=> $this->reporting_role_id,
            'role_prefix' => $this->role_prefix,
            'name' => $this->display_name,
            'description' => $this->description,
            'is_admin' => $this->is_admin,
            'status' => $this->status,
            'created_by' => $this->createdBy->first_name ?? '',
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format(UI_DATE_FORMAT) : '',
        ];
    }
}
