@extends('shared.layout-admin')
@section('title', 'Edit Task')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-8 align-self-center">
                    <h4 class="text-themecolor">Edit Task</h4>
                </div>
                <div class="col-md-4 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Edit Task</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('tasks.update', $task->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <h3 class="card-title">Edit Task</h3>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Date :- <span class="required">*</span></label>
                                                <input type="date" class="form-control" name="Date" id="Date" value="{{ $task->Date }}">
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Deadline Time :- <span class="required">*</span></label>
                                                <input type="time" class="form-control" name="CompletionTime" id="CompletionTime" value="{{ $task->CompletionTime }}">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label class="control-label">Note :- </label>
                                                <input type="text" class="form-control" name="Note" id="Note" placeholder="Note" autocomplete="off" value="{{ $task->Note }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Status :- <span class="required">*</span></label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="status" id="flexRadioDefault1" value="1" checked>
                                                    <label class="form-check-label" for="flexRadioDefault1">Completed</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="status" id="flexRadioDefault2" value="0" >
                                                    <label class="form-check-label" for="flexRadioDefault2">Pending</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions mt-3">
                                    <button type="submit" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Update</button>
                                    <a href="{{ route('review_task') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
