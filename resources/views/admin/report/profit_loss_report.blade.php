@extends('shared.layout-admin')
@section('title', 'Profit & Loss')

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
                            <li class="breadcrumb-item active">Profit & Loss</li>
                        </ol>
                       </div>
                </div>
            </div>

            <h2>Profit & Loss</h2>
            <h6 class="required">* Fields are required please don't leave blank</h6>
            <hr>
            @if(Session::get('company_id') != 4 && Session::get('company_id') != 5 && Session::get('company_id') != 8)
            <span>Average purchase price : <input type="number" class="form-control required" id="average_price" value="0" readonly></span>
            @endif
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Select Month :- <span class="required">*</span></label>
                        <input type="month" id="month" name="month" class="form-control month" required>
                    </div>
                </div>

                @if(Session::get('company_id') != 4 && Session::get('company_id') != 5 && Session::get('company_id') != 8)
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Enter Rate :- <span class="required">*</span></label>
                        <input type="number" id="currentRate" name="currentRate" class="form-control" required>
                    </div>
                </div>
                @endif
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button id="submit" type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i> Get Profit & Loss Statement</button></a>
                    </div>
                </div>
            </div>

            <hr>
            {{--  date to date profit and loss --}}
                <h2>Profit & Loss Date to Date</h2>
                <h6 class="required">* Fields are required please don't leave blank</h6>
                <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">From date :- <span class="required">*</span></label>
                                <input type="date" value="{{ date('Y-m-d') }}" id="fromDate" name="fromDate" class="form-control" placeholder="dd/mm/yyyy" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">To date :- <span class="required">*</span></label>
                                <input type="date" value="{{ date('Y-m-d') }}" id="toDate" name="toDate" class="form-control" placeholder="dd/mm/yyyy" required>
                            </div>
                        </div>
                    </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <a href="javascript:void(0)" onclick="return get_date_to_date_pdf()"><button id="submit_date" type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i> Get Profit & Loss Statement</button></a>
                        </div>
                    </div>
                </div>
            {{--  date to date profit and loss --}}

        </div>
    </div>
    @if(Session::get('company_id') != 4 && Session::get('company_id') != 5 && Session::get('company_id') != 8)
    <script>
        $(document).ready(function () {
            $('.month').change(function () {
                Id = $(this).val();
                $.ajax({
                    url: "{{ URL('getAveragePurchasePrice') }}/" + Id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        $('#average_price').val(result);
                        $('#currentRate').val(result);
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            });
        });
    </script>
    @endif
    <script>
        function get_pdf()
        {
            $('#submit').text('please wait...');
            $('#submit').attr('disabled',true);
            var month = $('#month').val();
            var currentRate = $('#currentRate').val();
            if(currentRate==0 || currentRate==0.00)
            {
                alert('Rate 0 not allowed here...');
                location.reload();
            }
            else
            {
                $.ajax({
                    url: "{{ URL('PrintProfit_loss') }}",
                    type: "POST",
                    dataType : "json",
                    data : {"_token": "{{ csrf_token() }}",month:month,currentRate:currentRate},
                    success: function (result) {
                        window.open(result.url,'_blank');
                        $('#submit').text('Get Profit & Loss Statement');
                        $('#submit').attr('disabled',false);
                    },
                    error: function (errormessage) {
                        alert('No Data Found');
                        $('#submit').text('Get Profit & Loss Statement');
                        $('#submit').attr('disabled',false);
                    }
                });
            }
        }
    </script>

    <script>
        function get_date_to_date_pdf()
        {
            $('#submit_date').text('please wait...');
            $('#submit_date').attr('disabled',true);
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();

            $.ajax({
                url: "{{ URL('PrintProfit_loss_by_date') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate,},
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit_date').text('Get Profit & Loss Statement');
                    $('#submit_date').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit_date').text('Get Profit & Loss Statement');
                    $('#submit_date').attr('disabled',false);
                }
            });

        }
    </script>
@endsection
