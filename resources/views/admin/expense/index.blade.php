@extends('shared.layout-admin')
@section('title', 'Expenses')

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
                            <li class="breadcrumb-item active">expenses</li>
                        </ol>
                        <a href="{{ route('expenses.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> create new</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Expenses</h2>
                            <div class="table-responsive m-t-40">
                                <table id="expense_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <th>SR#</th>
                                        <th style="width: 100px">Date</th>
                                        <th>Supplier</th>
                                        <th style="width: 150px">REF#</th>
                                        <th style="width: 150px">Category</th>
                                        <th>Sub Total</th>
                                        <th>VAT</th>
                                        <th>Total Amount</th>
                                        <th>PaymentMode</th>
                                        <th>Action</th>
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
                    <h5 class="modal-title" id="exampleModalLabel">Expense Detail</h5>
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
                    url: "{{ URL('getExpenseDetail') }}/"+id,
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
    <!-- Modal -->
    <div class="modal fade" id="deleteRow" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Why you want to delete this entry ?</h5>
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
                        url: "{{ URL('expense_delete_post') }}",
                        type: "post",
                        data: {deleteDescription:deleteDescription,row_id:_row_id},
                        success: function (result) {
                            if (result === true) {
                                window.location.href = "{{ route('expenses.index') }}";
                            } else {
                                alert('Something went wrong');
                                window.location.href = "{{ route('expenses.index') }}";
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
    {{-- for ajax pagination records   --}}
    <script>
        $(document).ready(function () {
            $('#expense_table').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    "url" : "{{ url('all_expenses') }}",
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
                        data: 'expenseDate',
                        name: 'expenseDate',
                    },
                    {
                        data: 'supplier',
                        name: 'supplier',
                    },
                    {
                        data: 'referenceNumber',
                        name: 'referenceNumber',
                    },
                    {
                        data: 'expenseCategory',
                        name: 'expenseCategory',
                    },
                    {
                        data: 'subTotal',
                        name: 'subTotal',
                    },
                    {
                        data: 'totalVat',
                        name: 'totalVat',
                    },
                    {
                        data: 'grandTotal',
                        name: 'grandTotal',
                    },
                    {
                        data: 'payment_type',
                        name: 'payment_type',
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
    {{-- for ajax pagination records   --}}

    {{-- for all records   --}}
    {{--<script>
        $(document).ready(function () {
            $('#expense_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('expenses.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'expenseDate',
                        name: 'expenseDate'
                    },
                    {
                        data: 'supplier',
                        name: 'supplier'
                    },
                    {
                        data: 'referenceNumber',
                        name: 'referenceNumber'
                    },
                    {
                        data: 'expenseCategory',
                        name: 'expenseCategory'
                    },
                    {
                        data: 'subTotal',
                        name: 'subTotal'
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
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]]
            });
        });
    </script>--}}
    {{-- for all records   --}}
@endsection
