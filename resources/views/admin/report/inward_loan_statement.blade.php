@extends('shared.layout-admin')
@section('title', 'Loan Statement')

@section('content')

    <style>
        .slct:focus{
            background: #aed9f6;
        }
    </style>
    <style>
        .chosen-container-single .chosen-single {
            height: 38px;
            border-radius: 3px;
            border: 1px solid #CCCCCC;
        }
        .chosen-container-single .chosen-single span {
            padding-top: 5px;
        }
        .chosen-container-single .chosen-single div b {
            margin-top: 5px;
        }
        .chosen-container-active .chosen-single,
        .chosen-container-active.chosen-with-drop .chosen-single {
            border-color: #ccc;
            border-color: rgba(82, 168, 236, .8);
            outline: 0;
            outline: thin dotted \9;
            -moz-box-shadow: 0 0 8px rgba(82, 168, 236, .6);
            box-shadow: 0 0 8px rgba(82, 168, 236, .6)
        }
    </style>

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">Inward Loan Statement</li>
                        </ol>
                       </div>
                </div>
            </div>

            <h2>Inward Loan Statement</h2>
            <h6 class="required">* Fields are required please don't leave blank</h6>

            <form id="report_form" method="post" action="{{ route('ViewDetailCustomerStatement') }}" enctype="multipart/form-data">
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
                        <label>Financer :- <span class="required">*</span></label>
                        <select class="form-control supplier-select financer_id chosen-select" name="financer_id" id="financer_id">
                            @foreach($financers as $financer)
                                @if(!empty($financer->Name))
                                    <option value="{{ $financer->id }}">{{ $financer->Name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <input type="hidden" id="financer_name" name="financer_name">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2" style="display: none">
                    <div class="form-group">
                        <button class="btn btn-info" type="submit"><i class="fa fa-plus-circle"></i> View Loan Statement</button>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button id="submit" type="button" class="btn btn-info"><i class="fa fa-plus-circle"></i> Get Loan Statement</button></a>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>

    <script>
        $( document ).ready(function() {
            var financer_name=$( "#financer_id option:selected" ).text();
            $('#financer_name').val(financer_name);
        });
        $( "#financer_id" ).change(function() {
            var financer_name=$( "#financer_id option:selected" ).text();
            $('#financer_name').val(financer_name);
        });
        function get_pdf()
        {
            $('#submit').text('please wait...');
            $('#submit').attr('disabled',true);
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            var financer_id = $('#financer_id').val();
            var financer_name=$( "#financer_id option:selected" ).text();
            $.ajax({
                url: "{{ URL('PrintInwardLoanStatement') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate,financer_id:financer_id,financer_name:financer_name},
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit').text('Get Loan Statement');
                    $('#submit').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit').text('Get Loan Statement');
                    $('#submit').attr('disabled',false);
                }
            });
        }
    </script>
@endsection
