@extends('shared.layout-admin')
@section('title', 'Booking Edit')

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

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Booking ID :- <span class="required">*</span></label>
                                                <input type="text" name="code" id="code" class="form-control" readonly value="{{$booking->code}}">
                                                <input type="hidden" name="id" id="id" value="{{$booking->id}}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label> Customer :- <span class="required">*</span></label>
                                                <select name="customer_id" class="form-control customer_id slct chosen-select" id="customer_id" required>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}" {{ ($customer->id == $booking->customer_id ?? 0) ? 'selected':'' }}>{{ $customer->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label> Date :- <span class="required">*</span></label>
                                                <input type="date" name="BookingDate" value="{{ $booking->BookingDate }}" id="BookingDate" class="form-control BookingDate">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Quantity :- <span class="required">*</span></label>
                                                <input type="number" min="0" name="totalQuantity" id="totalQuantity" value="{{ $booking->totalQuantity }}" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Rate :- <span class="required">*</span></label>
                                                <input type="number" min="0" name="Rate" id="Rate" value="{{ $booking->Rate }}" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label class="control-label">Description :- <span class="required">*</span></label>
                                                <input type="text" name="Description" id="Description" class="form-control" value="{{ $booking->Description }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-actions">
                                                <p>&nbsp;</p>
                                                <button type="button" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Update</button>
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
                /////////////// Add Record //////////////////////
                $('#submit').click(function () {
                    $('#submit').text('please wait...');
                    $('#submit').attr('disabled',true);

                    var supplierNew = $('.customer_id').val();
                    if (supplierNew != null)
                    {
                        let details = {
                            id: $('#id').val(),
                            code: $('#code').val(),
                            BookingDate: $('#BookingDate').val(),
                            totalQuantity: $('#totalQuantity').val(),
                            Rate: $('#Rate').val(),
                            Description: $('#Description').val(),
                            customer_id: $('#customer_id').val(),
                        }

                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        var Data = {Data: details};
                        var id = $('#id').val();
                        $.ajax({
                            url: "{{ URL('CustomerBookingUpdate') }}/"+id,
                            type: "post",
                            data: Data,
                            success: function (result) {
                                var result=JSON.parse(result);
                                if (result.result === false) {
                                    alert(result.message);
                                    window.location.href = "{{ route('customer_advance_bookings.index') }}";
                                } else {
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
                        alert('Select Customer first')
                        $('#submit').text('Save');
                        $('#submit').attr('disabled',false);
                    }

                });
                //////// end of submit Records /////////////////
            });
        });
    </script>
@endsection
