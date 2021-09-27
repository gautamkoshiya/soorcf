@extends('shared.layout-admin')
@section('title', 'Bank to Bank Transfer List')

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
                            <li class="breadcrumb-item active"><a style="color: #01c0c8;" href="{{route('GetLoginReport')}}">Bank to Bank Transfer</a></li>
                        </ol>
                        <a href="{{ route('bank_to_banks.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Bank To Bank Transfer</h2>
                            <div class="table-responsive m-t-40">
                                <table id="bank_to_bank_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>SR#</th>
                                        <th>Amount</th>
                                        <th>From Bank</th>
                                        <th>To Bank</th>
                                        <th>Reference</th>
                                        <th>Date</th>
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
            $('#bank_to_bank_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('bank_to_banks.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'Amount',
                        name: 'Amount'
                    },
                    {
                        data: 'FromBank',
                        name: 'FromBank'
                    },
                    {
                        data: 'ToBank',
                        name: 'ToBank'
                    },
                    {
                        data: 'Reference',
                        name: 'Reference'
                    },
                    {
                        data: 'depositDate',
                        name: 'depositDate',
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
