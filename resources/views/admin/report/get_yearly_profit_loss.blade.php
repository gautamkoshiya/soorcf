@extends('shared.layout-admin')
@section('title', 'Yearly Profit & Loss')

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
                            <li class="breadcrumb-item active">Yearly Profit & Loss</li>
                        </ol>
                       </div>
                </div>
            </div>

            <h2>Yearly Profit & Loss</h2>
            <h6 class="required">* Fields are required please don't leave blank</h6>
            <hr>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="start_date">Select Year : *</label>
                        <select name="yearpicker" id="yearpicker" class="form-control"></select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button id="submit" type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i> Generate Report</button></a>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script type="text/javascript">
        let startYear = 2020;
        let endYear = new Date().getFullYear();
        for (i = endYear; i > startYear; i--)
        {
            $('#yearpicker').append($('<option />').val(i).html(i));
        }
    </script>
    <script>
        function get_pdf()
        {
            $('#submit').text('please wait...');
            $('#submit').attr('disabled',true);
            var yearpicker = $('#yearpicker').val();

            $.ajax({
                url: "{{ URL('PrintYearlyProfitAndLoss') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",yearpicker:yearpicker},
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit').text('Generate Report');
                    $('#submit').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit').text('Generate Report');
                    $('#submit').attr('disabled',false);
                }
            });

        }
    </script>
@endsection
