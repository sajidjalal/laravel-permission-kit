<?php

namespace InsureTech\RolePermission\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use InsureTech\RolePermission\Services\CommonViewDataService;

abstract class Controller extends BaseController
{
    protected function getCommonViewData($request, $permission_name, $accessMenu = [], $title = '')
    {
        $service = app(CommonViewDataService::class);
        return $service->getCommonViewData($request, $permission_name, $accessMenu, $title);
    }
}
