@extends('shared.layout-admin')
@section('title', 'sales')

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
                            <li class="breadcrumb-item active">sale</li>
                        </ol>
                        <a href="{{ route('sales.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New sale</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Sale</h4>
                            <h6 class="card-subtitle">All Sales</h6>
                            <div class="table-responsive m-t-40">
                                <table id="sales_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th style="width: 100px">SR#</th>
                                        <th style="width: 100px">Date</th>
                                        <th style="width: 150px">Pad #</th>
                                        <th style="width: 150px">Customer</th>
                                        <th style="width: 150px">Vehicle</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>VAT</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
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
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Sales Payment Details</h5>
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
                    url: "{{ URL('getSalesPaymentDetail') }}/"+id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        $('#exampleModal').modal('toggle');
                        $('#modal_body').html(result);
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
            $('#sales_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('get_today_sale') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'SaleDate',
                        name: 'SaleDate'
                    },
                    {
                        data: 'PadNumber',
                        name: 'PadNumber'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'registrationNumber',
                        name: 'registrationNumber'
                    },
                    {
                        data: 'Quantity',
                        name: 'Quantity'
                    },
                    {
                        data: 'Price',
                        name: 'Price'
                    },
                    {
                        data: 'totalVat',
                        name: 'totalVat'
                    },
                    {
                        data: 'grandTotal',
                        name: 'grandTotal'
                    },
                    {
                        data: 'paidBalance',
                        name: 'paidBalance'
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
    </script>

<script>
    function ConfirmDelete()
    {
        var result = confirm("Are you sure you want to delete?");
        if(result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
</script>

@endsection
