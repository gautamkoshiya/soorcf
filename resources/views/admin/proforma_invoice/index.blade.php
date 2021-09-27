@extends('shared.layout-admin')
@section('title', 'Proforma Invoice')

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
                            <li class="breadcrumb-item active">Proforma Invoice</li>
                        </ol>
                        <a href="{{ route('proforma_invoices.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New Proforma Invoice</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">Proforma Invoice</h3>
                            <h5 class="required" style="float: right;">[search by Proforma Invoice Number ]</h5>
                            <div class="table-responsive m-t-40">
                                <table id="invoice_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th style="width: 100px">SR#</th>
                                        <th style="width: 100px">InvoiceNumber</th>
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
                    url: "{{ URL('PrintProformaInvoice') }}/"+id,
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
            $('#invoice_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('proforma_invoices.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'PFINVNumber',
                        name: 'PFINVNumber'
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
            $('#invoice_table').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    "url" : "{{ url('all_proforma') }}",
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
                        data: 'PFINVNumber',
                        name: 'PFINVNumber'
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
