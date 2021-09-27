@extends('shared.layout-admin')
@section('title', 'PURCHASE')

@section('content')

    <script type="text/javascript">
        function get_pdf(id)
        {
            $.ajax({
                type : "GET",
                url : "{{ URL('purchasePrint') }}/" + id,
            }).done(function(data){
                window.open(data,'_blank');
            });
        }
    </script>
    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">Purchase</li>
                        </ol>
                        <a href="{{ route('purchases.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New Purchase</button></a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Purchases</h2>
                            <h5 class="required" style="float: right;">[SEARCH BY PAD] [SEARCH BY LPO]</h5>
                            <div class="table-responsive m-t-40">
                                <table id="purchase_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th style="width: 100px">SR#</th>
                                        <th style="width: 100px">Purchase Date</th>
                                        <th style="width: 150px">LPO#</th>
                                        <th style="width: 100px">PAD#</th>
                                        <th style="width: 100px">Supplier</th>
                                        <th style="width: 150px">Due date</th>
                                        <th style="width: 150px">Quantity</th>
                                        <th style="width: 150px">Amount</th>
                                        <th>Vat</th>
                                        <th>Total Amount</th>
                                        <th>Paid</th>
                                        <th>Remaining</th>
                                        <th style="width: 100px">Action</th>
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
                    <h5 class="modal-title" id="exampleModalLabel">Purchase Payment Details</h5>
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
    <!-- Modal -->
    <div class="modal fade" id="deleteRow" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Why you want delete this entry ?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal_body">
                    <form action="#">
                        @csrf
                        <div class="form-group">
                            <label for="message-texta" class="control-label">Delete Note: <span class="required">*</span></label>
                            <textarea name="deleteDescription" class="form-control" id="deleteDescription" placeholder="Delete Note"></textarea>
                            <input name="_row_id" type="hidden" id="_row_id">
                        </div>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input class="btn btn-info" id="delete_row_submit"  type="button" value="Delete">
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <script>
        $(document).on("click", ".deleteRow", function () {
            var _row_id = $(this).data('id');
            $("#_row_id").val(_row_id);
        });

        function DoTrim(strComp) {
            ltrim = /^\s+/
            rtrim = /\s+$/
            strComp = strComp.replace(ltrim, '');
            strComp = strComp.replace(rtrim, '');
            return strComp;
        }

        function validateForm()
        {
            /*validation*/
            var fields;
            fields = "";

            if (DoTrim(document.getElementById('deleteDescription').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("deleteDescription").focus();
                }
                fields = '1';
                $("#deleteDescription").addClass("error");
            }

            if (fields != "")
            {
                fields = "Please fill in the following details:" + fields;
                return false;
            }
            else
            {
                return true;
            }
            /*validation*/
        }

        $(document).ready(function () {
            $('#delete_row_submit').click(function (event) {
                if (validateForm()) {
                    $('#delete_row_submit').text('please wait...');
                    $('#delete_row_submit').attr('disabled', true);
                    var deleteDescription = $('#deleteDescription').val();
                    var _row_id = $('#_row_id').val();
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ URL('purchase_delete_post') }}",
                        type: "post",
                        data: {deleteDescription:deleteDescription,row_id:_row_id},
                        success: function (result) {
                            if (result === true) {
                                window.location.href = "{{ route('purchases.index') }}";
                            } else {
                                alert('Something went wrong');
                                window.location.href = "{{ route('purchases.index') }}";
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                }
            })
        });
    </script>
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
                    url: "{{ URL('getPurchasePaymentDetail') }}/"+id,
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
            $('#purchase_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ url('all_purchase') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}"}
                },
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'PurchaseDate',
                        name: 'PurchaseDate'
                    },
                    {
                        data: 'referenceNumber',
                        name: 'referenceNumber'
                    },
                    {
                        data: 'PadNumber',
                        name: 'PadNumber'
                    },
                    {
                        data: 'supplier',
                        name: 'supplier'
                    },
                    {
                        data: 'DueDate',
                        name: 'DueDate'
                    },
                    {
                        data: 'Quantity',
                        name: 'Quantity'
                    },
                    {
                        data: 'Total',
                        name: 'Total'
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
                        data: 'remainingBalance',
                        name: 'remainingBalance'
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
            $('#purchase_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('purchases.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'createdDate',
                        name: 'createdDate'
                    },
                    {
                        data: 'referenceNumber',
                        name: 'referenceNumber'
                    },
                    {
                        data: 'PadNumber',
                        name: 'PadNumber'
                    },
                    {
                        data: 'supplier',
                        name: 'supplier'
                    },
                    {
                        data: 'DueDate',
                        name: 'DueDate'
                    },
                    {
                        data: 'Quantity',
                        name: 'Quantity'
                    },
                    {
                        data: 'Total',
                        name: 'Total'
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
                        data: 'remainingBalance',
                        name: 'remainingBalance'
                    },
                    // {
                    //     data: 'isActive',
                    //     name: 'isActive',
                    //     orderable: false
                    // },
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
@endsection
