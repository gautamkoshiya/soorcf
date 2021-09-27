@extends('shared.layout-admin')
@section('title', 'Cash Report')

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
                            <li class="breadcrumb-item active">Cash Report</li>
                        </ol>
                       </div>
                </div>
            </div>

            @if (Session::has('error'))
                <div class="alert alert-danger">
                    <ul>
                        <li>{!! Session::get('error') !!}</li>
                        {{Session::forget('error')}}
                    </ul>
                </div>
            @endif

            <h2 class="card-title">Cash Report</h2>
            <h6 class="required">* Fields are required please don't leave blank</h6>

            <form id="report_form" method="post" action="{{ route('ViewCashReport') }}" enctype="multipart/form-data">
                @csrf
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
                <div class="col-md-2">
                    <div class="form-group">
                        <button class="btn btn-dark" type="submit"><i class="fa fa-plus-circle"></i> View Cash Report</button>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button id="submit" type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i> Download as PDF Cash Report</button></a>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf1()"><button id="submit1" type="button" class="btn btn-danger"><i class="fa fa-plus-circle"></i> Download Only Cash Expense</button></a>
                    </div>
                </div>

                <div class="col-md-1">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_cash_entries_log()"><button id="cash_log" type="button" class="btn btn-warning"><i class="fa fa-plus-circle"></i> Download Cash Entries Log</button></a>
                    </div>
                </div>

            </div>
            </form>
        </div>
    </div>

    <script>
        function get_pdf()
        {
            $('#submit').text('please wait...');
            $('#submit').attr('disabled',true);
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            $.ajax({
                url: "{{ URL('PrintCashReport') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate},
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit').text('Download as PDF Cash Report');
                    $('#submit').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit').text('Download as PDF Cash Report');
                    $('#submit').attr('disabled',false);
                }
            });
        }
    </script>

    <script>
        function get_pdf1()
        {
            $('#submit1').text('please wait...');
            $('#submit1').attr('disabled',true);
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            $.ajax({
                url: "{{ URL('PrintExpenseCashReport') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate},
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit1').text('Download Only Cash Expense');
                    $('#submit1').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit1').text('Download Only Cash Expense');
                    $('#submit1').attr('disabled',false);
                }
            });
        }
    </script>

    <script>
        function get_cash_entries_log()
        {
            $('#cash_log').text('please wait...');
            $('#cash_log').attr('disabled',true);
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            $.ajax({
                url: "{{ URL('PrintCashLogReport') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate},
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#cash_log').text('Download Cash Entries Log');
                    $('#cash_log').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#cash_log').text('Download Cash Entries Log');
                    $('#cash_log').attr('disabled',false);
                }
            });
        }
    </script>
@endsection
