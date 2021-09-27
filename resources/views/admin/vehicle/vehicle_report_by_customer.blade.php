@extends('shared.layout-admin')
@section('title', 'Sales Report by Customer')

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
                            <li class="breadcrumb-item active">Balance Sheet</li>
                        </ol>
                       </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Customer</label>
                        <select class="form-control custom-select customer_id chosen-select" name="customer_id" id="customer_id">
                            <option value="all" selected>All Customers</option>
                            @foreach($customers as $customer)
                                @if(!empty($customer->Name))
                                    <option value="{{ $customer->id }}">{{ $customer->Name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button type="button" id="submit" class="btn btn-info "><i class="fa fa-plus-circle"></i> Get Vehicle Report</button></a>
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
            var customer_id = $('#customer_id').val();
            $.ajax({
                url: "{{ URL('PrintVehicleList') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",customer_id:customer_id},
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit').text('Get Vehicle Report');
                    $('#submit').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit').text('Get Vehicle Report');
                    $('#submit').attr('disabled',false);
                }
            });
        }
    </script>
@endsection
