@extends('shared.layout-admin')
@section('title', 'Sales Report')

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
                            <li class="breadcrumb-item active">Sales Report</li>
                        </ol>
                       </div>
                </div>
            </div>

            <h2 class="card-title">SALES REPORT</h2>
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
                        <label class="control-label">VAT FILTER :- <span class="required">*</span></label>
                        <select name="filter" class="form-control" id="filter" required>
                            <option value="all" selected>ALL</option>
                            <option value="with">With VAT</option>
                            <option value="without">Without VAT</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Payment Filter :- <span class="required">*</span></label>
                        <select name="payment_filter" class="form-control" id="payment_filter">
                            <option value="all" selected>ALL</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button id="submit" type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i>Get Sales Report</button></a>
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
            var filter = $("#filter option:selected").val();
            var payment_filter = $("#payment_filter option:selected").val();
            $.ajax({
                url: "{{ URL('PrintSalesReport') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate,filter:filter,payment_filter:payment_filter},
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit').text('Get Sales Report');
                    $('#submit').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit').text('Get Sales Report');
                    $('#submit').attr('disabled',false);
                }
            });
        }
    </script>
@endsection
