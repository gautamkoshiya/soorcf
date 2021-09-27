@extends('shared.layout-admin')
@section('title', 'File Manager')

@section('content')

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h2 class="text-themecolor">File Manager</h2>
            </div>
            <div class="col-md-7 align-self-center text-right">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                        <li class="breadcrumb-item active">File Manager</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('file_managers.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-body">
                                <h3 class="card-title">ADD Report File</h3>
                                <h6 class="required">* Fields are required please don't leave blank</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Report Type :- <span class="required">*</span></label>
                                            <select class="form-control custom-select" id="report_type_id" name="report_type_id">
                                                <option value="">--Select Report Type--</option>
                                                @foreach($report_type as $single)
                                                    <option value="{{ $single->id }}">{{ $single->Name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label">Report Date :- <span class="required">*</span></label>
                                            <input type="date" id="reportDate" name="reportDate" value="{{ date('Y-m-d') }}" class="form-control" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label">File Code :- <span class="required">*</span></label>
                                            <input type="text" class="form-control FileCode" name="FileCode" id="FileCode" value="{{ $newFileCode }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Note :- </label>
                                        <div class="form-group">
                                            <textarea name="Description" id="Description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Description"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="report_file">Select file :- <span class="required">*</span></label>
                                        <input class="form-control" type="file" id="report_file" name="report_file" multiple>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                <a href="{{ route('file_managers.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
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
        /////////////// validate form before submit //////////////////////
        // $('#submit').click(function () {
        // });
        //////// end of validate form before submit /////////////////
    });
</script>
@endsection
