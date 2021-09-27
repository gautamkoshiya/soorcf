@extends('shared.layout-admin')
@section('title', 'Region Edit')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h4 class="text-themecolor">Region Modification</h4>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Region</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-eye"></i> List</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">Region</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('regions.update', $region->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <h3 class="card-title">Modification</h3>
                                    <hr>
                                    <div class="row p-t-20">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Region Name</label>
                                                <input type="text" id="Name" name="Name" value="{{ $region->Name }}" class="form-control" placeholder="Region Name">
                                                @if ($errors->has('Name'))
                                                    <span class="text-danger">{{ $errors->first('Name') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>City</label>
                                                <select class="form-control custom-select city_id" name="city_id" id="city_id">
                                                    <option>--Select City--</option>
                                                    @foreach($cities as $city)
                                                        <option value="{{ $city->id }}" {{ ($city->id == $region->city_id) ? 'selected':'' }}>{{ $city->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update</button>
                                        <a href="{{ route('regions.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
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
