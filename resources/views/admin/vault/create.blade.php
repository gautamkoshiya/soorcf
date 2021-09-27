@extends('shared.layout-admin')
@section('title', 'ADD Vault Transaction')

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
                    <h2 class="text-themecolor">Vault Transaction</h2>
                    <h3 class="required"> * Enter Data Carefully after saving Update is not allowed.</h3>
                </div>
                <div class="col-md-4 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">New Vault Transaction</li>
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
                                    <h3 class="card-title">ADD Vault Transaction</h3>
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <h6 class="required">* Credit -> Transaction will decrease(-) cash for selected company</h6>
                                    <h6 class="required">* Debit -> Transaction will increase(+) cash for selected company</h6>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Transaction Type :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" id="transaction_type" name="transaction_type" required>
                                                    <option value="">--Select Type--</option>
                                                    <option value="debit">Debit</option>
                                                    <option value="credit">Credit</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Select Company :- <span class="required">*</span></label>
                                                <select class="form-control custom-select customer_id chosen-select" name="company_id" id="company_id">
                                                    <option value=""> ---- Select Company ---- </option>
                                                    @foreach($companies as $company)
                                                        <option value="{{ $company->id }}">{{ $company->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Amount :- <span class="required">*</span></label>
                                                <input type="text" class="form-control amount" onClick="this.setSelectionRange(0, this.value.length)" name="" id="totalAmount" placeholder="Paying Amount" value="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="control-label">Transaction Date</label>
                                            <input type="date" class="form-control" name="transferDate" id="transferDate" value="{{ date('Y-m-d') }}" placeholder="">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="control-label">Description</label>
                                            <textarea style="width: 100%" id="Description" name="Description" placeholder="Description"></textarea>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                    <a href="{{ route('suppliers.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

            if (DoTrim(document.getElementById('transaction_type').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("transaction_type").focus();
                }
                fields = '1';
                $("#transaction_type").addClass("error");
            }

            if(DoTrim(document.getElementById('company_id').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("company_id").focus();
                }
                fields = '1';
                $("#company_id").addClass("error");
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

                    let details = {
                        'transaction_type': $('#transaction_type').val(),
                        'company_id': $('#company_id').val(),
                        'transferDate': $('#transferDate').val(),
                        'totalAmount': $('#totalAmount').val(),
                        'Description': $('#Description').val(),
                    };

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var Data = {Data: details};
                    $.ajax({
                        url: "{{ route('vaults.store') }}",
                        type: "post",
                        data: Data,
                        success: function (result) {
                            if (result === true) {
                                window.location.href = "{{ route('vaults.index') }}";
                            } else {
                                window.location.href = "{{ route('vaults.index') }}";
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

@endsection
