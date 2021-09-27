@extends('shared.layout-admin')
@section('title', 'Customer App Management')

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
                            <li class="breadcrumb-item active">Customer App Management</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-10 col-sm-2">
                                    <h2 class="card-title">Customer App Management</h2>
                                    <h6>First enable Access and update login email and password</h6>
                                </div>
                            </div>
                            <div class="table-responsive m-t-40">
                                <table id="customers_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>SR#</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th width="100">Status</th>
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
        function change_app_status(e)
        {
            var id=e;
            id=id.split('_');
            id=id[1];
            if (id > 0)
            {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ URL('ChangeCustomerAppStatus') }}/"+id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        location.reload();
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            }
        }
    </script>
    <script>
        $(document).ready(function () {
            $('#customers_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('customer_app') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'Name',
                        name: 'Name'
                    },
                    {
                        data: 'Mobile',
                        name: 'Mobile'
                    },
                    {
                        data: 'app_access',
                        name: 'app_access',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]],
            });
        });
    </script>
@endsection
