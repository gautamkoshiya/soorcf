@extends('shared.layout-admin')
@section('title', 'Vehicle create')

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
                    <h4 class="text-themecolor">Vehicle Registration</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Vehicle</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button>
                    </div>
                </div>
            </div>

            @if(session()->has('exist'))
                <div class="alert alert-danger">
                    {{ session()->get('exist') }}
                </div>
            @endif

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">Vehicle</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('vehicles.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-body">
                                    <h3 class="card-title">Registration</h3>
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Customer :- <span class="required">*</span></label>
                                                <select class="form-control custom-select customer_id select2 chosen-select" name="customer_id" id="customer_id" required autofocus tabindex="1">
                                                    <option value="">--Select your Customer--</option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}">{{ $customer->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Registration Number :- <span class="required">*</span></label>
                                                <input type="text"  id="registrationNumber" name="registrationNumber" class="form-control" placeholder="Enter Vehicle Registration Number" required maxlength="15" autocomplete="off" tabindex="2">
                                                <span class="text-danger" id="already_exist">Already Exists</span>
                                                @if ($errors->has('registrationNumber'))
                                                    <span class="text-danger">{{ $errors->first('registrationNumber') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label">Description :-</label>
                                                <textarea name="Description" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success" tabindex="3"> <i class="fa fa-check"></i> Save</button>
                                    <a href="{{ route('vehicles.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#already_exist').hide();
            $('#registrationNumber').keyup(function () {
                var customer_id = 0;
                var registrationNumber=0;
                customer_id = $('#customer_id').val();
                registrationNumber = $('#registrationNumber').val();
                if (customer_id > 0)
                {
                    var data={customer_id:customer_id,registrationNumber:registrationNumber};
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ URL('CheckVehicleExist') }}",
                        type: "post",
                        data: data,
                        dataType: "json",
                        success: function (result) {
                            if (result === true)
                            {
                                $('#already_exist').show();
                            }
                            else
                            {
                                $('#already_exist').hide();
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
@endsection
