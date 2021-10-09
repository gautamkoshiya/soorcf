@extends('shared.layout-admin')
@section('title', 'GST Edit')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">GST Modification</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">GST</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-eye"></i> List</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">Unit</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('gsts.update', $gst->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <h3 class="card-title">Modification</h3>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">GST Name</label>
                                                <input type="text" id="Name" name="Name" value="{{ $gst->Name }}" class="form-control" placeholder="Unit Name">
                                                @if ($errors->has('Name'))
                                                    <span class="text-danger">{{ $errors->first('Name') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">GST Percentage :-<span class="required">*</span></label>
                                                <input type="number" min="0" max="28" id="percentage" name="percentage" class="form-control" required value="{{ $gst->percentage }}">
                                                @if ($errors->has('percentage'))
                                                    <span class="text-danger">{{ $errors->first('percentage') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>IsCombined ? :- <span class="required">*</span></label>
                                                <select class="form-control custom-select" id="IsCombined" name="IsCombined" required>
                                                    <option value="0" {{ ($gst->IsCombined == 0) ? 'selected':'' }}>No</option>
                                                    <option value="1" {{ ($gst->IsCombined == 1) ? 'selected':'' }}>Yes</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update</button>
                                        <a href="{{ route('gsts.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
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
