@extends('shared.layout-admin')
@section('title', 'user Edit')

@section('content')


    <!-- ============================================================== -->
    <!-- End Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Page wrapper  -->
    <!-- ============================================================== -->
    <div class="page-wrapper">
        <!-- ============================================================== -->
        <!-- Container fluid  -->
        <!-- ============================================================== -->
        <div class="container-fluid">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">User  Modification</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">User</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-eye"></i> List</button>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Start Page Content -->
            <!-- ============================================================== -->
            <!-- Row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">User</h4>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data">
                                @csrf
                                @method("PUT")
                                <div class="form-body">
                                    <h3 class="card-title">Modification</h3>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Full Name</label>
                                                <input type="text" id="name" name="name" value="{{ $user->name }}" class="form-control" placeholder="Enter Full Name">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Company Selection</label>
                                                <select class="form-control custom-select select2" name="company_id">
                                                    <option>--Select Company--</option>
                                                    @foreach($companies as $company)
                                                        <option value="{{ $company->id }}" {{ ( $company->id == $user->company_id) ? 'selected' : '' }}>{{ $company->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>



                                    </div>


                                    <div class="row">

                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone</label>
                                                <input type="text" name="contactNumber" value="{{ $user->contactNumber }}" placeholder="Mobile Number" class="form-control">
                                            </div>
                                        </div>
                                        <!--/span-->
                                        <!--/span-->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Address</label>
                                                <input type="text" name="address" placeholder="Enter Address" value="{{ $user->address }}" class="form-control">
                                            </div>
                                        </div>
                                        <!--/span-->
                                    </div>

                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="selectRoles">Select Roles</label>
                                                <select name="roles[]" id="selectRoles" class="form-control" multiple>
                                                    {{--                    <option value="0">Parent Category</option>--}}
                                                    @if(!$roles->IsEmpty())
                                                        @foreach($roles as $role)
                                                            <option
                                                                @if(in_array($role->id,
                                                                $user->roles->pluck('id')->toArray()))
                                                                {{'selected'}}
                                                                @endif
                                                                value="{{ $role->id }}">{{ $role->Name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <!--/span-->

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>File</label>
                                                <input type="file" name="fileUpload" placeholder="" class="form-control">
                                            </div>
                                        </div>
                                    </div>


                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update User</button>
                                    <button type="button" class="btn btn-inverse">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Row -->


        </div>
        <!-- ============================================================== -->
        <!-- End Container fluid  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Page wrapper  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->

    <script>
        $(document).ready(function () {
            // $('#paymentTermAll').hide();
            //
            // $("#customRadio1 input:radio").click(function() {
            //
            //     alert("clicked");
            //
            // });

            //
            // $('.c1').click(function () {
            //     $('#paymentTermAll').show();
            // });
            // $('.c2').click(function () {
            //     $('#paymentTermAll').hide();
            // });
        });
    </script>


@endsection
