@extends('shared.layout-admin')
@section('title', 'Role')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">Role</li>
                        </ol>
                        <a href="{{ route('roles.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New role</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Role</h4>
                            <h6 class="card-subtitle">All Role</h6>
                            <div class="table-responsive m-t-40">
                                <table id="roles_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th width="100">Is Active</th>
                                        <th width="100">Action</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#roles_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('roles.index') }}",
                },
                columns:[
                    {
                        data: 'Name',
                        name: 'Name'
                    },
                    {
                        data: 'isActive',
                        name: 'isActive',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ]
            });
        });
    </script>
@endsection
