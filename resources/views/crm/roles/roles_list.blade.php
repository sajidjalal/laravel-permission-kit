@extends('layout.app')

@section('page-script')
    <script>
        var role_rote = '{{ route('get.roles') }}';
        window.rolePermissionRoutes = {
            getRoles: '{{ route('get.roles') }}',
            roleDelete: '{{ route('role.delete') }}'
        };
    </script>
    <script src="{{ asset(config('role-permission.asset_path') . '/js/logic/roles/role_list.js?ver=' . SCRIPT_VERSION) }}"></script>
    <script src="{{ asset(config('role-permission.asset_path') . '/js/logic/modal/role_add_modal.js?ver=' . SCRIPT_VERSION) }}"></script>
@endsection

@section('layoutContent')
    @if (SHOW_HAMBURG)
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-6">
                        <h4>Roles</h4>
                    </div>
                    <div class="col-6">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('roles') }}">
                                    <svg class="stroke-icon">
                                        <use href="{{ asset('images/svg/icon-sprite.svg') }}#stroke-home">
                                        </use>
                                    </svg></a></li>
                            <li class="breadcrumb-item">Dashboard</li>
                            <li class="breadcrumb-item active">Roles</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Container-fluid starts-->
    <div class="container-fluid">
        <div class="row size-column">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header pb-0 card-no-border">
                        <div class="row">
                            <div class="col-10">
                                <h4>Roles Configuration</h4>
                                <span>
                                    A role provided access to predefined menus and features so that depending on assigned
                                    role an
                                    administrator can have access to what user needs.
                                </span>
                            </div>
                            <div class="col-2 text-end">

                                @if ($access_right->create)
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal"
                                        id="btnAddRoleModal">
                                        Add New Role <i class="fa fa-plus"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <p>


                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 position-relative">
                                <label class="form-label" for="reporting_id">Reporting Role</label>
                                <select class="form-select select2" id="reporting_id" name="reporting_id"
                                    data-placeholder="Select Reporting Role" data-allow-clear="true">
                                    <option selected="" value="">All</option>
                                    @foreach ($roleList as $key => $value)
                                        <option value="{{ $value->id }}">{{ $value->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="table table-responsive custom-scrollbar">
                            <table id="roleDataTable" class="table table-striped " style="width:100%">
                                <thead>
                                    <tr>
                                        <th data-hideCol="1">Sr No</th>
                                        <th data-hideCol="1">Action</th>
                                        <th >Role Name</th>
                                        <th>Prefix</th>
                                        <th data-hideCol="1">Reporting</th>
                                        <th data-hideCol="1">Description</th>
                                        <th data-hideCol="1">Status</th>
                                        <th data-dateCol="1">Created At</th>
                                    </tr>
                                </thead>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Container-fluid Ends-->

    <!-- Add Role Modal -->
    @include('role-permission::modals.role_add_modal')
    <!-- / Add Role Modal -->
@endsection
