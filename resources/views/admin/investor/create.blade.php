@extends('shared.layout-admin')
@section('title', 'Investor create')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h2 class="text-themecolor">Investor Registration</h2>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Investor</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h2 class="m-b-0 text-white">Investor</h2>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('investor.store') }}" enctype="multipart/form-data" id="customer_create" onsubmit="return validateForm()">
                                @csrf
                                <div class="form-body">
                                    <h3 class="card-title">Registration</h3>
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Investor Name :- <span class="required">*</span></label>
                                                <input type="text" id="Name" name="Name" class="form-control" placeholder="Enter Investor Name" required autocomplete="off" autofocus>
                                                @if ($errors->has('Name'))
                                                    <span class="text-danger">{{ $errors->first('Name') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Mobile</label>
                                                <input type="text" name="Mobile" placeholder="Mobile" class="form-control" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Share Percentage :- <span class="required">*</span></label>
                                            <input type="number" STEP="0.01" min="0" name="SharePercentage"  id="SharePercentage" class="form-control" autocomplete="off" onkeypress="return ((event.charCode >= 48 && event.charCode <= 57 || event.charCode==46) )" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success" id="btnSubmit"><i class="fa fa-check"></i> Save</button>
                                    <a href="{{ route('investor.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function (){
            function DoTrim(strComp) {
                ltrim = /^\s+/
                rtrim = /\s+$/
                strComp = strComp.replace(ltrim, '');
                strComp = strComp.replace(rtrim, '');
                return strComp;
            }

            function validateForm()
            {
                /*validation*/
                var fields;
                fields = "";

                if (DoTrim(document.getElementById('Name').value).length == 0)
                {
                    if(fields != 1)
                    {
                        document.getElementById("Name").focus();
                    }
                    fields = '1';
                    $("#Name").addClass("error");
                }

                if (DoTrim(document.getElementById('SharePercentage').value).length == 0)
                {
                    if(fields != 1)
                    {
                        document.getElementById("SharePercentage").focus();
                    }
                    fields = '1';
                    $("#SharePercentage").addClass("error");
                }

                if (fields != "")
                {
                    fields = "Please fill in the following details:" + fields;
                    return false;
                }
                else
                {
                    return true;
                }
                /*validation*/
            }
        });
    </script>
@endsection
