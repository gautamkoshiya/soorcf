@extends('shared.layout-admin')
@section('title', 'Expense Category List')

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
                            <li class="breadcrumb-item active">expense Category</li>
                        </ol>
                        <button type="button" class="btn btn-info d-lg-block m-l-15 insert"><i class="fa fa-plus-circle"></i> Create New</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Expense Categories</h2>
                            <div class="table-responsive m-t-40">
                                <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Is Active</th>
                                        <th width="100">Action</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($expense_categories as $category)
                                        <tr>
                                            <td>{{ $category->Name }}</td>
                                            <td>
                                                @if(!empty($category->isActive))
                                                    Active
                                                    @else
                                                    Not Active
                                                @endif
                                            </td>
                                            <td>
                                                @if($category->id != 31 || $category->id != 12)
                                                <form action="{{ route('expense_categories.destroy',$category->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="{{ route('expense_categories.edit', $category->id) }}"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>
{{--                                                    <button type="submit" class=" btn btn-danger btn-sm" onclick="return confirm('Are you sure to Delete?')"><i style="font-size: 20px" class="fa fa-trash"></i></button>--}}
                                                    <a href="#" data-id="{{ $category->id }}" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>
                                                </form>
                                                @endif
                                            </td>
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

    <div id="confirmModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <form action="{{ route('expense_categories.store') }}" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header" style="text-align: center !important;">
                        <h2 class="modal-title" >Create Expense Category</h2>
                    </div>
                    <div class="modal-body">
                        <div class="row p-t-20">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Name</label>
                                    <input type="text" id="Name" name="Name" class="form-control" placeholder="category Name" required>
                                    @if ($errors->has('Name'))
                                        <span class="text-danger">{{ $errors->first('Name') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="ok_button" id="ok_button" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).on('click', '.insert', function(){
            //  id = $(this).attr('id');
            $('#confirmModal').modal('show');
        });

        $('#ok_button').click(function(){

            $('#ok_button').text('Inserting...');
        });
    </script>

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
                        url: "{{ URL('expense_category_delete_post') }}",
                        type: "post",
                        data: {deleteDescription:deleteDescription,row_id:_row_id},
                        success: function (result) {
                            if (result === true) {
                                window.location.href = "{{ route('expense_categories.index') }}";
                            } else {
                                alert('Something went wrong');
                                window.location.href = "{{ route('expense_categories.index') }}";
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
@endsection
