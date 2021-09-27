@extends('shared.layout-admin')
@section('title', 'Vault Report by Company')

@section('content')

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
                            <li class="breadcrumb-item active">Vault Report by Company</li>
                        </ol>
                       </div>
                </div>
            </div>

            <h3 class="card-title">Vault Report by Company</h3>
            <h6 class="required">* Fields are required please don't leave blank</h6>
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
                        <label>Company :- <span class="required">*</span></label>
                        <select class="form-control custom-select company_id chosen-select" name="company_id" id="company_id">
                            <option value="0" selected>All Company</option>
                            @foreach($companies as $company)
                                @if(!empty($company->Name))
                                    <option value="{{ $company->id }}">{{ $company->Name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="control-label">Amount :-</label>
                    <input type="text" class="form-control" id="amount" disabled>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <a href="javascript:void(0)" onclick="return get_pdf()"><button id="submit" type="button" class="btn btn-info "><i class="fa fa-plus-circle"></i> Get Report</button></a>
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
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            var company_id = $('#company_id').val();
            var company_name=$( "#company_id option:selected" ).text();
            $.ajax({
                url: "{{ URL('PrintVaultReportByCompany') }}",
                type: "POST",
                dataType : "json",
                data : {"_token": "{{ csrf_token() }}",fromDate:fromDate,toDate:toDate,company_id:company_id,company_name:company_name},
                success: function (result) {
                    window.open(result.url,'_blank');
                    $('#submit').text('Get Report');
                    $('#submit').attr('disabled',false);
                },
                error: function (errormessage) {
                    alert('No Data Found');
                    $('#submit').text('Get Report');
                    $('#submit').attr('disabled',false);
                }
            });
        }
    </script>
    <script>
        $(document).ready(function () {
            $('.company_id').change(function () {
                Id = $(this).val();
                $.ajax({
                    url: "{{ URL('getClosingVault') }}/" + Id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        $('#amount').val(result);
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            });
        });

        $(document).ready(function () {
            var Id = $('#company_id').val();
            $.ajax({
                url: "{{ URL('getClosingVault') }}/" + Id,
                type: "get",
                dataType: "json",
                success: function (result) {
                    $('#amount').val(result);
                },
                error: function (errormessage) {
                    alert(errormessage);
                }
            });
        });
    </script>
@endsection
