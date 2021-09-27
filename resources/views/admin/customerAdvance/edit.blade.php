@extends('shared.layout-admin')
@section('title', 'Edit Customer advances')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">Customer Advances Modification</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">customer Advances</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-eye"></i> List</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">Customer</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('customer_advances.update', $customerAdvance->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <h3 class="card-title">UPDATE CUSTOMER ADVANCE</h3>
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Customer Selection :- <span class="required">*</span></label>
                                                <select class="form-control custom-select customer_id select2" name="customer_id" id="customer_id">
                                                    <option>--Select Customer--</option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}" {{ ($customer->id == $customerAdvance->customer_id) ? 'selected':'' }}>{{ $customer->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">RV Number :- <span class="required">*</span></label>
                                                <input type="text" id="receiptNumber" name="receiptNumber" value="{{ $customerAdvance->receiptNumber }}" class="form-control" placeholder="Receipt Number">
                                                <span class="text-danger" id="already_exist">Similar to this may exist please verify and proceed</span>
                                                @if ($errors->has('receiptNumber'))
                                                    <span class="text-danger">{{ $errors->first('receiptNumber') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Type {{ $customerAdvance->paymentType }} :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" id="paymentType" name="paymentType">
                                                    <option value="bankTransfer" {{ ($customerAdvance->paymentType == 'bankTransfer') ? 'selected':'' }}>Bank</option>
                                                    <option id="cash" value="cash" {{ ($customerAdvance->paymentType == 'cash') ? 'selected':'' }}>Cash</option>
                                                    <option value="checkTransfer" {{ ($customerAdvance->paymentType == 'checkTransfer') ? 'selected':'' }}>Cheque</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Transfer or Deposit Date :- <span class="required">*</span></label>
                                                <input type="date" id="TransferDate" name="TransferDate" value="{{ $customerAdvance->TransferDate }}" class="form-control" placeholder="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label">Amount</label>
                                                <input type="text" onClick="this.setSelectionRange(0, this.value.length)" onkeyup="toWords($('.amount').val())" id="amount" value="{{ $customerAdvance->Amount }}" name="amount" class="form-control amount" placeholder="Enter Amount">
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="form-group">
                                                <label class="control-label">Sum Of</label>
                                                <input type="text" id="SumOf" name="amountInWords" value="{{ $customerAdvance->sumOf }}" class="form-control SumOf" placeholder="Amount In words">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row bankTransfer">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Bank Name</label>
                                                <select class="form-control custom-select" id="bank_id" name="bank_id">
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}" {{ ($bank->id == $customerAdvance->bank_id) ? 'selected':'' }}>{{ $bank->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Account Number</label>
                                                <input type="text" id="accountNumber" name="accountNumber" value="{{ $customerAdvance->accountNumber }}" class="form-control" placeholder="Enter Account Number">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Cheque or Ref. Number ?</label>
                                                <input type="text" id="ChequeNumber" name="ChequeNumber" class="form-control" placeholder="Enter Cheque Number">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-6" style="display:none;">
                                            <div class="form-group">
                                                <label class="control-label">Register Date</label>
                                                <input type="date" id="registerDate" name="registerDate" value="{{ $customerAdvance->registerDate }}"  class="form-control" placeholder="">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Receiver Name :- <span class="required">*</span></label>
                                                <input type="text" id="receiver" name="receiverName" value="{{ $customerAdvance->receiverName }}" class="form-control" placeholder="Enter Receiver Name">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea name="Description" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note">{{ $customerAdvance->Description }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update Advance</button>
                                        <a href="{{ route('customer_advances.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                                    </div>
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
            $('#receiptNumber').keyup(function () {
                var receiptNumber=0;
                receiptNumber = $('#receiptNumber').val();

                var data={receiptNumber:receiptNumber};
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ URL('CheckCustomerAdvanceReferenceExist') }}",
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
    <script>
        $(document).ready(function () {

            var val = $('#paymentType').val();
            if (val !== 'cash'){
                $('.bankTransfer').show();
            }
            else {
                $('.bankTransfer').hide();
            }
        });

        $(document).on("change", '#paymentType', function () {
            var cashDetails = $('#paymentType').val();

            if (cashDetails === 'bankTransfer'){
                $('.bankTransfer').show();
            }
            else if(cashDetails === 'checkTransfer')
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
