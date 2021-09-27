@extends('shared.layout-admin')
@section('title', 'Companies create')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">Company Registration</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">company</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-eye"></i> List</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">Create New Company</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('companies.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-body">
                                    <h3 class="card-title">Registration</h3>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Company Name :- *</label>
                                                <input type="text" id="Name" name="Name" class="form-control" placeholder="Enter company Name" required>
                                                @if ($errors->has('Name'))
                                                    <span class="text-danger">{{ $errors->first('Name') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Representative :- *</label>
                                                <input type="text" id="Representative" name="Representative" class="form-control" placeholder="Representative Name">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Cash Opening Balance :- *</label>
                                                <input type="number" step=".01" name="openingBalance" value="0.00" class="form-control" placeholder="Opening Balance" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Opening Balance As of Date :- *</label>
                                                <input type="date" name="openingBalanceAsOfDate" class="form-control" value="{{ date('Y-m-d') }}" placeholder="Opening Balance As of Date" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Phone</label>
                                                <input type="text" id="Phone" name="Phone" class="form-control" placeholder="Enter Phone Number">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Mobile Number</label>
                                                <input type="text" id="Mobile" name="Mobile" class="form-control" placeholder="Mobile Number">
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
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Region</label>
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
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Save</button>
                                    <a href="{{ route('companies.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        /////////////////////////// location select /////////////////
        $(document).ready(function () {
            $('.region_id').change(function () {
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
