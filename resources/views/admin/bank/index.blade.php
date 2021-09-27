@extends('shared.layout-admin')
@section('title', 'Banks List')

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
                            <li class="breadcrumb-item active">Bank</li>
                        </ol>
                        <a href="{{ route('banks.create') }}"><button type="button" class="btn btn-info d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Banks</h4>
                            <h6 class="card-subtitle">All Banks</h6>
                            <div class="table-responsive m-t-40">
                                <table id="banks_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>Bank Name</th>
                                        <th>Account Number</th>
                                        <th>Branch</th>
                                        <th>Contact Number</th>
                                        <th width="100">IsActive</th>
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
            $('#banks_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('banks.index') }}",
                },
                columns:[
                    {
                        data: 'Name',
                        name: 'Name'
                    },
                    {
                        data: 'Description',
                        name: 'Description'
                    },
                    {
                        data: 'Branch',
                        name: 'Branch'
                    },
                    {
                        data: 'contactNumber',
                        name: 'contactNumber'
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
