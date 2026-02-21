<?php

namespace InsureTech\RolePermission\Http\Controllers;

use InsureTech\RolePermission\Http\Resources\RolePermissionsResource;
use InsureTech\RolePermission\Models\RolePermissionsModel;
use InsureTech\RolePermission\Models\RolesModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PermissionsController extends Controller
{
    public function permissionView(Request $request)
    {
        $data = $this->getCommonViewData(
            $request,
            'permission_management',
            ['read'],
            'Permission List - Pages'
        );

        $data['roleList'] = getRoleList($data['userInfo'], "", false);
        $data['reportingRoleList'] = getReportingRoleList($data['userInfo']);

        return view('role-permission::crm.permission.permission_list', $data);
    }

    public function addUpdatePermission(Request $request)
    {
        $userInfo = getCurrentUser();
        $errors_fields = $data = [];
        $status = false;
        $status_code = 422;
        $response_message = UN_AUTHORIZE_MSG;

        $accessMenu = checkRoleHasPermission($request, $userInfo, 'permission_management', ['update', 'create']);
        if (!in_array($accessMenu->getStatusCode(), ERROR_CODE_ARRAY)) {
            $rules = [
                'role_id' => 'required|exists:roles,id,deleted_at,NULL',
                'menu_id.*' => 'required|numeric|exists:master_menu,id,deleted_at,NULL',
            ];

            $messages = [
                //
            ];
            $validator = Validator::make($request->all(), $rules, $messages);

            try {
                if ($validator->fails()) {
                    $response_message = $validator->errors()->first();
                    $errors_fields = $validator->errors();
                } else {
                    $response_message = UN_AUTHORIZE_MSG;

                    foreach ($request->menu_id as $key => $value) {
                        $fields = [
                            'role_id' => $request->role_id,
                            'menu_id' => $request->menu_id[$key],
                            'create' => $request->create[$key],
                            'read' => $request->read[$key],
                            'update' => $request->update[$key],
                            'delete' => $request->delete[$key],
                            'status' => $request->status ?? 1,
                        ];
                        $checkCount = RolePermissionsModel::where([
                            'role_id' => $request->role_id,
                            'menu_id' => $request->menu_id[$key],
                        ])->count();

                        if ($checkCount > 0) {
                            $fields['updated_by'] = $userInfo['id'];
                            $fields['updated_at'] = now();
                            $status = RolePermissionsModel::where([
                                'role_id' => $request->role_id,
                                'menu_id' => $request->menu_id[$key],
                            ])->update($fields);
                        } else {
                            $fields['created_by'] = $userInfo['id'];
                            $fields['created_at'] = now();
                            $status = RolePermissionsModel::create($fields);
                        }
                    }
                    $response_message = 'Permissions Updated Successfully.';

                    $status = true;
                    $status_code = 200;

                    flushQueryCache($request->role_id);
                }
            } catch (\Throwable $th) {
                Log::critical('PermissionsController::addUpdatePermission()');
                Log::critical($th);
                $response_message = 'Server Error';

                saveErrorLog($th->getMessage(), 0, $th->getLine(), $th->getFile());
                trackBrokerErrorLog($th->getMessage(), $userInfo, $th->getLine(), $th->getFile());
            }
        }
        return response()->json(['status' => $status, 'fields' => $errors_fields, 'message' => $response_message], $status_code);
    }

    public function getRolePermission(Request $request)
    {
        $errors_fields = $data = [];
        $userInfo = getCurrentUser();
        $data['userInfo'] =  $userInfo;
        $data['financialYearList'] = getFinancialYearList();
        $status = false;
        $status_code = 422;
        $response_message = UN_AUTHORIZE_MSG;

        $accessMenu = checkRoleHasPermission($request, $data['userInfo'], 'permission_management');

        if (!in_array($accessMenu->getStatusCode(), ERROR_CODE_ARRAY)) {
            $rules['role_id'] = 'required|exists:roles,id,deleted_at,NULL';
            $messages = [
                'role_name.regex' => 'The :attribute only accepts characters.',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);

            try {
                if ($validator->fails()) {
                    $response_message = $validator->errors()->first();
                    $errors_fields = $validator->errors();
                } else {
                    $role_list = getRoleMenuPermissionsList($data['userInfo'], $request->role_id, true);

                    $data['role_list'] = RolePermissionsResource::collection($role_list);
                    $response_message = 'success';
                    $status = true;
                    $status_code = 200;
                }
            } catch (Exception $e) {
                Log::critical('PermissionsController::getRolePermission()');
                Log::critical($e);
                $response_message = 'Server Error';
                saveErrorLog($e->getMessage(), 0, $e->getLine(), $e->getFile());
                trackBrokerErrorLog($e->getMessage(), $userInfo, $e->getLine(), $e->getFile());
            }
        }
        return response()->json(['status' => $status, 'data' => $data, 'fields' => $errors_fields, 'message' => $response_message], $status_code);
    }
}
