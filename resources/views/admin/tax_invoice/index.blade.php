@extends('shared.layout-admin')
@section('title', 'Tax Invoice')

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
                            <li class="breadcrumb-item active">Tax Invoice</li>
                        </ol>
                        <a href="{{ route('tax_invoices.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New Tax Invoice</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Tax Invoice</h2>
                            <h5 class="required" style="float: right;">[search by Tax Invoice Number ]</h5>
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
                                        <th>Remaining</th>
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

    <!-- Modal -->
    <div class="modal fade" id="TaxInvoicePayments" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Payment Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal_body">
                    <form action="#">
                        @csrf
                        <input name="tax_invoice_id" type="hidden" id="tax_invoice_id" value="">
                        <h4>Total Amount : <span id="total_amount"></span></h4>
                        <h4>Remaining Amount : <span id="remaining_amount"></span></h4>
                        <input type="hidden" id="rem_amount" value="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="message-texta" class="control-label">Payment Date: <span class="required">*</span></label>
                                    <input type="date" id="PaymentDate" name="PaymentDate" value="{{ date('Y-m-d') }}" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="message-texta" class="control-label">Payment Amount: <span class="required">*</span></label>
                                    <input type="number" min="0" required id="PaymentAmount" name="PaymentAmount" class="form-control">
                                    <input name="_payment_id" type="hidden" id="_payment_id">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Payment Mode :- <span class="required">*</span></label>
                                    <select class="custom-select payment_type" name="payment_type" id="payment_type">
                                        <option value="cash">Cash</option>
                                        <option value="bank">Bank</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="message-texta" class="control-label">Note: <span class="required">*</span></label>
                                    <textarea name="Description" class="form-control" id="Description" placeholder="Payment Note"></textarea>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input class="btn btn-info" id="save_payment_submit"  type="button" value="Save">
                    </form>
                </div>
                <hr>
                <div id="previous_payments"></div>
            </div>
        </div>
    </div>
    <!-- Modal -->

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
                    url: "{{ URL('PrintTaxInvoice') }}/"+id,
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
        function payment(e)
        {
            $('#TaxInvoicePayments').modal('show');
            var id=e;
            id=id.split('_');
            id=id[1];
            if (id > 0)
            {
                $('#tax_invoice_id').val(id)
                $.ajax({
                    url: "{{ URL('GetTaxInvoiceDetails') }}/"+id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        var result=JSON.parse(JSON.stringify(result));
                        $('#remaining_amount').html(result.remaining);
                        $('#total_amount').html(result.total);
                        $('#rem_amount').val(result.remaining)
                        $('#previous_payments').html(result.table)
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            }
        }

        function DoTrim(strComp) {
            ltrim = /^\s+/
            rtrim = /\s+$/
            strComp = strComp.replace(ltrim, '');
            strComp = strComp.replace(rtrim, '');
            return strComp;
        }

        function validateForm()
        {
            /*validation*/
            var fields;
            fields = "";

            if (DoTrim(document.getElementById('PaymentAmount').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("PaymentAmount").focus();
                }
                fields = '1';
                $("#PaymentAmount").addClass("error");
            }

            if (DoTrim(document.getElementById('Description').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("Description").focus();
                }
                fields = '1';
                $("#Description").addClass("error");
            }

            if (DoTrim(document.getElementById('payment_type').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("payment_type").focus();
                }
                fields = '1';
                $("#payment_type").addClass("error");
            }

            if (fields != "")
            {
                fields = "Please fill in the following details:" + fields;
                return false;
            }
            else
            {
                return true;
            }
            /*validation*/
        }

        $(document).ready(function () {
            $('#save_payment_submit').click(function (event) {
                if (validateForm()) {
                    $('#save_payment_submit').text('please wait...');
                    $('#save_payment_submit').attr('disabled', true);
                    var tax_invoice_id = $('#tax_invoice_id').val();
                    var PaymentDate = $('#PaymentDate').val();
                    var PaymentAmount = $('#PaymentAmount').val();
                    var payment_type = $('#payment_type').val();
                    var Description = $('#Description').val();
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ URL('SaveTaxInvoiceDetails') }}",
                        type: "post",
                        data: {tax_invoice_id:tax_invoice_id,PaymentDate:PaymentDate,PaymentAmount:PaymentAmount,payment_type:payment_type,Description:Description},
                        success: function (result) {
                            if (result === true) {
                                //window.location.href = "{{ route('tax_invoices.index') }}";
                            } else {
                                alert('Something went wrong');
                                //window.location.href = "{{ route('tax_invoices.index') }}";
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                }
            })
        });
    </script>

    {{--<script>
        $(document).ready(function () {
            $('#invoice_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('tax_invoices.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'InvoiceNumber',
                        name: 'InvoiceNumber'
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
                        data: 'RemainingBalance',
                        name: 'RemainingBalance'
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
                    "url" : "{{ url('all_tax_invoice') }}",
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
                        name: 'InvoiceNumber'
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
                        data: 'RemainingBalance',
                        name: 'RemainingBalance'
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
