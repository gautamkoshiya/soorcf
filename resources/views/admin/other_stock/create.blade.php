@extends('shared.layout-admin')
@section('title', 'Other Stock create')
@section('content')

<div class="page-wrapper">
<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h2 class="text-themecolor">Other Stock</h2>
        </div>
        <div class="col-md-7 align-self-center text-right">
            <div class="d-flex justify-content-end align-items-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                    <li class="breadcrumb-item active">Other Stock</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row">
        <h3 class="required"> * Please Verify all data before submit.</h3>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('other_stocks.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Date :- <span class="required">*</span></label>
                                        <input type="date" name="createdDate" id="createdDate" class="form-control" value="{{ date('Y-m-d') }}" placeholder="dd/mm/yyyy">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">In Stock :- <span class="required">*</span></label>
                                        <input type="number" name="in" id="in" class="form-control" value="0" placeholder="dd/mm/yyyy">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">Out Stock :- <span class="required">*</span></label>
                                        <input type="number" name="out" id="out" class="form-control" value="0" placeholder="dd/mm/yyyy">
                                    </div>
                                </div>
                                </div>
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Note :- </label>
                                            <div class="form-group">
                                                <textarea name="Description" id="Description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <button type="submit" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                <a href="{{ route('other_stocks.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
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
