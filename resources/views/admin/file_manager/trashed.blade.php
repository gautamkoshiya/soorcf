@extends('shared.layout-admin')
@section('title', 'Trashed Files')

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
                    <li class="breadcrumb-item active">Trashed Files</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Trashed Files</h2>
                    <h5 class="required" style="float: right;"></h5>
                    <div class="table-responsive m-t-40">
                        <table id="trash_files" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <th>SR#</th>
                                <th>CODE#</th>
                                <th>Report Type</th>
                                <th>FileType</th>
                                <th>Description</th>
                                <th>Report Date</th>
                                <th>Deleted By</th>
                                <th>Deleted Time</th>
                                <th>View</th>
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
            $('#trash_files').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('trash_files') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'FileCode',
                        name: 'FileCode'
                    },
                    {
                        data: 'report_type',
                        name: 'report_type'
                    },
                    {
                        data: 'file_type',
                        name: 'file_type'
                    },
                    {
                        data: 'Description',
                        name: 'Description'
                    },
                    {
                        data: 'reportDate',
                        name: 'reportDate'
                    },
                    {
                        data: 'user',
                        name: 'user'
                    },
                    {
                        data: 'deleted_at',
                        name: 'deleted_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]]
            });
        });
    </script>
@endsection
