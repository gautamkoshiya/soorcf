@extends('shared.layout-admin')
@section('title', 'Customer Receivable Summary')

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
                            <li class="breadcrumb-item active">Receivable Summary</li>
                        </ol>
                       </div>
                </div>
            </div>

            <div class="row">
                <h2 class="card-title">Receivable Summary</h2>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                            <a href="javascript:void(0)" onclick="return get_pdf()"><button id="submit" type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Get Customer Statement</button></a>
                    </div>
                </div>
            </div>
            <hr/>


            {{--  date to date statement --}}
            <h2>Receivable Summary For Date </h2>
            <h6 class="required">* Fields are required please don't leave blank</h6>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Date :- <span class="required">*</span></label>
                        <input type="date" value="{{ date('Y-m-d') }}" id="toDate" name="toDate" class="form-control" placeholder="dd/mm/yyyy" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_date_to_date_pdf()"><button id="submit_date" type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i> Get Customer Statement</button></a>
                    </div>
                </div>
            </div>
            {{--  date to date statement --}}

        </div>
    </div>

    <script>
        function get_pdf()
        {
            $('#submit').text('please wait...');
            $('#submit').attr('disabled',true);
            $.ajax({
                url: "{{ URL('PrintCustomerStatement') }}",
                type: "get",
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit').text('Get Customer Statement');
                    $('#submit').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit').text('Get Customer Statement');
                    $('#submit').attr('disabled',false);
                }
            });
        }
    </script>

    <script>
        function get_date_to_date_pdf()
        {
            $('#submit_date').text('please wait...');
            $('#submit_date').attr('disabled',true);
            var toDate = $('#toDate').val();

            $.ajax({
                url: "{{ URL('PrintCustomerStatementForDate') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",toDate:toDate,},
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit_date').text('Get Customer Statement');
                    $('#submit_date').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit_date').text('Get Customer Statement');
                    $('#submit_date').attr('disabled',false);
                }
            });

        }
    </script>
@endsection
