@extends('shared.layout-admin')
@section('title', 'Project')

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
                            <li class="breadcrumb-item active">Project</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-10 col-sm-2"><h4 class="card-title">Project</h4></div>
                                <div class="col-md-1 col-sm-2"><a href="{{ route('projects.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New Project</button></a></div>
                            </div>
                            <h6 class="card-subtitle">All Projects</h6>
                            <div class="table-responsive m-t-40">
                                <table id="project_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead><tr>
                                        <th>SR#</th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Contact</th>
                                        <th>Email</th>
                                        <th>TRN</th>
                                        <th>FAX</th>
                                        <th>Registration Date</th>
                                        <th>Renewal Date</th>
                                        <th width="100">Status</th>
                                        <th width="100">Action</th>
                                    </tr></thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function change_status(e)
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
                    url: "{{ URL('ChangeProjectStatus') }}/"+id,
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
            $('#project_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('projects.index') }}",
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
                        data: 'Address',
                        name: 'Address'
                    },
                    {
                        data: 'Contact',
                        name: 'Contact'
                    },
                    {
                        data: 'Email',
                        name: 'Email'
                    },
                    {
                        data: 'TRN',
                        name: 'TRN'
                    },
                    {
                        data: 'FAX',
                        name: 'FAX'
                    },
                    {
                        data: 'registration_date',
                        name: 'registration_date'
                    },
                    {
                        data: 'renewal_date',
                        name: 'renewal_date'
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
                ],
                order: [[ 0, "desc" ]],
                dom: 'Blfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
            });
        });
    </script>
@endsection
