@extends('layout.app')

@section('page-script')
    <script src="{{ asset('js/logic/blank.js') }}"></script>

    <script>
        var permission_list_route = '{{ route('get.role.permission') }}';
    </script>
    <script src="{{ asset(config('role-permission.asset_path') . '/js/logic/roles/permission_list.js?ver=' . SCRIPT_VERSION) }}"></script>
@endsection

<style>
    .is_parent {
        color: #ffbb3d;
        font-weight: bolder;
    }
</style>
@section('layoutContent')
    @if (SHOW_HAMBURG)
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-6">
                        <h4>Permission</h4>
                    </div>
                    <div class="col-6">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('permission') }}">
                                    <svg class="stroke-icon">
                                        <use href="{{ asset('images/svg/icon-sprite.svg') }}#stroke-home">
                                        </use>
                                    </svg></a></li>
                            <li class="breadcrumb-item">Permission</li>
                            <li class="breadcrumb-item active">Permission List</li>
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
                                <h4>Permissions List</h4>
                                <span>
                                    Each role (category) includes the four predefined permissions shown below.
                                </span>
                            </div>
                            <div class="col-2 text-end">
                                {{-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal"> --}}
                            </div>
                        </div>
                        <p>


                    </div>

                    <div class="card-body">
                        <!-- Add Permission form -->
                       
                        <form id="add_update_permission_form" action="{{ route('add.update.permission') }}" class="row g-3"
                            onsubmit="return false">
                            <div class="col-12 mb-4">
                                <small class=" fw-semibold d-block mb-3">Role Name</small>
                                @foreach ($roleList as $key => $value)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="role_id"
                                            id="{{ $value->id }}" value="{{ $value->id }}" />
                                        <label class="form-check-label"
                                            for="{{ $value->id }}">{{ $value->display_name }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-12">
                                <h5>Role Permissions</h5>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
                                    <label class="form-check-label" for="selectAllCheckbox">
                                        Select All
                                    </label>
                                </div>
                                <!-- Permission table -->
                                <div class="table-responsive">
                                    <table class="table table-flush-spacing" id="permission_table">
                                        <thead>
                                            <th>Sr. No.</th>
                                            <th>Select</th>
                                            <th>Permissions</th>
                                            <th>Create</th>
                                            <th>Read</th>
                                            <th>Update</th>
                                            <th>Delete</th>
                                        </thead>

                                        <tbody id= "allMenus">
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Permission table -->
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <br>
                                    <button type="submit" id="btn_submit"
                                        class="btn btn-primary waves-effect waves-light">Submit</button>
                                </div>
                            </div>
                        </form>
                        <!--/ Add Permission form -->

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
