@extends('shared.layout-admin')
@section('title', 'Garage Value')

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
                            <li class="breadcrumb-item active">Garage Value</li>
                        </ol>
                       </div>
                </div>
            </div>
            @if(Session::get('company_id') != 4 && Session::get('company_id') != 5 && Session::get('company_id') != 8)
            <h2>Garage Value</h2>
            <h6 class="required">* Fields are required please don't leave blank</h6>
            <hr>
            <span>Average purchase price : <input type="number" class="form-control required" id="average_price" value="0" readonly></span>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Select Month :- <span class="required">*</span></label>
                        <input type="month" id="month" name="month" class="form-control month" required>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Enter Rate :- <span class="required">*</span></label>
                        <input type="number" id="currentRate" name="currentRate" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button id="submit" type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i> Get Garage Value Statement</button></a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
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
                    url: "{{ URL('PrintGarage_value') }}",
                    type: "POST",
                    dataType : "json",
                    data : {"_token": "{{ csrf_token() }}",month:month,currentRate:currentRate},
                    success: function (result) {
                        window.open(result.url,'_blank');
                        $('#submit').text('Get Garage Value Statement');
                        $('#submit').attr('disabled',false);
                    },
                    error: function (errormessage) {
                        alert('No Data Found');
                        $('#submit').text('Get Garage Value Statement');
                        $('#submit').attr('disabled',false);
                    }
                });
            }
        }
    </script>
@endsection
