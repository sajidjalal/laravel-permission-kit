<!-- Add Role Modal -->

<input type="hidden" id="role_delete" value="{{ route('role.delete') }}">
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-role">
        <div class="modal-content">
            <form id="addRoleForm" action="{{ route('add.edit.role') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Add/Update Role</h5>
                    <button class="btn-close py-0" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Add role form -->

                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-4">
                            <label class="form-label" for="name">Role Name</label>
                            <input type="text" id="name" name="name" class="form-control"
                                placeholder="Enter a role name" tabindex="-1" />
                        </div>
                        <div class="col-6 mb-4">
                            <label class="form-label" for="name">Reporting</label>
                            <select id="reporting_role_id" name="reporting_role_id" class="form-select select2"
                                data-allow-clear="true">
                                <option value="" disabled selected></option>
                                @foreach ($reportingRoleList as $key => $value)
                                    <option value="{{ $value->id }}">{{ $value->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 mb-4">
                            <label class="form-label" for="role_prefix">Role Prefix
                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top"
                                    title="This is a one-time entry and cannot be changed."></i>
                            </label>
                            <input type="text" id="role_prefix" name="role_prefix" class="form-control"
                                placeholder="Enter a Role Prefix" tabindex="-1" />
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="status" class="form-label">Status
                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top"
                                    title="If this inactive,Then all user of this role can not able to login."></i>
                            </label>
                            <select id="status" name="status" class="form-select select2" data-allow-clear="true">
                                <option value="" disabled selected></option>
                                <option value="1" selected>{{ ACTIVE }}</option>
                                <option value="0">{{ INACTIVE }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="is_admin" class="form-label">Is Global Access
                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top"
                                    title="If this yes,Then all user of this role can have all data access which permission assign to it.For Admin, POS and Customer this can not be changed"></i>
                            </label>
                            <select id="is_admin" name="is_admin" class="form-select select2" data-allow-clear="true">
                                <option value="" disabled selected></option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label" for="description">Role Description</label>
                            <input type="text" id="description" name="description" class="form-control"
                                placeholder="Enter role description" tabindex="-1" />
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <input type="hidden" name="id" id="id" value="">
                    <button type="submit" id="btn-save" class="btn btn-primary me-sm-3 me-1">Submit</button>
                    <button type="button" class="btn alert-light-primary" data-bs-dismiss="modal"
                        id="backbtn">Cancel</button>

                </div>
                <!-- Add role form END -->
            </form>
        </div>
    </div>
</div>
<!--/ Add Role Modal -->
