@extends('shared.layout-admin')
@section('title', 'Customer App Data Edit')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h2 class="text-themecolor">Customer App Data</h2>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Customer App Data</li>
                        </ol>
                        <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-eye"></i> List</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="m-b-0 text-white">Customer App Data</h4>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('ChangeCustomerAppData', $customer->id) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-body">
                                    <h3 class="card-title">Registration</h3>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-4 ">
                                            <div class="form-group">
                                                <label class="control-label">Name :- </label>
                                                <input type="text" value="{{ $customer->Name }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>App Login Email :-</label>
                                                <input type="text" name="login_email" value="{{ $customer->login_email }}" placeholder="App Login Email" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Password :-</label>
                                                <input type="password" name="password" value="{{ $customer->password }}" placeholder="password" class="form-control">
                                            </div>
                                        </div>


                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Update Customer</button>
                                    <a href="{{ route('customers.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
