@extends('shared.layout-admin')
@section('title', 'ADD Task')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-8 align-self-center">
                    <h4 class="text-themecolor">Task Master</h4>
                </div>
                <div class="col-md-4 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">New Task Master</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('task_masters.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-body">
                                    <h3 class="card-title">ADD Task</h3>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Select Frequency :- <span class="required">*</span></label>
                                                <select class="form-control custom-select select2 frequency_id" name="frequency_id" id="frequency_id" required>
                                                    <option value=""> ---- Select Frequency ---- </option>
                                                    @foreach($task_frequency as $single)
                                                        <option value="{{ $single->id }}">{{ $single->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">User :- <span class="required">*</span></label>
                                                <select class="form-control custom-select select2 assigned_to" name="assigned_to" id="assigned_to" required>
                                                    <option value=""> ---- Select User ---- </option>
                                                    @foreach($users as $single)
                                                        <option value="{{ $single->id }}">{{ $single->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                            <label class="control-label">Start Date :- <span class="required">*</span></label>
                                            <input type="date" class="form-control" name="StartDate" id="StartDate" value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                            <label class="control-label">End Date :- <span class="required">*</span></label>
                                            <input type="date" class="form-control" name="EndDate" id="EndDate" value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                            <label class="control-label">Deadline Time :- <span class="required">*</span></label>
                                            <input type="time" class="form-control" name="CompletionTime" id="CompletionTime">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            <label class="control-label">Task Name :- <span class="required">*</span></label>
                                            <input type="text" class="form-control" name="Name" id="Name" placeholder="Task Name" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                            <label class="control-label">Description :- </label>
                                            <input type="text" class="form-control" name="Description" id="Description" placeholder="Description" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions mt-3">
                                    <button type="submit" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                    <a href="{{ route('task_masters.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
