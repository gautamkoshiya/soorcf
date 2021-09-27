@extends('shared.layout-admin')
@section('title', 'Inward Loans')

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
                            <li class="breadcrumb-item active">Inward Loans</li>
                        </ol>
                    </div>
                </div>
            </div>
            <h3 class="required"> * Only Enter Short Term Loans if it is additional investment ask admin to make Investor Journal Entry.</h3>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-10 col-sm-2"><h2 class="card-title">Inward Loans</h2></div>
                                <div class="col-md-1 col-sm-2"><a href="{{ route('inward_loans.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</button></a></div>
                            </div>
                            <div class="table-responsive m-t-40">
                                <table id="financers_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>SR#</th>
                                        <th>Loan Date</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Remaining</th>
                                        <th>referenceNumber</th>
                                        <th>Financer</th>
                                        <th>Payment Type</th>
                                        <th>Push Loan</th>
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
    <div class="modal fade" id="deleteInwardLoan" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                        <input class="btn btn-info" id="delete_loan_submit"  type="button" value="Delete">
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <script>
        $(document).on("click", ".inwardLoanDelete", function () {
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
            $('#delete_loan_submit').click(function (event) {
                if (validateForm()) {
                    $('#submit').text('please wait...');
                    $('#submit').attr('disabled', true);
                    var deleteDescription = $('#deleteDescription').val();
                    var _row_id = $('#_row_id').val();
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ URL('inward_loan_delete_post') }}",
                        type: "post",
                        data: {deleteDescription:deleteDescription,row_id:_row_id},
                        success: function (result) {
                            if (result === true) {
                                window.location.href = "{{ route('inward_loans.index') }}";
                            } else {
                                alert('Something went wrong');
                                window.location.href = "{{ route('inward_loans.index') }}";
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
        $(document).ready(function () {
            $('#financers_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('inward_loans.index') }}",
                },
                columns:[
                    {
                        data: 'id',
                        name: 'id',
                        visible: false
                    },
                    {
                        data: 'loanDate',
                        name: 'loanDate'
                    },
                    {
                        data: 'totalAmount',
                        name: 'totalAmount'
                    },
                    {
                        data: 'inward_PaidBalance',
                        name: 'inward_PaidBalance'
                    },
                    {
                        data: 'inward_RemainingBalance',
                        name: 'inward_RemainingBalance'
                    },
                    {
                        data: 'referenceNumber',
                        name: 'referenceNumber'
                    },
                    {
                        data: 'financer',
                        name: 'financer'
                    },
                    {
                        data: 'payment_type',
                        name: 'payment_type'
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
            });
        });
    </script>
    <script>
        function ConfirmDelete()
        {
         var result = confirm("Are you sure you want to delete?");
         if (result) {
            document.getElementById("deleteData").submit();
         }
        }
    </script>
@endsection
