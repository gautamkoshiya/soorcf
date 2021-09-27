@extends('shared.layout-admin')
@section('title', 'Add Bank To Bank Transfer')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-8 align-self-center">
                    <h4 class="text-themecolor">Bank to Bank Transfer</h4>
                </div>
                <div class="col-md-4 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">New Bank to Bank Transfer</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('bank_to_banks.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-body">
                                    <h3 class="card-title">ADD Bank to Bank Transfer</h3>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">From Bank :- <span class="required">*</span></label>
                                                <select class="form-control custom-select select2 from_bank_id" name="from_bank_id" id="from_bank_id" required>
                                                    <option value=""> ---- Select Bank ---- </option>
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}">{{ $bank->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Account Number</label>
                                                <input type="text" id="FromAccountNumber" name="FromAccountNumber" class="form-control FromAccountNumber"  readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">To Bank :- <span class="required">*</span></label>
                                                <select class="form-control custom-select select2 to_bank_id" name="to_bank_id" id="to_bank_id" required>
                                                    <option value=""> ---- Select Bank ---- </option>
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}">{{ $bank->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Account Number</label>
                                                <input type="text" id="ToAccountNumber" name="ToAccountNumber" class="form-control ToAccountNumber"  readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Amount :- <span class="required">*</span></label>
                                                <input type="number" id="Amount" name="Amount" class="form-control" placeholder="Amount" maxlength="8" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="control-label">Reference Number :- <span class="required">*</span></label>
                                            <input type="text" class="form-control" name="Reference" id="Reference" placeholder="Reference Number">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="control-label">Transfer Date :- <span class="required">*</span></label>
                                            <input type="date" class="form-control" name="depositDate" id="depositDate" value="{{ date('Y-m-d') }}" placeholder="">
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                    <a href="{{ route('bank_to_banks.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
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
            $('#from_bank_id').change(function () {
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
                                $("#FromAccountNumber").val('');
                                $("#FromAccountNumber").val(result);
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

    <script>
        $(document).ready(function () {
            $('#to_bank_id').change(function () {
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
                                $("#ToAccountNumber").val('');
                                $("#ToAccountNumber").val(result);
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
