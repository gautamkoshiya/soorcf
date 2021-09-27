@extends('shared.layout-admin')
@section('title', 'Meter Create')

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
                        <h4 class="m-b-0 text-white">Meter Reader</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form action="{{ route('meter_readers.store') }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-body">

                                        <div class="row p-t-20">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Meter Name</label>
                                                    <input type="text" id="Name" name="Name" class="form-control" placeholder="Enter Meter Name">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <textarea name="Description" id="description"  cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Save</button>
                                        <a href="{{ route('meter_readings.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                                    </div>
                                </form>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group" hidden>
                                    <textarea name="" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                </div>
                                <div class="table-responsive" style="margin-top: 20px">
                                    <table class="table color-table inverse-table">
                                        <thead>
                                        <tr>
                                            <th>Meter Name</th>
                                            <th>Description</th>
                                            <th style="width: 150px">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($meter_readers as $records)
                                            <tr id="rowData" style="background: #1285ff;color: white;font-size: 12px">
                                                <td>{{ $records->Name }}</td>
                                                <td>{{ $records->shortDescriptionForm }}</td>
                                                <td>
                                                    <form action="{{ route('meter_readers.destroy',$records->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <a href="{{ route('meter_readers.edit', $records->id) }}"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>
                                                        <button type="submit" class=" btn btn-danger btn-sm" onclick="return confirm('Are you sure to Delete?')"><i style="font-size: 20px" class="fa fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
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
