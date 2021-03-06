@extends('shared.layout-admin')
@section('title', 'Customer create')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">Customer Registration</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">customer</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">Customer</h4>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('customers.store') }}" enctype="multipart/form-data" id="customer_create">
                                @csrf
                                <div class="form-body">
                                    <h3 class="card-title">Registration</h3>
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Company Name :- <span class="required">*</span></label>
                                                <input type="text" id="Name" name="Name" class="form-control" placeholder="Enter Customer Company Name" required autocomplete="off" autofocus>
                                                <span class="text-danger" id="already_exist">Customer With Same Name May Already Exists</span>
                                                @if ($errors->has('Name'))
                                                    <span class="text-danger">{{ $errors->first('Name') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Owner/Representative Name</label>
                                                <input type="text" id="Representative" name="Representative" class="form-control" placeholder="Enter Owner/Representative Name" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Company Type :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" name="companyType">
                                                    <option readonly disabled="" selected="">--Select your Company Type--</option>
                                                    @foreach ($company_types as $company_type)
                                                       <option value="{{ $company_type->id }}">{{ $company_type->Name }}</option>
                                                    @endforeach
                                                </select>
                                                <span>If we are selling something please select transporter option</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Registration date :- <span class="required">*</span></label>
                                                <input type="date" value="{{ date('Y-m-d') }}" name="registrationDate" class="form-control" placeholder="dd/mm/yyyy">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Type :- <span class="required">*</span></label>
                                                <select class="form-control custom-select paymentType" name="paymentType">
                                                    <option readonly disabled="" selected="">--Select your Payment Type--</option>
                                                    @foreach ($payment_types as $payment)
                                                       <option value="{{ $payment->id }}">{{ $payment->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group" id="paymentTermAll">
                                                <label class="control-label">Payment Term</label>
                                                <select class="form-control custom-select" data-placeholder="" name="paymentTerm" id="paymentTerm" tabindex="1">
                                                     <option readonly disabled="" selected="">--Select Payment Term Type--</option>
                                                    @foreach ($payment_terms as $payment_term)
                                                       <option value="{{ $payment_term->id }}">{{ $payment_term->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Opening Balance :- <span class="required">*</span></label>
                                                <input type="number" step=".01" name="openingBalance" value="0.00" class="form-control" placeholder="Opening Balance" required autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Opening Balance As of Date :- <span class="required">*</span></label>
                                                <input type="date" name="openingBalanceAsOfDate" class="form-control" value="{{ date('Y-m-d') }}" placeholder="Opening Balance As of Date" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">GST Number</label>
                                                <input type="text" name="TRNNumber" class="form-control" placeholder="Enter GST Number" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">GST Document File</label>
                                                <input type="file" name="fileUpload" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 ">
                                            <div class="form-group">
                                                <label>Mobile</label>
                                                <input type="text" name="Mobile" placeholder="Mobile" class="form-control" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Phone</label>
                                                <input type="text" name="Phone" placeholder="Phone" class="form-control" autocomplete="off">
                                            </div>
                                        </div>

                                         <div class="col-md-4 ">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" name="Email" placeholder="Email" class="form-control" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 ">
                                            <div class="form-group">
                                                <label>Rate :- <span class="required">*</span></label>
                                                <input type="number" step=".01" name="Rate" placeholder="Rate" class="form-control" autocomplete="off" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>VAT :- <span class="required">*</span></label>
                                                <input type="number" step=".01" name="VAT" placeholder="VAT" class="form-control" autocomplete="off" value="0.00" onkeypress="return ((event.charCode >= 48 && event.charCode <= 57) )">
                                            </div>
                                        </div>

                                        <div class="col-md-4 ">
                                            <div class="form-group">
                                                <label>Credit Limit :- <span class="required">*</span></label>
                                                <input type="number" step=".01" name="customerLimit" placeholder="Credit Limit" class="form-control" autocomplete="off" value="0.00">
                                            </div>
                                        </div>
                                    </div>

                                    <h3 class="box-title m-t-40">Address</h3>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6 ">
                                            <div class="form-group">
                                                <label>Street</label>
                                                <input type="text" name="Address" placeholder="Address" class="form-control">
                                                <span>This will appear as address line in official documentation</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Region :- <span class="required">*</span></label>
                                                <select class="form-control custom-select region_id" name="region_id" id="region_id">
                                                    <option value="">-- Select Region --</option>
                                                    @foreach($regions as $region)
                                                        @if(!empty($region->Name))
                                                            <option value="{{ $region->id }}">{{ $region->Name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>City</label>
                                                <input type="text" name="City" id="city" placeholder="City" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>State</label>
                                                <input type="text" name="State" id="state" PLACEHOLDER="State" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Post Code</label>
                                                <input type="text" name="postCode" placeholder="PostCode" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>State</label>
                                                <input type="text" name="Country" id="country" PLACEHOLDER="Country" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea name="Description" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success" id="btnSubmit"><i class="fa fa-check"></i> Save</button>
                                    <a href="{{ route('customers.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
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
            $('#Name').keyup(function () {
                var Name='';
                Name = $('#Name').val();
                if (Name != '')
                {
                    var data={Name:Name};
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ URL('CheckCustomerExist') }}",
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
    <script>
        $(document).ready(function () {
            $('#paymentTermAll').hide();

        });
        $(document).on("change", '.paymentType', function () {
            var cash = $('.paymentType').val();
            // alert(cash);

            if (cash === '2'){
                $('#paymentTermAll').show();
            }
            else
            {
                $('#paymentTermAll').hide();
            }
        });
    </script>

    <script>
        /////////////////////////// location select /////////////////
        $(document).ready(function () {

            $('.region_id').change(function () {
                // alert();
                var Id = 0;
                Id = $(this).val();

                if (Id > 0)
                {
                    $.ajax({
                        url: "{{ URL('locationDetails') }}/" + Id,
                        type: "get",
                        dataType: "json",
                        success: function (result) {
                            if (result !== "Failed") {
                                console.log(result);
                                $('#city').val(result.city.Name);
                                $('#state').val(result.city.state.Name);
                                $('#country').val(result.city.state.country.Name);
                            } else {
                                alert(result);
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                }
            });

        });
        ////////////// end of location select ////////////////
    </script>
@endsection
