@extends('shared.layout-admin')
@section('title', 'Generate Salary')

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
                <div class="col-md-8 align-self-center">
                    <h4 class="text-themecolor">Generate Salary</h4>
                    <h3 class="required"> * Select Entries Carefully after saving Update is not allowed.</h3>
                </div>
                <div class="col-md-4 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Generate Salary</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">
                                    <h3 class="card-title">Generate Salary</h3>
                                    <h6 class="required">* Fields are required please don't leave blank</h6>
                                    <div class="row">
                                        <label class="mt-2">Select Company :- <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <div class="form-group">
                                                <select class="form-control custom-select select2 company_id chosen-select" name="company_id" id="company_id">
                                                    <option value=""> ---- Select Company ---- </option>
                                                    @foreach($companies as $single)
                                                        <option value="{{ $single->id }}">{{ $single->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1 all">
                                            <input type="checkbox" class="form-control" name="chk[]" value="0" id="selectall"><span style="margin-left: 20px;">Select All</span>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table color-bordered-table success-bordered-table">
                                            <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Basic</th>
                                                <th width="70">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody id="sales" style="font-size: 12px">
                                            <tr>
                                                <td colspan="7" align="center" style="font-size: 16px !important;"> Please select customer for sale records</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row advance_reminder">

                                    </div>

                                    <div class="row">
                                        <div class="col-md-2 mt-2 pl-5">
                                            <div class="form-group">
                                                <label class="control-label">Total Amount :- </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="text" class="form-control totalSaleAmount" onClick="this.setSelectionRange(0, this.value.length)"  name="" id="" placeholder="Total Amount" disabled>
                                                <input type="hidden" class="form-control totalSaleAmount net_payable_amount" onClick="this.setSelectionRange(0, this.value.length)"  name="" id="price" placeholder="Total Amount">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2 mt-2 pl-5">
                                            <div class="form-group">
                                                <label class="control-label">Select Month :- <span class="required">*</span></label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="month" id="month" name="month" class="form-control month" required>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                    <a href="{{ route('salaries.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script type="text/javascript">
    jQuery(function($)
    {
        $('body').on('click', '#selectall', function() {
            $('.singlechkbox').prop('checked', this.checked);

            var totalPrice   = 0,
                values       = [];
            $('input[type=checkbox]').each( function() {
                if( $(this).is(':checked') ) {
                    values.push($(this).val());
                    totalPrice += parseFloat($(this).val());
                }
            });
            $(".totalSaleAmount").val(parseFloat(totalPrice).toFixed(2));
        });

        $('body').on('click', '.singlechkbox', function() {
            if($('.singlechkbox').length == $('.singlechkbox:checked').length) {
                $('#selectall').prop('checked', true);
                var totalPrice   = 0,
                    values       = [];
                $('input[type=checkbox]').each( function() {
                    if( $(this).is(':checked') ) {
                        values.push($(this).val());
                        totalPrice += parseFloat($(this).val());
                    }
                });
                $(".totalSaleAmount").val(parseFloat(totalPrice).toFixed(2));

            } else {
                $("#selectall").prop('checked', false);
                var totalPrice   = 0,
                    values       = [];
                $('input[type=checkbox]').each( function() {
                    if( $(this).is(':checked') ) {
                        values.push($(this).val());
                        totalPrice += parseFloat($(this).val());
                    }
                });
                $(".totalSaleAmount").val(parseFloat(totalPrice).toFixed(2));
            }
        });
    });
</script>
<script>
    $(document).ready(function (){
        $('.company_id').change(function () {
            var Id = 0;
            Id = $(this).val();
            if (Id > 0)
            {
                $.ajax({
                    url: "{{ URL('getCompanyEmployee') }}/" + Id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        if (result !== "Failed")
                        {
                            if(result.account_closing>0)
                            {
                                $(".advance_reminder").html('');
                                var advance_reminder='<h3 class="required">* THERE IS ('+result.account_closing+') AMOUNT WHICH NEEDS TO DISBURSE WITH THIS CUSTOMER *</h3>';
                                $(".advance_reminder").append(advance_reminder);
                            }
                            else
                            {
                                $(".advance_reminder").html('');
                            }
                            $("#sales").html('');
                            var salesDetails = '';
                            if (result.employees.length > 0)
                            {
                                for (var i = 0; i < result.employees.length; i++)
                                {
                                    salesDetails += '<tr>';
                                    salesDetails += '<td>' + result.employees[i].Name + '</td>';
                                    salesDetails += '<td>' + result.employees[i].Basic + '<input type="hidden" class="employee_id" name="employee_id" value="' + result.employees[i].id + '"/></td>';
                                    var value = result.employees[i].Basic;
                                    salesDetails += '<td><input type="checkbox" class="singlechkbox my_checkbox" name="username" value="' + value + '"/> </td>';
                                }
                            }
                            else {
                                salesDetails += '<td value="0" align="center" style="font-size: 16px" colspan="7">No Data</td>';
                                salesDetails += '</tr>';
                            }
                            $("#sales").append(salesDetails);
                        } else {
                            alert(result);
                        }
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            }
        });
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

        if (DoTrim(document.getElementById('month').value).length == 0)
        {
            if(fields != 1)
            {
                document.getElementById("month").focus();
            }
            fields = '1';
            $("#month").addClass("error");
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
        $('#submit').click(function (event)
        {
            if(validateForm())
            {
                $('#submit').text('please wait...');
                $('#submit').attr('disabled', true);

                var insert = [], chekedValue = [];
                $('.singlechkbox:checked').each(function ()
                {
                    var currentRow = $(this).closest("tr");
                    chekedValue =
                    {
                        salary_amount: currentRow.find('.singlechkbox').val(),
                        employee_id: currentRow.find('.employee_id').val(),
                    };
                    insert.push(chekedValue);
                })

                let details = {
                    'company_id': $('#company_id').val(),
                    'totalAmount': $('.totalSaleAmount').val(),
                    'month': $('#month').val(),
                    orders: insert,
                };
                if (insert.length > 0)
                {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var Data = {Data: details};
                    $.ajax({
                        url: "{{ route('salaries.store') }}",
                        type: "post",
                        data: Data,
                        success: function (result)
                        {
                            var result=JSON.parse(result);
                            if (result.result !== false)
                            {
                                alert(result.message);
                                window.location.href = "{{ route('salaries.index') }}";
                            }
                            else
                            {
                                alert(result.message);
                            }
                        },
                        error: function (errormessage)
                        {
                            alert(errormessage);
                        }
                    });
                }
                else
                {
                    alert('Please Add item to list');
                    $('#submit').text('Save');
                    $('#submit').attr('disabled', false);
                }
            }
            else
            {
                alert('Please Enter Required Data...');
            }
        });
    });
</script>
<script src="{{ asset('admin_assets/assets/dist/custom/custom.js') }}" type="text/javascript" charset="utf-8" async defer></script>
@endsection
