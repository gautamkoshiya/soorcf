@extends('shared.layout-admin')
@section('title', 'ADD Employee Transaction')

@section('content')

    <style>
        .slct:focus{
            background: #aed9f6;
        }
    </style>
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
                <div class="col-md-8 align-self-center">
                    <h2 class="text-themecolor">Employee Transaction</h2>
                    <h3 class="required"> * Enter Data Carefully after saving Update is not allowed.</h3>
                </div>
                <div class="col-md-4 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">New Employee Transaction</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">
                                    <h3 class="card-title">ADD Employee Transaction</h3>
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <h6 class="required">* Credit -> Transaction will decrease(-) employee account</h6>
                                    <h6 class="required">* Debit -> Transaction will increase(+) employee account</h6>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Transaction Type :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" id="transaction_type" name="transaction_type" required>
                                                    <option value="">--Select Type--</option>
                                                    <option value="debit">Debit</option>
                                                    <option value="credit">Credit</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="mt-2">Select Employee :- <span class="required">*</span></label>
                                                <select class="form-control custom-select employee_id chosen-select" name="employee_id" id="employee_id">
                                                    <option value=""> ---- Select Employee ---- </option>
                                                    @foreach($employees as $single)
                                                        <option value="{{ $single->id }}">{{ $single->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Type :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" id="paymentType" name="paymentType" required>
                                                    <option value="">--Select your Payment Type--</option>
                                                    <option value="bank">Bank</option>
                                                    <option id="cash" value="cash" selected>Cash</option>
                                                    <option value="cheque">Cheque</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2 bankTransfer">
                                            <div class="form-group">
                                                <label>Bank Name :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" id="bank_id" name="bank_id">
                                                    <option selected value="">--Select Bank Name--</option>
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}">{{ $bank->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2 bankTransfer">
                                            <div class="form-group">
                                                <label class="control-label">Account Number :- <span class="required">*</span></label>
                                                <input type="text" id="accountNumber" name="accountNumber" class="form-control accountNumber" placeholder="Enter Account Number">
                                            </div>
                                        </div>

                                        <div class="col-md-2 bankTransfer">
                                            <div class="form-group">
                                                <label class="control-label">Transfer or Deposit Date :- <span class="required">*</span></label>
                                                <input type="date" id="TransferDate" name="TransferDate" value="{{ date('Y-m-d') }}" class="form-control" placeholder="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4" style="display: none;">
                                            <div class="form-group">
                                                <label class="control-label">Receipt Number</label>
                                                <input type="text" id="receiptNumber" name="receiptNumber" class="form-control" placeholder="Receipt Number">
                                                @if ($errors->has('receiptNumber'))
                                                    <span class="text-danger">{{ $errors->first('receiptNumber') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Amount :- <span class="required">*</span></label>
                                                <input type="text" class="form-control amount" onClick="this.setSelectionRange(0, this.value.length)" name="" id="totalAmount" placeholder="Paying Amount" value="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="control-label">Cheque or Ref. Number ? <span class="required">*</span></label>
                                            <input type="text" class="form-control" name="" id="referenceNumber" placeholder="Cheque or Ref. Number" autocomplete="off">
                                            <span class="text-danger" id="already_exist">Similar to this may exist please verify and proceed</span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="control-label">Payment Release Date</label>
                                            <input type="date" class="form-control" name="createdDate" id="createdDate" value="{{ date('Y-m-d') }}" placeholder="">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="control-label">Description :- <span class="required">*</span></label>
                                            <textarea style="width: 100%" id="Description" name="Description" placeholder="Description"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                    <button type="button" class="btn btn-inverse">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('#already_exist').hide();
            $('#referenceNumber').keyup(function () {
                var referenceNumber=0;
                referenceNumber = $('#referenceNumber').val();

                var data={referenceNumber:referenceNumber};
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ URL('CheckAccountTransactionReferenceExist') }}",
                    type: "post",
                    data: data,
                    dataType: "json",
                    success: function (result) {
                        if (result === true)
                        {
                            $('#already_exist').show();
                        }
                        else
                        {
                            $('#already_exist').hide();
                        }
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            });

        });
    </script>

    <script type="text/javascript">
        $(document).ready(function () {
            $('.bankTransfer').hide();
        });

        $(document).on("change", '#paymentType', function () {
            var cashDetails = $('#paymentType').val();

            if (cashDetails === 'bank'){
                $('.bankTransfer').show();
            }
            else if(cashDetails === 'cheque')
            {
                $('.bankTransfer').show();
            }
            else
            {
                $('.bankTransfer').hide();
            }
        });
    </script>
    <script>
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

            if (DoTrim(document.getElementById('paymentType').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("paymentType").focus();
                }
                fields = '1';
                $("#paymentType").addClass("error");
            }

            if(DoTrim(document.getElementById('referenceNumber').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("referenceNumber").focus();
                }
                fields = '1';
                $("#referenceNumber").addClass("error");
            }

            if(DoTrim(document.getElementById('totalAmount').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("totalAmount").focus();
                }
                fields = '1';
                $("#totalAmount").addClass("error");
            }

            var payment_type=$("#paymentType option:selected").val();
            if(payment_type=='bank' || payment_type=='cheque')
            {
                if(DoTrim(document.getElementById('bank_id').value).length == 0)
                {
                    if(fields != 1)
                    {
                        document.getElementById("bank_id").focus();
                    }
                    fields = '1';
                    $("#bank_id").addClass("error");
                }
            }

            if(DoTrim(document.getElementById('Description').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("Description").focus();
                }
                fields = '1';
                $("#Description").addClass("error");
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
            $('#submit').click(function (event) {
                if(validateForm()) {
                    $('#submit').text('please wait...');
                    $('#submit').attr('disabled', true);

                    var bank_id = $('#bank_id').val();
                    if (bank_id === "") {
                        bank_id = 0
                    }
                    let details = {
                        'transaction_type': $('#transaction_type').val(),
                        'employee_id': $('#employee_id').val(),
                        'payment_type': $('#paymentType').val(),
                        'bank_id': bank_id,
                        'accountNumber': $('#accountNumber').val(),
                        'TransferDate': $('#paymentReceiveDate').val(),
                        'receiptNumber': $('#receiptNumber').val(),
                        'referenceNumber': $('#referenceNumber').val(),
                        'createdDate': $('#createdDate').val(),
                        'totalAmount': $('#totalAmount').val(),
                        'Description': $('#Description').val(),
                    };

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var Datas = details;
                    $.ajax({
                        url: "{{ route('employee_transactions.store') }}",
                        type: "post",
                        data: Datas,
                        success: function (result) {
                            if (result === true) {
                                alert("Record Inserted Successfully");
                                window.location.href = "{{ route('employee_transactions.index') }}";
                            } else {
                                window.location.href = "{{ route('employee_transactions.index') }}";
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                }
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#bank_id').change(function () {
                var Id = 0;
                Id = $(this).val();
                if (Id > 0)
                {
                    $.ajax({
                        url: "{{ URL('getBankAccountDetail') }}/" + Id,
                        type: "get",
                        dataType: "json",
                        success: function (result) {
                            if (result !== "Failed") {
                                $("#accountNumber").val('');
                                $("#accountNumber").val(result);
                            } else {
                                alert(result);
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                }
            });
        });
    </script>
    <script src="{{ asset('admin_assets/assets/dist/custom/custom.js') }}" type="text/javascript" charset="utf-8" async defer></script>
@endsection
