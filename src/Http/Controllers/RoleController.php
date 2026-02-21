<?php

namespace InsureTech\RolePermission\Http\Controllers;

use InsureTech\RolePermission\Http\Resources\RoleResource;
use InsureTech\RolePermission\Models\RolesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function roleView(Request $request)
    {
        $data = $this->getCommonViewData(
            $request,
            'role_management',
            ['read'],
            'Role List'
        );

        $data['roleList'] = getRoleList($data['userInfo'], false, $data['userInfo']['role_id']);
        $data['reportingRoleList'] = getReportingRoleList($data['userInfo']);

        return view('role-permission::crm.roles.roles_list', $data);
    }

    public function get_role_query($request, $userInfo, $is_all_count = false)
    {
        $roleQuery = RolesModel::select('*');

        if (!in_array($userInfo['role_id'], [SUPER_ADMIN_ROLE_ID])) {
            $excludedRoles = [SUPER_ADMIN_ROLE_ID];

            if (!$userInfo['is_admin']) {
                $subordinateRoleIds = getAllSubordinateRoleIds($userInfo['role_id']);
                $subordinateRoleIds[] = $userInfo['role_id'];

                $roleQuery->whereIn('id', $subordinateRoleIds)
                    ->whereNotIn('id', $excludedRoles);
            } else {
                $roleQuery->whereNotIn('id', $excludedRoles);
            }
        }

        if (!$is_all_count && !empty($request['role_id'])) {
            $roleQuery->where('roles.id', $request['role_id']);
        }
        $roleQuery->selectRaw('ROW_NUMBER() OVER (ORDER BY roles.id DESC) AS sr_number')->orderBy('roles.id', 'DESC');

        if (!$is_all_count && !empty($request['reporting_id'])) {
            $roleQuery->where('roles.reporting_role_id', $request['reporting_id']);
        }

        return $roleQuery;
    }

    public function getRoles(Request $request)
    {
        $userInfo = getCurrentUser();
        $status = false;
        $status_code = 422;
        $recordsTotal = $recordsFiltered = 0;
        $data = $accessRight = [];
        $response_message = UN_AUTHORIZE_MSG;

        $accessRightJsonResponse = checkRoleHasPermission($request, $userInfo, 'role_management', ['read']);

        if (!in_array($accessRightJsonResponse->getStatusCode(), ERROR_CODE_ARRAY)) {
            $requestData = $request->all();

            $mainQuery = $this->get_role_query($requestData, $userInfo);
            $allQuery  = $this->get_role_query($requestData, $userInfo, true);

            $data = data_table_updated_query_filter(clone $mainQuery, $requestData, true, ['roles.id', 'desc'])->get();
            $recordsTotal = data_table_updated_query_filter(clone $allQuery, [], false)->count();
            $recordsFiltered = data_table_updated_query_filter(clone $mainQuery, $requestData, false)->count();

            $accessData = json_decode($accessRightJsonResponse->getContent());
            $accessRight = $accessData->data->access_right;

            $status = true;
            $status_code = 200;
            $response_message = '';
        }

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data' => RoleResource::collection($data),
            'message' => $response_message,
            'accessRight' => $accessRight,
            'status' => $status,
        ], $status_code);
    }

    public function addEditRole(Request $request)
    {
        $response_message = ERROR_MESSAGE;
        $data = $errors_fields = [];
        $status = false;
        $status_code = 422;

        $userInfo = getCurrentUser();
        $brokerInfo = getBrokerInfo();
        if (isset($request->id)) {
            $accessMenu = checkRoleHasPermission($request, $userInfo, 'role_management', ['update']);
        } else {
            $accessMenu = checkRoleHasPermission($request, $userInfo, 'role_management', ['create']);
        }

        if (!in_array($accessMenu->getStatusCode(), ERROR_CODE_ARRAY)) {
            $rules = [
                'name' => [
                    'required',
                    'max:100',
                    'regex:/^[A-Za-z_ ]+$/',
                    Rule::unique('roles', 'role_name')->where(function ($query) use ($request) {
                        $query->whereNull('deleted_at');
                        if (isset($request->id)) {
                            $query->where('id', '!=', $request->id);
                        }
                    }),
                ],
                'role_prefix' => [
                    'required_without:id',
                    'max:7',
                    Rule::unique('roles', 'role_prefix')->where(function ($query) use ($request) {
                        $query->whereNull('deleted_at');
                        if (isset($request->id)) {
                            $query->where('id', '!=', $request->id);
                        }
                    }),
                ],
                'status' => 'required|between:0,1',
                'description' => 'sometimes|nullable|max:150|regex:/^[a-zA-Z0-9\s\-\.\/;,]*$/',
            ];

            if (!in_array($request->id, [SUPER_ADMIN_ROLE_ID, ADMIN_ROLE_ID])) {
                $rules['reporting_role_id'] = [
                    'required',
                    'exists:roles,id,deleted_at,NULL',
                ];
            }

            $messages = [
                'name.regex' => 'The :attribute only accepts characters.',
                'name.unique' => 'The Role Name already has been taken.',
                'role_prefix.unique' => 'The Role Prefix has already been taken.',
                'reporting_role_id.required_without' => 'The reporting role field is required.',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);

            try {
                if ($validator->fails()) {
                    $response_message = $validator->errors()->first();
                    $errors_fields = $validator->errors();
                } else {
                    $fields = [
                        'role_name' => $request->name,
                        'reporting_role_id' => $request->reporting_role_id ?? 0,
                        'role_prefix' => $request->role_prefix,
                        'display_name' => $request->name,
                        'description' => $request->description,
                        'is_admin' => $request->is_admin ?? 0,
                        'status' => $request->status,
                    ];

                    $role_id = false;
                    if (isset($request->id)) {
                        $fields['updated_by'] = $userInfo['id'];
                        $fields['updated_at'] = now();

                        $response_message = 'Role Updated Successfully.';
                        $status = RolesModel::where('id', $request->id)->update($fields);
                        $role_id = $request->id;

                        if ($brokerInfo['update_notification'] ?? false) {
                            if ($status && class_exists(\App\Http\Controllers\NotificationController::class)) {
                                $notificationController = app(\App\Http\Controllers\NotificationController::class);
                                $notificationController->createNotification(
                                    'role_update',
                                    $userInfo['role_id'],
                                    'high',
                                    'Role Management',
                                    'Role Updated',
                                    [
                                        'message' => "Role {$request->name} updated successfully.",
                                    ],
                                    route('roles'),
                                    $userInfo['id'],
                                );
                            }
                        }
                    } else {
                        $fields['created_by'] = $userInfo['id'];
                        $fields['created_at'] = now();
                        $tableStatus = $status = RolesModel::create($fields);
                        $response_message = 'Role Created Successfully.';
                        addRolePermission($tableStatus->id, $userInfo['id']);

                        if ($tableStatus && class_exists(\App\Http\Controllers\NotificationController::class)) {
                            $notificationController = app(\App\Http\Controllers\NotificationController::class);
                            $notificationController->createNotification(
                                'role_create',
                                $userInfo['role_id'],
                                'high',
                                'Role Management',
                                'Role Created',
                                [
                                    'message' => "Role {$tableStatus->display_name} created successfully.",
                                ],
                                route('roles'),
                                $userInfo['id']
                            );
                        }
                    }

                    $status = true;
                    $status_code = 200;
                }
            } catch (\Throwable $th) {
                Log::critical('RoleController::addEditRole()');
                Log::critical($th);
                saveErrorLog($th->getMessage(), 0, $th->getLine(), $th->getFile());
                trackBrokerErrorLog($userInfo, $th->getMessage(), $th->getLine(), $th->getFile());
            }
        } else {
            $response_message = UN_AUTHORIZE_MSG;
        }

        return response()->json(['status' => $status, 'is_show_alert' => IS_SHOW_ALERT, 'is_toast_alert' => IS_TOAST_ALERT, 'errors_fields' => $errors_fields, 'message' => $response_message, 'redirect' => route('roles')], $status_code);
    }

    public function roleDelete(Request $request)
    {
        $data = [];
        $success = false;
        $status_code = 422;
        $errors_fields = [];
        $response_message = 'something went wrong please try again.';
        $userInfo = getCurrentUser();

        try {
            $rules = [
                'role_id' => 'required|integer|exists:roles,id,deleted_at,NULL',
            ];

            $messages = [
                //
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                $response_message = $validator->errors()->first();
                $errors_fields = $validator->errors();
            } else {
                $brokerInfo = getBrokerInfo();
                $response_message = UN_AUTHORIZE_MSG;
                $accessMenu = checkRoleHasPermission($request, $userInfo, 'role_management', ['delete']);

                if (!in_array($accessMenu->getStatusCode(), ERROR_CODE_ARRAY)) {
                    DB::beginTransaction();

                    $query = RolesModel::find($request->role_id);

                    if ($query) {
                        $role_name = $query->display_name;

                        $query->deleted_at = now();
                        $query->deleted_by = $userInfo['id'];

                        $query->save();

                        if ($brokerInfo['delete_notification'] ?? false) {
                            if (class_exists(\App\Http\Controllers\NotificationController::class)) {
                                $notificationController = app(\App\Http\Controllers\NotificationController::class);
                                $notificationController->createNotification(
                                    'role_delete',
                                    $userInfo['role_id'],
                                    'high',
                                    'Role Management',
                                    'Role Deleted',
                                    [
                                        'message' => "Role {$role_name} deleted successfully.",
                                    ],
                                    route('roles'),
                                    $userInfo['id'],
                                );
                            }
                        }
                        $success = true;
                        $status_code = 200;
                        $response_message = 'Role Deleted Successfully';
                    }

                    DB::commit();
                }
            }
        } catch (\Exception $e) {
            Log::critical('Error in RoleController::roleDelete', [
                'exception_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $status_code = 500;
            $response_message = ERROR_MESSAGE;
            Log::info('Request Parameters:', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'parameters' => $request->all(),
            ]);
            saveErrorLog($e->getMessage(), 0, $e->getLine(), $e->getFile());
            trackBrokerErrorLog($e->getMessage(), $userInfo, $e->getLine(), $e->getFile());
        }

        return response()->json(
            [
                'success' => $success,
                'data' => $data,
                'message' => $response_message,
                'errors_fields' => $errors_fields,
            ],
            $status_code,
        );
    }
}
