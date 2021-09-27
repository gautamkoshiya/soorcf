@extends('shared.layout-admin')
@section('title', 'Inventory Report')

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
                            <li class="breadcrumb-item active">Inventory Report</li>
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

            <h2 class="card-title">Inventory Report</h2>
            <h6 class="required">* Fields are required please don't leave blank</h6>

            <form id="report_form" action="#">
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
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button id="submit" type="button" class="btn btn-info"><i class="fa fa-file-pdf-o"></i> Print Report</button></a>
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
                url: "{{ URL('PrintInventoryReport') }}",
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
