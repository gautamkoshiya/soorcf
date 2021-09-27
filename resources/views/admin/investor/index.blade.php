@extends('shared.layout-admin')
@section('title', 'Investor')

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
                            <li class="breadcrumb-item active">Investor</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-10 col-sm-2"><h2 class="card-title">Investor</h2></div>
                                <div class="col-md-1 col-sm-2"><a href="{{ route('investor.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New Investor</button></a></div>
                            </div>
                            <div class="table-responsive m-t-40">
                                <table id="investor_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>SR#</th>
                                        <th>Name</th>
                                        <th>Share % </th>
                                        <th>Mobile</th>
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
            $('#investor_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('investor.index') }}",
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
                        data: 'SharePercentage',
                        name: 'SharePercentage'
                    },
                    {
                        data: 'Mobile',
                        name: 'Mobile'
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
