@extends('shared.layout-admin')
@section('title', 'Bank Report')

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
                            <li class="breadcrumb-item active">Bank Report</li>
                        </ol>
                       </div>
                </div>
            </div>

            @if (Session::has('error'))
                <div class="alert alert-danger">
                    <ul>
                        <li>{!! Session::get('error') !!}</li>
                        {{Session::forget('error')}}
                    </ul>
                </div>
            @endif

            <h2 class="card-title">Bank Report</h2>
            <h6 class="required">* Fields are required please don't leave blank</h6>
            <form id="report_form" method="post" action="{{ route('ViewBankReport') }}" enctype="multipart/form-data">
                @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">From date :- <span class="required">*</span></label>
                        <input type="date" value="{{ date('Y-m-d') }}" id="fromDate" name="fromDate" class="form-control" placeholder="dd/mm/yyyy" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">To date :- <span class="required">*</span></label>
                        <input type="date" value="{{ date('Y-m-d') }}" id="toDate" name="toDate" class="form-control" placeholder="dd/mm/yyyy" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Bank :- <span class="required">*</span></label>
                        <select class="form-control supplier-select bank_id" name="bank_id" id="bank_id">
                            @foreach($banks as $bank)
                                @if(!empty($bank->Name))
                                    <option value="{{ $bank->id }}">{{ $bank->Name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <input type="hidden" id="bank_name" name="bank_name">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <button class="btn btn-info" type="submit"><i class="fa fa-plus-circle"></i> View Bank Report</button>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button id="submit" type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i> Get Bank Report</button></a>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>

    <script>
        $( document ).ready(function() {
            var bank_name=$( "#bank_id option:selected" ).text();
            $('#bank_name').val(bank_name);
        });
        $( "#bank_id" ).change(function() {
            var bank_name=$( "#bank_id option:selected" ).text();
            $('#bank_name').val(bank_name);
        });
        function get_pdf()
        {
            $('#submit').text('please wait...');
            $('#submit').attr('disabled',true);
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            var bank_id = $('#bank_id').val();
            var bank_name=$( "#bank_id option:selected" ).text();
            $.ajax({
                url: "{{ URL('PrintBankReport') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate,bank_id:bank_id,bank_name:bank_name},
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit').text('Get Bank Report');
                    $('#submit').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit').text('Get Bank Report');
                    $('#submit').attr('disabled',false);
                }
            });
        }
    </script>
@endsection
