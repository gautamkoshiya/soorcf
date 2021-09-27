@extends('shared.layout-admin')
@section('title', 'Expense Analysis By Category')

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
                            <li class="breadcrumb-item active">Expense Analysis By Category</li>
                        </ol>
                       </div>
                </div>
            </div>

            <h2 class="card-title">Expense Analysis By Category</h2>
            <h6 class="required">* Fields are required please don't leave blank</h6>
            <form action="{{ URL('ViewExpenseAnalysisByCategory') }}" method="post" enctype="multipart/form-data">
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

            <div class="form-actions">
                <div class="row">
                    <div class="col-md-1">
                        <div class="form-group">
                            <button type="submit" class="btn btn-success" tabindex="3">View</button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <a href="javascript:void(0)" onclick="return get_date_to_date_pdf()"><button id="submit_date" type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i> Print Report</button></a>
                        </div>
                    </div>
                </div>

            </div>
            </form>
        </div>
    </div>
    <script>
        function get_date_to_date_pdf()
        {
            $('#submit').text('please wait...');
            $('#submit').attr('disabled',true);

            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();

            $.ajax({
                url: "{{ URL('PrintExpenseAnalysisByDate') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate},
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit').text('Print Report');
                    $('#submit').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit').text('Print Report');
                    $('#submit').attr('disabled',false);
                }
            });
        }
    </script>
@endsection
