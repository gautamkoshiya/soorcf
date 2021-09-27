@extends('shared.layout-admin')
@section('title', 'Payment Ledger')

@section('content')

    <style>
        .chosen-container-single .chosen-single {
            height: 38px;
            border-radius: 3px;
            border: 1px solid #CCCCCC;
        }
        .chosen-container-single .chosen-single span {
            padding-top: 5px;
        }
        .chosen-container-single .chosen-single div b {
            margin-top: 5px;
        }
        .chosen-container-active .chosen-single,
        .chosen-container-active.chosen-with-drop .chosen-single {
            border-color: #ccc;
            border-color: rgba(82, 168, 236, .8);
            outline: 0;
            outline: thin dotted \9;
            -moz-box-shadow: 0 0 8px rgba(82, 168, 236, .6);
            box-shadow: 0 0 8px rgba(82, 168, 236, .6)
        }
    </style>

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">Payment Ledger</li>
                        </ol>
                       </div>
                </div>
            </div>

            <h3 class="card-title">Payment Ledger</h3>
            <h6 class="required">* Fields are required please don't leave blank</h6>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">From date :- <span class="required">*</span></label>
                        <input type="date" value="{{ date('Y-m-d') }}" id="fromDate" name="fromDate" class="form-control" placeholder="dd/mm/yyyy" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">To date :- <span class="required">*</span></label>
                        <input type="date" value="{{ date('Y-m-d') }}" id="toDate" name="toDate" class="form-control" placeholder="dd/mm/yyyy" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Customer/Supplier :- <span class="required">*</span></label>
                        <select class="form-control custom-select ledger_party" name="ledger_party" id="ledger_party">
                            <option value="" selected>Select</option>
                            <option value="customers">Customers</option>
                            <option value="suppliers">Suppliers</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Parties :- <span class="required">*</span></label>
                        <select class="form-control custom-select parties chosen-select" name="parties" id="parties">
                            <option value="all" selected><-- All Parties --></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">PAYMENT TYPE FILTER :- <span class="required">*</span></label>
                        <select name="filter" class="form-control" id="filter" required>
                            <option value="all" selected>ALL</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button id="submit" type="button" class="btn btn-info "><i class="fa fa-plus-circle"></i> Print Ledger Report</button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function get_pdf()
        {
            $('#submit').text('please wait...');
            $('#submit').attr('disabled',true);
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            var ledger_party = $('#ledger_party').val();
            var parties = $('#parties').val();
            var party_name=$( "#parties option:selected" ).text();
            var filter = $("#filter option:selected").val();
            $.ajax({
                url: "{{ URL('PrintPaymentLedger') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate,ledger_party:ledger_party,parties:parties,party_name:party_name,filter:filter},
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit').text('Print Ledger Report');
                    $('#submit').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit').text('Print Ledger Report');
                    $('#submit').attr('disabled',false);
                }
            });
        }

        $(document).ready(function () {
            $('.ledger_party').change(function () {
                party = $(this).val();
                if (party!=='')
                {
                    url_string='';
                    if(party==='customers')
                    {
                        url_string='{{ URL('getLedgerCustomers') }}';
                    }
                    else if(party==='suppliers')
                    {
                        url_string='{{ URL('getLedgerSuppliers') }}';
                    }
                    $.ajax({
                        url: url_string,
                        type: "get",
                        dataType: "json",
                        success: function (result)
                        {
                            if (result !== "Failed")
                            {
                                $("#parties").html('');
                                var partiesDetails = '';
                                if (result.parties.length > 0)
                                {
                                    partiesDetails += '<option value="all" selected>All</option>';
                                    for (var i = 0; i < result.parties.length; i++)
                                    {
                                        partiesDetails += '<option value="' + result.parties[i].id + '">' + result.parties[i].Name + '</option>';
                                    }
                                }
                                else
                                {
                                    partiesDetails += '<option value="0">No Data</option>';
                                }
                                $("#parties").append(partiesDetails);
                                $("#parties").trigger("chosen:updated");
                            }
                            else
                            {
                                alert(result);
                            }
                        },
                        error: function (errormessage)
                        {
                            alert(errormessage);
                        }
                    });
                }
            });
        });
    </script>
@endsection
