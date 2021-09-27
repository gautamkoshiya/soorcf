@extends('shared.layout-admin')
@section('title', 'Task Frequency Create')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">Task Frequency Registration</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Task Frequency</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-eye"></i> List</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">Task Frequency</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('task_frequencies.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-body">
                                    <h3 class="card-title">Registration</h3>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Frequency Name :- <span class="required">*</span></label>
                                                <input type="text" id="Name" name="Name" class="form-control" placeholder="Frequency Name" required>
                                                @if ($errors->has('Name'))
                                                    <span class="text-danger">{{ $errors->first('Name') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Save</button>
                                    <a href="{{ route('task_frequencies.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
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
