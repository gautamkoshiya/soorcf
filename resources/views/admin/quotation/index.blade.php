@extends('shared.layout-admin')
@section('title', 'Quotation')

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
                            <li class="breadcrumb-item active">Quotation</li>
                        </ol>
                        <a href="{{ route('quotations.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New Quotation</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">Quotation</h3>
                            <h5 class="required" style="float: right;">[search by Quotation Number ]</h5>
                            <div class="table-responsive m-t-40">
                                <table id="quotation_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th style="width: 100px">SR#</th>
                                        <th style="width: 100px">QuotationNumber</th>
                                        <th style="width: 150px">FromDate</th>
                                        <th style="width: 150px">DueDate</th>
                                        <th style="width: 150px">Customer</th>
                                        <th style="width: 150px">Project</th>
                                        <th>Subtotal</th>
                                        <th>VAT</th>
                                        <th>Discount</th>
                                        <th>GrandTotal</th>
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
        function show_detail(e)
        {
            var id=e;
            id=id.split('_');
            id=id[1];
            if (id > 0)
            {
                $.ajax({
                    url: "{{ URL('PrintQuotation') }}/"+id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        window.open(result.url,'_blank');
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            }
        }
    </script>
    <script>
        function show_detail1(e)
        {
            var id=e;
            id=id.split('_');
            id=id[1];
            if (id > 0)
            {
                $.ajax({
                    url: "{{ URL('PrintQuotation1') }}/"+id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        window.open(result.url,'_blank');
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            }
        }
    </script>

    {{--<script>
        $(document).ready(function () {
            $('#quotation_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('quotations.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'QuotationNumber',
                        name: 'QuotationNumber'
                    },
                    {
                        data: 'FromDate',
                        name: 'FromDate'
                    },
                    {
                        data: 'DueDate',
                        name: 'DueDate'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'project',
                        name: 'project'
                    },
                    {
                        data: 'subTotal',
                        name: 'subTotal'
                    },
                    {
                        data: 'totalVat',
                        name: 'totalVat'
                    },
                    {
                        data: 'discount',
                        name: 'discount'
                    },
                    {
                        data: 'grandTotal',
                        name: 'grandTotal'
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
    </script>--}}

    <script>
        $(document).ready(function () {
            $('#quotation_table').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    "url" : "{{ url('all_quotation') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}"},
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false,
                    },
                    {
                        data: 'QuotationNumber',
                        name: 'QuotationNumber'
                    },
                    {
                        data: 'FromDate',
                        name: 'FromDate'
                    },
                    {
                        data: 'DueDate',
                        name: 'DueDate'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'project',
                        name: 'project'
                    },
                    {
                        data: 'subTotal',
                        name: 'subTotal'
                    },
                    {
                        data: 'totalVat',
                        name: 'totalVat'
                    },
                    {
                        data: 'discount',
                        name: 'discount'
                    },
                    {
                        data: 'grandTotal',
                        name: 'grandTotal'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable : false,
                    },
                ],
                order: [[ 0, "desc" ]],
                pageLength : 10,
            });
        });
    </script>
@endsection
