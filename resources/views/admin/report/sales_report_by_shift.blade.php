@extends('shared.layout-admin')
@section('title', 'Sales Report by Shift')

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
                            <li class="breadcrumb-item active">Sales Report By Shift</li>
                        </ol>
                       </div>
                </div>
            </div>

            <h3 class="card-title">SALES REPORT BY SHIFT</h3>
            <h6 class="required">* Fields are required please don't leave blank</h6>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Start Pad :- <span class="required">*</span></label>
                        <input type="text" id="start_pad" name="start_pad" class="form-control start_pad" required>
                        <span class="text-danger" id="start_pad_already_exist">Not Found</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Middle Pad :- <span class="required">*</span></label>
                        <input type="text" id="middle_pad" name="middle_pad" class="form-control middle_pad" required>
                        <span class="text-danger" id="middle_pad_already_exist">Not Found</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">End Pad :- <span class="required">*</span></label>
                        <input type="text" id="end_pad" name="end_pad" class="form-control end_pad" required>
                        <span class="text-danger" id="end_pad_already_exist">Not Found</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button id="submit" type="button" class="btn btn-info "><i class="fa fa-plus-circle"></i> Get Report</button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('#start_pad_already_exist').hide();
            $('#middle_pad_already_exist').hide();
            $('#end_pad_already_exist').hide();
            $('#start_pad').keyup(function () {
                var PadNumber = 0;
                PadNumber = $('#start_pad').val();
                if (PadNumber > 0)
                {
                    var data={PadNumber:PadNumber};
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ URL('CheckPadExist') }}",
                        type: "post",
                        data: data,
                        dataType: "json",
                        success: function (result) {
                            if (result === false)
                            {
                                $('#start_pad_already_exist').show();
                            }
                            else
                            {
                                $('#start_pad_already_exist').hide();
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                }
            });

            $('#middle_pad').keyup(function () {
                var PadNumber = 0;
                PadNumber = $('#middle_pad').val();
                if (PadNumber > 0)
                {
                    var data={PadNumber:PadNumber};
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ URL('CheckPadExist') }}",
                        type: "post",
                        data: data,
                        dataType: "json",
                        success: function (result) {
                            if (result === false)
                            {
                                $('#middle_pad_already_exist').show();
                            }
                            else
                            {
                                $('#middle_pad_already_exist').hide();
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                }
            });

            $('#end_pad').keyup(function () {
                var PadNumber = 0;
                PadNumber = $('#end_pad').val();
                if (PadNumber > 0)
                {
                    var data={PadNumber:PadNumber};
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ URL('CheckPadExist') }}",
                        type: "post",
                        data: data,
                        dataType: "json",
                        success: function (result) {
                            if (result === false)
                            {
                                $('#end_pad_already_exist').show();
                            }
                            else
                            {
                                $('#end_pad_already_exist').hide();
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
        function get_pdf()
        {
            $('#submit').text('please wait...');
            $('#submit').attr('disabled',true);
            var start_pad = $('#start_pad').val();
            var middle_pad = $('#middle_pad').val();
            var end_pad = $('#end_pad').val();
            $.ajax({
                url: "{{ URL('PrintSalesReportByShift') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",start_pad:start_pad,middle_pad:middle_pad,end_pad:end_pad},
                success: function (result) {
                    //var result=JSON.parse(result);
                    var result=JSON.parse(JSON.stringify(result))
                    //alert(result);
                    console.log(result);
                    if (result.result === false) {
                        alert(result.message);
                        window.location.href = "{{ route('SalesReportByShift') }}";
                    } else {
                        window.open(result.url,'_blank');
                        $('#submit').text('Get Report');
                        $('#submit').attr('disabled',false);
                    }

                },
                error: function (errormessage) {
                    alert('Something Went Wrong');
                }
            });
        }
    </script>
@endsection
