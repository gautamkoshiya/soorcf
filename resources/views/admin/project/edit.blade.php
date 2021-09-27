@extends('shared.layout-admin')
@section('title', 'Project Edit')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">Project Modification</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Project</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">Project</h4>
                            <h6 class="required">* Fields are required please don't leave blank</h6>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('projects.update', $project->id) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <h3 class="card-title">Registration</h3>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Project Name :- <span class="required">*</span></label>
                                                <input type="text" id="Name" name="Name" value="{{ $project->Name }}" class="form-control" placeholder="Name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Address :- <span class="required">*</span></label>
                                                <input type="text" id="Address" value="{{ $project->Address }}" name="Address" class="form-control" placeholder="Address">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Contact :-<span class="required">*</span></label>
                                                <input type="text" id="Contact" name="Contact" value="{{ $project->Contact }}" class="form-control" placeholder="Contact" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Email :-<span class="required">*</span></label>
                                                <input type="email" id="Email" name="Email" value="{{ $project->Email }}" class="form-control" placeholder="Email" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">TRN :-<span class="required">*</span></label>
                                                <input type="text" id="TRN" name="TRN" value="{{ $project->TRN }}" class="form-control" placeholder="TRN" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">FAX :-<span class="required">*</span></label>
                                                <input type="text" id="FAX" name="FAX" value="{{ $project->FAX }}" class="form-control" placeholder="FAX" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Manager :-<span class="required">*</span></label>
                                                <input type="text" id="manager_name" name="manager_name" value="{{ $project->manager_name }}" class="form-control" placeholder="manager name" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Registration Date :- <span class="required">*</span></label>
                                                <input type="date" name="registration_date" class="form-control" value="{{ $project->registration_date }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Renewal Date :- <span class="required">*</span></label>
                                                <input type="date" name="renewal_date" class="form-control" value="{{ $project->renewal_date }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Logo :-</label>
                                                <input type="file" name="logo" id="logo" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Signature :-</label>
                                                <input type="file" name="signature" id="signature" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        @if($project->logo!='')
                                        <div class="col-md-6">
                                            <img src="{{$base.'/'.$project->logo}}" class="img-fluid" alt="current_logo">
                                            <input type="hidden" name="previous_logo" value="{{$project->logo}}">
                                        </div>
                                        @endif
                                        @if($project->signature!='')
                                        <div class="col-md-6">
                                            <img src="{{$base.'/'.$project->signature}}" class="img-fluid" alt="current_signature">
                                            <input type="hidden" name="previous_signature" value="{{$project->signature}}">
                                        </div>
                                        @endif
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update Customer</button>
                                    <a href="{{ route('projects.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
