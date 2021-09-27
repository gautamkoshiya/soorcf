@extends('shared.layout-admin')
@section('title', 'Employees')

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
                        <li class="breadcrumb-item active">employee</li>
                    </ol>
                    <a href="{{ route('employees.create') }}"><button type="button" class="btn btn-info d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> create new</button></a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Employees</h2>
                        <div class="table-responsive m-t-40">
                            <table id="employees_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th>ID#</th>
                                    <th>Name</th>
{{--                                    <th>Mobile</th>--}}
{{--                                    <th>Passport Number</th>--}}
{{--                                    <th>Address</th>--}}
                                    <th>Status</th>
                                    <th>Action</th>
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
                <h5 class="modal-title" id="exampleModalLabel">Employee Detail</h5>
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
<div id="confirmModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="text-align: center !important;">
                <h2 class="modal-title" >Confirmation</h2>
            </div>
            <div class="modal-body">
                <h4 align="center" style="margin:0;">Are you sure you want to remove this data?</h4>
            </div>
            <div class="modal-footer">
                <button type="submit" name="ok_button" id="ok_button" class="btn btn-danger">OK</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
function change_status(e)
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
            url: "{{ URL('ChangeEmployeeStatus') }}/"+id,
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
                url: "{{ URL('getEmployeeDetail') }}/"+id,
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

<script>
    $(document).ready(function () {
        $('#employees_table').dataTable({
            processing: true,
            ServerSide: true,
            ajax:{
                url: "{{ route('employees.index') }}",
            },
            columns:[
                {
                    data: 'id',
                    name: 'id',
                    visible: false,
                },
                {
                    data: 'Name',
                    name: 'Name'
                },
                // {
                //     data: 'Mobile',
                //     name: 'Mobile'
                // },
                // {
                //     data: 'passportNumber',
                //     name: 'passportNumber'
                // },
                // {
                //     data: 'Address',
                //     name: 'Address'
                // },
                {
                    data: 'isActive',
                    name: 'isActive',
                    orderable: false
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
</script>
<script>
function ConfirmDelete()
{
    var result = confirm("Are you sure you want to delete?");
    if (result)
    {
        document.getElementById("deleteData").submit();
    }
}
</script>
@endsection
