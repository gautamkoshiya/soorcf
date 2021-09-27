@extends('shared.layout-admin')
@section('title', 'Report File Types List')

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
                            <li class="breadcrumb-item active">Report File Types</li>
                        </ol>
                       <a href="{{ route('report_file_types.create') }}"><button type="button" class="btn btn-info d-lg-block m-l-15 insert"><i class="fa fa-plus-circle"></i> Create New</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Report File Types</h4>
                            <div class="table-responsive m-t-40">
                                <table id="report_file_types" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th width="100">Action</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($types as $type)
                                        <tr>
                                            <td>{{ $type->Name }}</td>
                                            <td>
                                                <form action="{{ route('report_file_types.destroy',$type->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="{{ route('report_file_types.edit', $type->id) }}"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>
                                                    <button type="submit" class=" btn btn-danger btn-sm" onclick="return confirm('Are you sure to Delete?')"><i style="font-size: 20px" class="fa fa-trash"></i></button>
                                                </form>
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
    <script>
        $(document).on('click', '.insert', function(){
            $('#confirmModal').modal('show');
        });
        $('#ok_button').click(function(){

            $('#ok_button').text('Inserting...');
        });
    </script>
@endsection
