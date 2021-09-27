@extends('shared.layout-admin')
@section('title', 'Booking create')

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
                <div class="col-md-5 align-self-center">
                    <h2 class="text-themecolor">Booking</h2>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Booking</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="row page-titles">
                <div class="col-md-12 align-self-center">
                    <h2 class="required">[BEWARE : CHECK ALL DATA BEFORE SUBMIT AFTER MAKING BOOKING NOT POSSIBLE TO UPDATE ANY DATA ONLY YOU CAN VIEW THE BOOKING DETAILS]</h2>
                </div>
            </div>
            <div class="row page-titles">
                @if(Session::has('message'))
                    {{Session::get('message')}}
                @endif
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
{{--                            <form action="{{ route('customer_advance_bookings.store') }}" method="post" enctype="multipart/form-data" onclick="return validateForm()">--}}
                            <form action="#">
{{--                                @csrf--}}
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Booking ID :- <span class="required">*</span></label>
                                                <input type="text" name="code" id="code" class="form-control" readonly value="{{$init_data}}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label> Date :- <span class="required">*</span></label>
                                                <input type="date" name="BookingDate" value="{{ date('Y-m-d') }}" id="BookingDate" class="form-control BookingDate" placeholder="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label> Customer :- <span class="required">*</span></label>
                                                <select name="customer_id" class="form-control customer_id slct chosen-select" id="customer_id" required>
                                                    <option value=""> select customer </option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}">{{ $customer->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <span class="control-label" id="overfilled_info" style="font-size: x-large;color: red;"></span>
                                                <input type="hidden" id="overfilled_quantity_value" value="0">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Quantity :- <span class="required">*</span></label>
                                                <input type="number" min="0" name="totalQuantity" id="totalQuantity" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Rate :- <span class="required">*</span></label>
                                                <input type="number" min="0" name="Rate" id="Rate" class="form-control" required autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Description :- </label>
                                                <input type="text" name="Description" id="Description" class="form-control" autocomplete="off" maxlength="200">
                                            </div>
                                        </div>
                                        <div class="col-md-4 update_overfilled_div" style="display: none;">
                                            <div class="form-group">
                                                <label> Update Overfilled Qty with this booking ? :- <span class="required">*</span></label>
                                                <select name="update_over_filled" class="form-control update_over_filled" id="update_over_filled" required>
                                                    <option value="1" selected>YES</option>
                                                    <option value="0">NO</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="report_file">Select file :- (out of service as of now)</label>
                                            <input class="form-control" type="file" id="booking_file" name="booking_file" multiple>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-actions">
                                                <p>&nbsp;</p>
                                                <button type="button" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                                <a href="{{ route('customer_advance_bookings.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                                            </div>
                                        </div>
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
            $(document).ready(function () {

                $('.customer_id').change(function () {
                    var Id = 0;
                    Id = $(this).val();
                    if (Id > 0)
                    {
                        $.ajax({
                            url: "{{ URL('CustomerBookingOverfilledDetails') }}/" + Id,
                            type: "get",
                            dataType: "json",
                            success: function (result) {
                                if (result.result===true)
                                {
                                    string='Total overfilled quantity : '+result.data;
                                    $("#overfilled_info").html(string);
                                    $( ".update_overfilled_div" ).show();
                                    $('#overfilled_quantity_value').val(result.data);
                                }
                                else
                                {
                                    $("#overfilled_info").html('');
                                    $( ".update_overfilled_div" ).hide();
                                    $('#overfilled_quantity_value').val(0);
                                }
                            },
                            error: function (errormessage) {
                                alert(errormessage);
                            }
                        });
                    }
                });
            });
            ////////////// end of customer select ////////////////
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
            if (DoTrim(document.getElementById('customer_id').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("customer_id").focus();
                }
                fields = '1';
                $("#customer_id").addClass("error");
                $("#customer_id_chosen").css("border", "1px solid red");
            }

            if (DoTrim(document.getElementById('totalQuantity').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("totalQuantity").focus();
                }
                fields = '1';
                $("#totalQuantity").addClass("error");
            }

            if (DoTrim(document.getElementById('Rate').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("Rate").focus();
                }
                fields = '1';
                $("#Rate").addClass("error");
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
            $('#submit').click(function () {
                if(validateForm())
                {
                    $('#submit').text('please wait...');
                    $('#submit').attr('disabled',true);

                    let details = {
                        code: $('#code').val(),
                        BookingDate: $('#BookingDate').val(),
                        totalQuantity: $('#totalQuantity').val(),
                        Rate: $('#Rate').val(),
                        Description: $('#Description').val(),
                        customer_id: $('#customer_id').val(),
                        update_over_filled: $('#update_over_filled').val(),
                        overfilled_quantity_value: $('#overfilled_quantity_value').val(),
                    }

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        url: "{{ route('customer_advance_bookings.store') }}",
                        type: "post",
                        data: details,
                        success: function (result) {
                            var result=JSON.parse(result);
                            if (result.result === false)
                            {
                                alert(result.message);
                                window.location.href = "{{ route('customer_advance_bookings.index') }}";
                            }
                            else
                            {
                                alert(result.message);
                                window.location.href = "{{ route('customer_advance_bookings.index') }}";
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                }
                else
                {
                    alert('Please Enter All Required Data....');
                }
            });
        });
    </script>
@endsection
