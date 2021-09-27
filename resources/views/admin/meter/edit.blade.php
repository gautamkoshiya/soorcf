@extends('shared.layout-admin')
@section('title', 'Meter Edit')

@section('content')

<div class="page-wrapper" style="margin-bottom: 20px">

<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
        </div>
        <div class="col-md-7 align-self-center text-right">
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-info">
                    <h4 class="m-b-0 text-white">Meter Reader Modification</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <form action="{{ route('meter_readers.update', $meter_reader->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <div class="row p-t-20">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label">Meter Name</label>
                                                <input type="text" id="Name" name="Name" value="{{ $meter_reader->Name }}" class="form-control" placeholder="Enter Meter Name">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea name="Description" id="description"  cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note">{{ $meter_reader->shortDescriptionForm }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update</button>
                                    <button type="button" class="btn btn-inverse">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    $(document).ready(function () {

    });
</script>
@endsection
