@extends('shared.layout-admin')
@section('title', 'Supplier Summary')

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
                            <li class="breadcrumb-item active">Supplier Advance Summary</li>
                        </ol>
                       </div>
                </div>
            </div>

            <div class="row">
                <h2 class="card-title">Supplier Advance Summary</h2>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button id="submit" type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i> Get Supplier Advance Summary</button></a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function get_pdf()
        {
            $('#submit').text('please wait...');
            $('#submit').attr('disabled',true);
            $.ajax({
                url: "{{ URL('PrintPaidAdvancesSummary') }}",
                type: "get",
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit').text('Get Supplier Advance Summary');
                    $('#submit').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit').text('Get Supplier Advance Summary');
                    $('#submit').attr('disabled',false);
                }
            });
        }
    </script>
@endsection
