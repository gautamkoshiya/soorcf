@extends('shared.layout-admin')
@section('title', 'Delivery Notes')

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
                            <li class="breadcrumb-item active">Delivery Notes</li>
                        </ol>
                        <a href="{{ route('delivery_notes.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New Delivery Note</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Delivery Note</h2>
                            <h5 class="required" style="float: right;">[search by DO Number ]</h5>
                            <div class="table-responsive m-t-40">
                                <table id="delivery_note_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th style="width: 100px">SR#</th>
                                        <th style="width: 100px">DO Number</th>
                                        <th style="width: 150px">Date</th>
                                        <th style="width: 150px">Ref#</th>
                                        <th style="width: 150px">Customer</th>
                                        <th style="width: 150px">Project</th>
                                        <th>Product</th>
                                        <th>Unit</th>
                                        <th>Quantity</th>
                                        <th style="width: 40px">Action</th>
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
    <script>
        function show_detail(e)
        {
            var id=e;
            id=id.split('_');
            id=id[1];
            if (id > 0)
            {
                $.ajax({
                    url: "{{ URL('PrintDeliveryNote') }}/"+id,
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

    {{--<script>
        $(document).ready(function () {
            $('#delivery_note_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('delivery_notes.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'DoNumber',
                        name: 'DoNumber'
                    },
                    {
                        data: 'createdDate',
                        name: 'createdDate'
                    },
                    {
                        data: 'OrderReference',
                        name: 'OrderReference'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'project',
                        name: 'project'
                    },
                    {
                        data: 'product',
                        name: 'product'
                    },
                    {
                        data: 'unit',
                        name: 'unit'
                    },
                    {
                        data: 'Quantity',
                        name: 'Quantity'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]],
                pageLength : 10,
            });
        });
    </script>--}}

    <script>
        $(document).ready(function () {
            $('#delivery_note_table').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    "url" : "{{ url('all_delivery_note') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}"},
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false,
                    },
                    {
                        data: 'DoNumber',
                        name: 'DoNumber'
                    },
                    {
                        data: 'createdDate',
                        name: 'createdDate'
                    },
                    {
                        data: 'OrderReference',
                        name: 'OrderReference'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'project',
                        name: 'project'
                    },
                    {
                        data: 'product',
                        name: 'product'
                    },
                    {
                        data: 'unit',
                        name: 'unit'
                    },
                    {
                        data: 'Quantity',
                        name: 'Quantity'
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
@endsection
