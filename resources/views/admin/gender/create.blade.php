@extends('shared.layout-admin')
@section('title', 'Gender')

@section('content')

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Gender</h4>
            </div>
            <div class="col-md-7 align-self-center text-right">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                        <li class="breadcrumb-item active">Gender</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header bg-info">
                        <h4 class="m-b-0 text-white">Gender</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('genders.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-body">
                                <h3 class="card-title">Registration</h3>
                                <hr>
                                <div class="row p-t-20">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Gender Name</label>
                                            <input type="text" id="Name" name="Name" class="form-control" placeholder="Gender Name" required autocomplete="off">
                                            @if ($errors->has('Name'))
                                                <span class="text-danger">{{ $errors->first('Name') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Save</button>
                                <a href="{{ route('genders.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                            </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
