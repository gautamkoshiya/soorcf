@extends('shared.layout-admin')
@section('title', 'Employee Transactions List')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <a href="{{ URL('EmployeeAccountStatement') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-file-pdf-o"></i> Employee Account Statement </button></a>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">Employee Transactions</li>
                        </ol>
                        <a href="{{ route('employee_transactions.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Employee Transactions</h2>
                            <h5 class="required">** EDIT IS NOT ALLOWED SO VERIFY ENTRY BEFORE SAVE **</h5>
                            <div class="table-responsive m-t-40">
                                <table id="employee_transaction_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>SR#</th>
                                        <th>Employee</th>
                                        <th>Transaction Date</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>REF#</th>
                                        <th>Description</th>
                                        <th>User</th>
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
                            <label for="deleteDescription" class="control-label">Delete Note: <span class="required">*</span></label>
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
                        url: "{{ URL('employee_transaction_delete_post') }}",
                        type: "post",
                        data: {deleteDescription:deleteDescription,row_id:_row_id},
                        success: function (result) {
                            if (result === true) {
                                window.location.href = "{{ route('employee_transactions.index') }}";
                            } else {
                                alert('Something went wrong');
                                window.location.href = "{{ route('employee_transactions.index') }}";
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
                    url: "{{ URL('getCustomerPaymentDetail') }}/"+id,
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
            $('#employee_transaction_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('employee_transactions.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'employee',
                        name: 'employee'
                    },
                    {
                        data: 'createdDate',
                        name: 'createdDate'
                    },
                    {
                        data: 'Debit',
                        name: 'Debit'
                    },
                    {
                        data: 'Credit',
                        name: 'Credit'
                    },
                    {
                        data: 'referenceNumber',
                        name: 'referenceNumber'
                    },
                    {
                        data: 'TransactionDesc',
                        name: 'TransactionDesc'
                    },
                    {
                        data: 'User',
                        name: 'User'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ],
                order: [[ 0, "desc" ]],
                limit: 10,
            });
        });
    </script>
@endsection
