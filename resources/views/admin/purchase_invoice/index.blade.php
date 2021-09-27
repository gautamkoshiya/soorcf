@extends('shared.layout-admin')
@section('title', 'Purchase Invoice')

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
                            <li class="breadcrumb-item active">Purchase Invoice</li>
                        </ol>
                        <a href="{{ route('purchase_invoices.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New Purchase Invoice</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Purchase Invoice</h2>
                            <h5 class="required" style="float: right;">[search by Purchase Invoice Number ][search by Reference Number ]</h5>
                            <div class="table-responsive m-t-40">
                                <table id="invoice_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th style="width: 100px">SR#</th>
                                        <th style="width: 100px">InvoiceNumber</th>
                                        <th>Reference#</th>
                                        <th style="width: 150px">FromDate</th>
                                        <th style="width: 150px">DueDate</th>
                                        <th style="width: 150px">Supplier</th>
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
        function roundToTwo(num) {
            return +(Math.round(num + "e+2")  + "e-2");
        }
        $(document).on("keyup",'#PaymentAmount',function () {
            var payable = $('#rem_amount').val();
            var paying = $('#PaymentAmount').val();
            payable=parseFloat(payable).toFixed(2);
            paying=parseFloat(paying).toFixed(2);
            payable=roundToTwo(payable);
            paying=roundToTwo(paying);
            if(!isNaN(payable) && !isNaN(paying) && (paying > payable))
            {
                $('#PaymentAmount').val((payable));
            }
        });
    </script>
    <script>
        function show_detail(e)
        {
            var id=e;
            id=id.split('_');
            id=id[1];
            if (id > 0)
            {
                $.ajax({
                    url: "{{ URL('PrintPurchaseInvoice') }}/"+id,
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
        $(document).ready(function () {
            $('#invoice_table').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    "url" : "{{ url('all_purchase_invoice') }}",
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
                        data: 'InvoiceNumber',
                        name: 'InvoiceNumber',
                        orderable: false,
                    },
                    {
                        data: 'ReferenceNumber',
                        name: 'ReferenceNumber',
                        orderable: false,
                    },
                    {
                        data: 'FromDate',
                        name: 'FromDate',
                        orderable: false,
                    },
                    {
                        data: 'DueDate',
                        name: 'DueDate',
                        orderable: false,
                    },
                    {
                        data: 'supplier',
                        name: 'supplier',
                        orderable: false,
                    },
                    {
                        data: 'project',
                        name: 'project',
                        orderable: false,
                    },
                    {
                        data: 'subTotal',
                        name: 'subTotal',
                        orderable: false,
                    },
                    {
                        data: 'totalVat',
                        name: 'totalVat',
                        orderable: false,
                    },
                    {
                        data: 'discount',
                        name: 'discount',
                        orderable: false,
                    },
                    {
                        data: 'grandTotal',
                        name: 'grandTotal',
                        orderable: false,
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
