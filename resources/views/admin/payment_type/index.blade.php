@extends('shared.layout-admin')
@section('title', 'Payment Type')

@section('content')

    <!-- ============================================================== -->
    <!-- Page wrapper  -->
    <!-- ============================================================== -->
    <div class="page-wrapper">
        <!-- ============================================================== -->
        <!-- Container fluid  -->
        <!-- ============================================================== -->
        <div class="container-fluid">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <!-- <h4 class="text-themecolor">diensten</h4> -->
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">Payment Type</li>
                        </ol>
                        <a href="{{ route('payment_types.create') }}"><button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> create new</button></a>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Start Page Content -->
            <!-- ============================================================== -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Payment Type</h4>
                            <h6 class="card-subtitle">All Payment Types</h6>
                            <div class="table-responsive m-t-40">
                                <table id="payment_types_table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th width="100">Is Active</th>
                                       {{--  <th>Is Active</th> --}}
                                        <th width="100">Action</th>
                                    </tr>
                                    </thead>

                                  {{--   <tbody>

                                    @foreach($roles as $role)
                                        <tr>
                                            <td>{{ $role->Name }}</td>
                                            <td>
                                                <form action="{{ route('roles.destroy',$role->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="{{ route('roles.edit', $role->id) }}"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>
                                                    <button type="submit" class=" btn btn-danger btn-sm" onclick="return confirm('Are you sure to Delete?')"><i style="font-size: 20px" class="fa fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>

                                    @endforeach
                                    </tbody> --}}
                                </table>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Container fluid  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Page wrapper  -->
    <!-- ============================================================== -->

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

   {{--  <div class="switchery-demo m-b-30">
    <input type="checkbox" checked="" class="js-switch" data-color="#009efb" data-switchery="true" style="display: none;"></div> --}}
    <script>
        $(document).ready(function () {
            $('#payment_types_table').dataTable({
                processing: true,
                ServerSide: true,
                ajax:{
                    url: "{{ route('payment_types.index') }}",
                },
                columns:[
                    {
                        data: 'Name',
                        name: 'Name'
                    },
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
                    // {
                    //     name: '',
                    //     data: null,
                    //     sortable: false,
                    //     searchable: false,
                    //     render: function (data) {
                    //         var actions = '';
                    //         actions += '<a href="/transaksi-masuk/tambah/:id"><span class="label label-primary">TAMBAH</span></a>';
                    //         actions += '<a href="/transaksi-masuk/edit/:id"><span class="label label-warning">EDIT</span></a>';
                    //         return actions.replace(/:id/g, data.id_produk);
                    //     }
                    // }
                ]
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
