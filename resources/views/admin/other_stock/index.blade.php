@extends('shared.layout-admin')
@section('title', 'Other Stock')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <a href="{{ route('GetOtherStockReport') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Get Report</button></a>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">Other Stock</li>
                        </ol>
                        <a href="{{ route('other_stocks.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New Entry</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">Other Stock</h3>
                            <div class="table-responsive m-t-40">
                                <table id="other_stock_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th style="width: 100px">SR#</th>
                                        <th style="width: 100px">Date</th>
                                        <th style="width: 150px">In</th>
                                        <th style="width: 150px">Out</th>
                                        <th style="width: 150px">Differance</th>
                                        <th style="width: 150px">Description</th>
                                        <th style="width: 40px">Action</th>
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
            $('#other_stock_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('other_stocks.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'createdDate',
                        name: 'createdDate'
                    },
                    {
                        data: 'in',
                        name: 'in'
                    },
                    {
                        data: 'out',
                        name: 'out'
                    },
                    {
                        data: 'differance',
                        name: 'differance'
                    },
                    {
                        data: 'Description',
                        name: 'Description'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]],
                pageLength : 10,
            });
        });
    </script>
@endsection
