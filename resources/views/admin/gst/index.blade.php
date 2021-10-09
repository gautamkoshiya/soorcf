@extends('shared.layout-admin')
@section('title', 'GST List')

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
                            <li class="breadcrumb-item active">GST</li>
                        </ol>
                       <a href="{{ route('gsts.create') }}"><button type="button" class="btn btn-info d-lg-block m-l-15 insert"><i class="fa fa-plus-circle"></i> Create New</button></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">GST</h4>
                            <h6 class="card-subtitle">All GST</h6>
                            <div class="table-responsive m-t-40">
                                <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>GST Name</th>
                                        <th>Percentage</th>
                                        <th>Combined</th>
                                        <th width="100">Action</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($gsts as $single)
                                        <tr>
                                            <td>{{ $single->Name ?? "N.A." }}</td>
                                            <td>{{ $single->percentage }}</td>
                                            <td>{{ $single->IsCombined }}</td>
                                            <td>
                                                <form action="{{ route('gsts.destroy',$single->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="{{ route('gsts.edit', $single->id) }}"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>
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
