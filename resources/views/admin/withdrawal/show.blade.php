@extends('shared.layout-admin')
@section('title', 'Customer Payment Details List')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">payment details</li>
                        </ol>
                        <a href="{{ route('payment_receives.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Customer Payment Details</h4>
                            <h6 class="card-subtitle">All Payment Details</h6>
                            <div class="table-responsive m-t-40">
                                <table id="customer_payments_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Total Amount Paid</th>
                                        <th>Date</th>
                                        <th width="150">Amount</th>
                                        <th width="150"><a href="{{ route('payment_receives.index') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-bars"></i> Go Back </button></a>
                                        </th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($payment_receives_details as $details)
                                            <tr>
                                                <td>{{ $details->payment_receive->customer->Name ?? '' }}</td>
                                                <td>{{ $details->payment_receive->paidAmount ?? '' }}</td>
                                                <td>{{ $details->createdDate ?? '' }}</td>
                                                <td colspan="2">{{ $details->amountPaid ?? '' }}</td>

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
@endsection
