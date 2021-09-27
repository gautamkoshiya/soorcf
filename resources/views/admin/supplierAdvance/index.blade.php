@extends('shared.layout-admin')
@section('title', 'Supplier advances List')

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
                            <li class="breadcrumb-item active">Supplier Advance</li>
                        </ol>
                        <a href="{{ route('supplier_advances.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> New Supplier Advance</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Supplier Advances</h2>
                            <h5 class="required">** AFTER PUSH EDIT IS NOT ALLOWED SO VERIFY ENTRY BEFORE PUSH **</h5>
                            <div class="table-responsive m-t-40">
                                <table id="supplier_advances_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>SR#</th>
                                        <th>Supplier Name</th>
                                        <th>Amount</th>
                                        <th>Disbursed</th>
                                        <th>Remaining</th>
{{--                                        <th>Payment Type</th>--}}
                                        <th>Transfer Date</th>
                                        <th>Description</th>
                                        <th>REF#</th>
                                        <th>Push Advance</th>
                                        <th>Disburse</th>
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
                    <h5 class="modal-title" id="exampleModalLabel">Supplier Advance Detail</h5>
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
    <div class="modal fade" id="deleteSupplierAdvance" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                            <input name="_payment_id" type="hidden" id="_payment_id">
                        </div>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input class="btn btn-info" id="delete_payment_submit"  type="button" value="Delete">
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <script>
        $(document).on("click", ".paymentDelete", function () {
            var payment_id = $(this).data('id');
            $("#_payment_id").val(payment_id);
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
            $('#delete_payment_submit').click(function (event) {
                if (validateForm()) {
                    $('#delete_payment_submit').text('please wait...');
                    $('#delete_payment_submit').attr('disabled', true);
                    var deleteDescription = $('#deleteDescription').val();
                    var _payment_id = $('#_payment_id').val();
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ URL('supplier_advance_delete_post') }}",
                        type: "post",
                        data: {deleteDescription:deleteDescription,payment_id:_payment_id},
                        success: function (result) {
                            if (result === true) {
                                window.location.href = "{{ route('supplier_advances.index') }}";
                            } else {
                                alert('Something went wrong');
                                window.location.href = "{{ route('supplier_advances.index') }}";
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
                    url: "{{ URL('getSupplierAdvanceDetail') }}/"+id,
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

        function push_payment(e)
        {
            var id=e;
            id=id.split('_');
            id=id[1];
            if (id > 0)
            {
                $('#pay_'+id).text('please wait...');
                $('#pay_'+id).attr('disabled',true);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ URL('supplier_advances_push') }}/"+id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        location.reload();
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            }
        }
    </script>
    <script>
        function cancel_supplier_advance(e)
        {
            if(ConfirmDelete())
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
                        url: "{{ URL('cancelSupplierAdvance') }}/"+id,
                        type: "get",
                        dataType: "json",
                        success: function (result) {
                            if (result === true)
                            {
                                alert('Payment Cancelled Successfully.');
                                location.reload()
                            }
                            else
                            {
                                alert('Payment Cancellation Failed.');
                                location.reload()
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                }
            }
        }
    </script>
    <script>
        $(document).ready(function () {
            $('#supplier_advances_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ url('all_supplier_advance') }}",
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
                        data: 'supplier',
                        name: 'supplier'
                    },
                    {
                        data: 'Amount',
                        name: 'Amount'
                    },
                    {
                        data: 'spentBalance',
                        name: 'spentBalance'
                    },
                    {
                        data: 'remainingBalance',
                        name: 'remainingBalance'
                    },
                    // {
                    //     data: 'paymentType',
                    //     name: 'paymentType'
                    // },
                    {
                        data: 'TransferDate',
                        name: 'TransferDate'
                    },
                    {
                        data: 'Description',
                        name: 'Description'
                    },
                    {
                        data: 'receiptNumber',
                        name: 'receiptNumber'
                    },
                    {
                        data: 'push',
                        name: 'push',
                        orderable: false
                    },
                    {
                        data: 'disburse',
                        name: 'disburse',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]],
                pageLength : 10,
            });
        });
    </script>
    {{--<script>
        $(document).ready(function () {
            $('#supplier_advances_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('supplier_advances.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'supplier',
                        name: 'supplier'
                    },
                    {
                        data: 'Amount',
                        name: 'Amount'
                    },
                    {
                        data: 'spentBalance',
                        name: 'spentBalance'
                    },
                    {
                        data: 'remainingBalance',
                        name: 'remainingBalance'
                    },
                    {
                        data: 'paymentType',
                        name: 'paymentType'
                    },
                    {
                        data: 'TransferDate',
                        name: 'TransferDate'
                    },
                    {
                        data: 'Description',
                        name: 'Description'
                    },
                    {
                        data: 'receiptNumber',
                        name: 'receiptNumber'
                    },
                    {
                        data: 'push',
                        name: 'push',
                        orderable: false
                    },
                    {
                        data: 'disburse',
                        name: 'disburse',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]]
            });
        });
    </script>--}}
@endsection
