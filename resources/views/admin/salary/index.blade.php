@extends('shared.layout-admin')
@section('title', 'Salary List')

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
                            <li class="breadcrumb-item active">Salary</li>
                        </ol>
                        <a href="{{ route('salaries.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Generate Salary</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Salary</h2>
                            <div class="table-responsive m-t-40">
                                <table id="salary_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>SR#</th>
                                        <th>Company</th>
                                        <th>TotalAmount</th>
                                        <th>Month</th>
                                        <th>Year</th>
                                        <th>GeneratedDate</th>
                                        <th>GeneratedBy</th>
                                        <th width="100">Action</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Customer Payment Detail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal_body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <script>
        function show_detail(e)
        {
            var id=e;
            id=id.split('_');
            id=id[1];
            if (id > 0)
            {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ URL('printSalary') }}/"+id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        window.open(result.url,'_blank');
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            }
        }
    </script>
    <script>
        $(document).ready(function () {
            $('#salary_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ route('salaries.index') }}",
                },
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                        visible: false,
                    },
                    {
                        data: 'company',
                        name: 'company',
                    },
                    {
                        data: 'TotalAmount',
                        name: 'TotalAmount',
                    },
                    {
                        data: 'Month',
                        name: 'Month',
                    },
                    {
                        data: 'Year',
                        name: 'Year',
                    },
                    {
                        data: 'createdDate',
                        name: 'createdDate',
                    },
                    {
                        data: 'GeneratedBy',
                        name: 'GeneratedBy',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable : false,
                    },
                ],
                order: [[ 0, "desc" ]],
                pageLength : 10,
            });
        });
    </script>
    {{--<script>
        $(document).ready(function () {
            $('#customer_payments_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('payment_receives.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'paymentReceiveDate',
                        name: 'paymentReceiveDate'
                    },
                    {
                        data: 'paidAmount',
                        name: 'paidAmount'
                    },
                    {
                        data: 'referenceNumber',
                        name: 'referenceNumber'
                    },
                    {
                        data: 'payment_type',
                        name: 'payment_type'
                    },
                    {
                        data: 'Description',
                        name: 'Description'
                    },
                    {
                        data: 'push',
                        name: 'push',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]],
                dom: 'Blfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
            });
        });
    </script>--}}
@endsection
