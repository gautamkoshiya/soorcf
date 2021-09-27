@extends('shared.layout-admin')
@section('title', 'Task List')

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
                            <li class="breadcrumb-item active">My Tasks</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">My Tasks</h2>
                            <div class="table-responsive m-t-40">
                                <table id="task_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>SR#</th>
                                        <th>Task</th>
                                        <th>Assigned By</th>
                                        <th>Date</th>
                                        <th>Deadline</th>
                                        <th>Code</th>
                                        <th>Note</th>
                                        <th>Status</th>
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
                    url: "{{ URL('ChangeTaskStatus') }}/"+id,
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
            $('#task_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('tasks.index') }}",
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
                        data: 'assigned_by',
                        name: 'assigned_by'
                    },
                    {
                        data: 'Date',
                        name: 'Date'
                    },
                    {
                        data: 'CompletionTime',
                        name: 'CompletionTime'
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'Note',
                        name: 'Note'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]]
            });
        });
    </script>
@endsection
