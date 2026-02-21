<?php

use InsureTech\RolePermission\Http\Controllers\RoleController;
use InsureTech\RolePermission\Http\Controllers\PermissionsController;
use Illuminate\Support\Facades\Route;

// roles routes start
Route::get('roles', [RoleController::class, 'roleView'])->name('roles');
Route::post('get-roles', [RoleController::class, 'getRoles'])->name('get.roles');
Route::any('role-add-edit', [RoleController::class, 'addEditRole'])->name('add.edit.role');
Route::any('role-delete', [RoleController::class, 'roleDelete'])->name('role.delete');
// roles routes end

// permission route start
Route::get('permission', [PermissionsController::class, 'permissionView'])->name('permission');
Route::post('/add-update-permission', [PermissionsController::class, 'addUpdatePermission'])->name('add.update.permission');
Route::get('get-role-permission', [PermissionsController::class, 'getRolePermission'])->name('get.role.permission');
// permission route end
