<?php

namespace InsureTech\RolePermission\Services;

class CommonViewDataService
{
    public function getCommonViewData($request, $permission_name, $accessMenu = [], $title = '')
    {
        $userInfo = getCurrentUser();
        $brokerInfo = getBrokerInfo();
        $financialYearList = getFinancialYearList();
        $menuListSideBar = getRoleMenuPermissions($userInfo);

        $accessPermission = checkRoleHasPermission($request, $userInfo, $permission_name, $accessMenu);

        $accessRight = [];

        if (!in_array($accessPermission->getStatusCode(), ERROR_CODE_ARRAY)) {
            $accessRight = $accessPermission->original['data']['access_right'];
        }

        $menuListSideBar = getRoleMenuPermissions($userInfo);
        $grouped = $menuListSideBar->groupBy('group_name');
        $menuGrouped = $grouped->all();

        return [
            'title' => $title,
            'userInfo' => $userInfo,
            'brokerInfo' => $brokerInfo,
            'financialYearList' => $financialYearList,
            'menuListSideBar' => $menuListSideBar,
            'menuGrouped' => $menuGrouped,
            'access_right' => $accessRight,
            'accessMenu' => $accessPermission,
        ];
    }
}
