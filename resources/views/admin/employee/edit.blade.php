@extends('shared.layout-admin')
@section('title', 'Employee Edit')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h2 class="text-themecolor">Employee Modification</h2>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Employee</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">Edit Employee</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('employees.update',$employee->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <h3 class="card-title">Modification</h3>
                                    <hr>
                                    <h3 class="card-title"><u><i>Basic Information</i></u></h3>
                                    <div class="row p-t-20">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label">Employee Name :- <span class="required">*</span></label>
                                                <input type="text" id="Name" name="Name" class="form-control" placeholder="Enter Employee Name" required autocomplete="off" value="{{ $employee->Name }}">
                                                @if ($errors->has('Name'))
                                                    <span class="text-danger">{{ $errors->first('Name') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label">Emergency Contact Number</label>
                                                <input type="text" id="emergencyContactNumber" name="emergencyContactNumber" class="form-control" placeholder="Emergency Contact Number" autocomplete="off" value="{{ $employee->emergencyContactNumber }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label">Mobile Number :- <span class="required">*</span></label>
                                                <input type="text" id="Mobile" name="Mobile" class="form-control" placeholder="Mobile Number" required autocomplete="off" value="{{ $employee->Mobile }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label">Project (visa belongs) :- <span class="required">*</span></label>
                                                <select class="form-control custom-select select2 UpdateDescription" name="UpdateDescription" id="UpdateDescription" required>
                                                    <option value=""> Select Project </option>
                                                    @foreach($projects as $single)
                                                        <option value="{{ $single->id }}" {{ ($single->id == $employee->UpdateDescription) ? 'selected':'' }}>{{ $single->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row p-t-20">

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Company :- <span class="required">*</span></label>
                                                <select class="form-control custom-select select2 company_id" name="company_id" id="company_id" required>
                                                    <option value=""> Select Company </option>
                                                    @foreach($companies as $single)
                                                        <option value="{{ $single->id }}" {{ ($single->id == $employee->company_id) ? 'selected':'' }}>{{ $single->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Department :- <span class="required">*</span></label>
                                                <select class="form-control custom-select select2 department_id" name="department_id" id="department_id" required>
                                                    <option value=""> Select Department </option>
                                                    @foreach($department as $single)
                                                        <option value="{{ $single->id }}" {{ ($single->id == $employee->department_id) ? 'selected':'' }}>{{ $single->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Designation :- <span class="required">*</span></label>
                                                <select class="form-control custom-select select2 designation_id" name="designation_id" id="designation_id" required>
                                                    <option value=""> Select Designation </option>
                                                    @foreach($designation as $single)
                                                        <option value="{{ $single->id }}" {{ ($single->id == $employee->designation_id) ? 'selected':'' }}>{{ $single->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Date of Join :- </label>
                                                <input type="date" id="startOfJob" name="startOfJob" class="form-control" value="{{ $employee->startOfJob }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Birth Date :- </label>
                                                <input type="date" id="DOB" name="DOB" class="form-control" value="{{ $employee->DOB }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="passport_file">Photo :- </label>
                                            <input class="form-control" type="file" id="photo" name="photo">
                                            <span><a href="{{url('/storage/app/public/file_manager/employee_docs/').'/'.$employee->photo}}">view_current_file</a></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Nationality :- <span class="required">*</span></label>
                                                <select class="form-control custom-select select2 nationality_id" name="nationality_id" id="nationality_id" required>
                                                    <option value=""> Select Nationality </option>
                                                    @foreach($nationality as $single)
                                                        <option value="{{ $single->id }}" {{ ($single->id == $employee->nationality_id) ? 'selected':'' }}>{{ $single->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Gender :- <span class="required">*</span></label>
                                                <select class="form-control custom-select select2 gender_id" name="gender_id" id="gender_id" required>
                                                    <option value=""> Select Gender </option>
                                                    @foreach($gender as $single)
                                                        <option value="{{ $single->id }}" {{ ($single->id == $employee->gender_id) ? 'selected':'' }}>{{ $single->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Email :-</label>
                                                <input type="email" id="email" name="email" class="form-control" placeholder="email" autocomplete="off" maxlength="120" value="{{ $employee->email }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Basic Salary :- </label>
                                                <input type="number" min="0" id="Basic" name="Basic" class="form-control" value="{{ $employee->Basic }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Labour Code :- </label>
                                                <input type="text" maxlength="14"  id="labour_code" name="labour_code" class="form-control" value="{{ $employee->labour_code }}">
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                    <h3 class="card-title"><u><i>Passport</i></u></h3>
                                    <div class="row p-t-20">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Passport Number :- </label>
                                                <input type="text" id="passportNumber" name="passportNumber" class="form-control" placeholder="Enter Passport Number" autocomplete="off" value="{{ $employee->passportNumber }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Issue Date :- </label>
                                                <input type="date" id="passport_issue_date" name="passport_issue_date" class="form-control" value="{{ $employee->passport_issue_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Expire Date :- </label>
                                                <input type="date" id="passport_expire_date" name="passport_expire_date" class="form-control" value="{{ $employee->passport_expire_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="passport_doc">Select file :- </label>
                                            <input class="form-control" type="file" id="passport_doc" name="passport_doc">
                                            <span><a href="{{url('/storage/app/public/file_manager/employee_docs/').'/'.$employee->passport_doc}}">view_current_file</a></span>
                                        </div>
                                    </div>
                                    <hr>

                                    <h3 class="card-title"><u><i>Visa</i></u></h3>
                                    <div class="row p-t-20">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Visa Reference Number :- </label>
                                                <input type="text" id="visa_reference_number" name="visa_reference_number" class="form-control" placeholder="Visa Number" autocomplete="off" value="{{ $employee->visa_reference_number }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Issue Date :- </label>
                                                <input type="date" id="visa_issue_date" name="visa_issue_date" class="form-control" value="{{ $employee->visa_issue_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Expire Date :- </label>
                                                <input type="date" id="visa_expire_date" name="visa_expire_date" class="form-control" value="{{ $employee->visa_expire_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="visa_doc">Select file :- </label>
                                            <input class="form-control" type="file" id="visa_doc" name="visa_doc">
                                            <span><a href="{{url('/storage/app/public/file_manager/employee_docs/').'/'.$employee->visa_doc}}">view_current_file</a></span>
                                        </div>
                                    </div>
                                    <hr>

                                    <h3 class="card-title"><u><i>Medical Insurance</i></u></h3>
                                    <div class="row p-t-20">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Reference Number :- </label>
                                                <input type="text" id="insurance_reference_number" name="insurance_reference_number" class="form-control" placeholder="Enter Number" autocomplete="off" value="{{ $employee->insurance_reference_number }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Issue Date :- </label>
                                                <input type="date" id="insurance_issue_date" name="insurance_issue_date" class="form-control" value="{{ $employee->insurance_issue_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Expire Date :- </label>
                                                <input type="date" id="insurance_expire_date" name="insurance_expire_date" class="form-control" value="{{ $employee->insurance_expire_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="insurance_doc">Select file :- </label>
                                            <input class="form-control" type="file" id="insurance_doc" name="insurance_doc">
                                            <span><a href="{{url('/storage/app/public/file_manager/employee_docs/').'/'.$employee->insurance_doc}}">view_current_file</a></span>
                                        </div>
                                    </div>
                                    <hr>

                                    <h3 class="card-title"><u><i>Driving License</i></u></h3>
                                    <div class="row p-t-20">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Reference Number :- </label>
                                                <input type="text" id="driving_licence_reference_number" name="driving_licence_reference_number" class="form-control" placeholder="Number" autocomplete="off" value="{{ $employee->driving_licence_reference_number }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Issue Date :- </label>
                                                <input type="date" id="driving_licence_issue_date" name="driving_licence_issue_date" class="form-control" value="{{ $employee->driving_licence_issue_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Expire Date :- </label>
                                                <input type="date" id="driving_licence_expire_date" name="driving_licence_expire_date" class="form-control" value="{{ $employee->driving_licence_expire_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="driving_licence_doc">Select file :- </label>
                                            <input class="form-control" type="file" id="driving_licence_doc" name="driving_licence_doc">
                                            <span><a href="{{url('/storage/app/public/file_manager/employee_docs/').'/'.$employee->driving_licence_doc}}">view_current_file</a></span>
                                        </div>
                                    </div>
                                    <hr>

                                    <h3 class="card-title"><u><i>Emirates ID</i></u></h3>
                                    <div class="row p-t-20">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Reference Number :- </label>
                                                <input type="text" id="identityNumber" name="identityNumber" class="form-control" placeholder="Enter Number" autocomplete="off" value="{{ $employee->identityNumber }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Issue Date :- </label>
                                                <input type="date" id="emi_id_issue_date" name="emi_id_issue_date" class="form-control" value="{{ $employee->emi_id_issue_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Expire Date :- </label>
                                                <input type="date" id="emi_id_expire_date" name="emi_id_expire_date" class="form-control" value="{{ $employee->emi_id_expire_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="emi_id_doc">Select file :- </label>
                                            <input class="form-control" type="file" id="emi_id_doc" name="emi_id_doc" >
                                            <span><a href="{{url('/storage/app/public/file_manager/employee_docs/').'/'.$employee->emi_id_doc}}">view_current_file</a></span>
                                        </div>
                                    </div>
                                    <hr>

                                    <h3 class="card-title"><u><i>Other Information (if any)</i></u></h3>
                                    <div class="row p-t-20">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Reference Number :- </label>
                                                <input type="text" id="other_reference_number" name="other_reference_number" class="form-control" placeholder="Enter Number" autocomplete="off" value="{{ $employee->other_reference_number }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Issue Date :- </label>
                                                <input type="date" id="other_issue_date" name="other_issue_date" class="form-control" value="{{ $employee->other_issue_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Expire Date :- </label>
                                                <input type="date" id="other_expire_date" name="other_expire_date" class="form-control" value="{{ $employee->other_expire_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="other_doc">Select file :- </label>
                                            <input class="form-control" type="file" id="other_doc" name="other_doc">
                                            <span><a href="{{url('/storage/app/public/file_manager/employee_docs/').'/'.$employee->other_doc}}">view_current_file</a></span>
                                        </div>
                                    </div>
                                    <hr>

                                    <h3 class="box-title m-t-40">Address</h3>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6 ">
                                            <div class="form-group">
                                                <label>Street</label>
                                                <input type="text" name="Address" value="{{ $employee->Address }}" placeholder="Address" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Region</label>
                                                <select class="form-control custom-select region_id" name="region_id" id="region_id">
                                                    <option value="">-- Select Region --</option>
                                                    @foreach($regions as $region)
                                                        @if(!empty($region->Name))
                                                            <option value="{{ $region->id }}" {{ ($region->id == $employee->region_id) ? 'selected':'' }}>{{ $region->Name }}</option>
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
                                                <input type="text" name="City" id="city" value="{{ $employee->region->city->Name ?? "" }}" placeholder="City" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>State</label>
                                                <input type="text" name="State" id="state" value="{{ $employee->region->city->state->Name ?? "" }}" PLACEHOLDER="State" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Country</label>
                                                <input type="text" name="Country" id="country" value="{{ $employee->region->city->state->country->Name ?? "" }}" PLACEHOLDER="Country" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea name="Description" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note">{{ $employee->Description }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update Employee</button>
                                    <a href="{{ route('employees.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
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
