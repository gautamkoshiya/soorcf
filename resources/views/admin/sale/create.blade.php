<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="ALHAMOOD GENERAL TRANSPORT">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('admin_assets/assets/images/favicon.png') }}">
<title>Create Sales</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="{{ asset('admin_assets/assets/dist/css/style.min.css') }}" rel="stylesheet">
<link href="{{ asset('admin_assets/assets/dist/css/chosen.min.css') }}" rel="stylesheet">
<script src="{{ asset('admin_assets/assets/node_modules/jquery/jquery-3.5.1.min.js') }}"></script>
<script type="text/javascript">
    $(function() {
        $(".chosen-select").chosen();
    });
</script>
<style>
    .required {
        color: red;
    }
</style>
</head>
<body class="horizontal-nav skin-megna-dark fixed-layout">
<div class="preloader">
    <div class="loader">
        <div class="loader__figure"></div>
        <p class="loader__label">IT Molen</p>
    </div>
</div>
<div id="main-wrapper">
    <header class="topbar">
        <nav class="navbar top-navbar navbar-expand-md navbar-dark">
            <div class="navbar-header">
                <a class="navbar-brand" href="/">
                    <b>
                        <img src="{{ asset('admin_assets/assets/images/logo-icon.png') }}" alt="homepage" class="dark-logo" />
                        <img src="{{ asset('admin_assets/assets/images/logo-icon.png') }}" alt="homepage" class="light-logo" />
                    </b><span>
                         <img src="{{ asset('admin_assets/assets/images/logo-text.png') }}" alt="homepage" class="dark-logo" />
                         <img src="{{ asset('admin_assets/assets/images/logo-text.png') }}" class="light-logo" alt="homepage" /></span> </a>
            </div>
            <div class="navbar-collapse">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item d-md-none"> <a class="nav-link nav-toggler waves-effect waves-light" href="javascript:void(0)"><i class="ti-menu"></i></a></li>
                </ul>
                <ul class="navbar-nav my-lg-0">
                    <li style="font-size: larger;margin-top: 20px;color: antiquewhite;">{{ Auth::user()->name }}</li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="{{ asset('admin_assets/assets/images/users/1.jpg') }}" alt="user" class="img-circle" width="30"></a>
                        <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
                            <span class="with-arrow"><span class="bg-primary"></span></span>
                            <div class="d-flex no-block align-items-center p-15 bg-primary text-white m-b-10">
                                <div class=""><img src="{{ asset('admin_assets/assets/images/users/1.jpg') }}" alt="user" class="img-circle" width="60"></div>
                                <div class="m-l-10">
                                    <h4 class="m-b-0">{{ Auth::user()->name }}</h4>
                                    <p class=" m-b-0">{{ Auth::user()->email }}</p>
                                </div>
                            </div>
                            <a class="dropdown-item" href="javascript:void(0)"><i class="ti-user m-r-5 m-l-5"></i> My Profile</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('UserChangePassword') }}"><i class="ti-settings m-r-5 m-l-5"></i>Change Password</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                               document.getElementById('logout-form').submit();"><i class="fa fa-sign-out m-r-5 m-l-5"></i> {{ __('Logout') }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <div class="dropdown-divider"></div>
                            <div class="p-l-30 p-10"><a href="javascript:void(0)" class="btn btn-sm btn-success btn-rounded">View Profile</a></div>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <aside class="left-sidebar">
        <div class="nav-text-box align-items-center d-md-none">
            <span><img src="{{ asset('admin_assets/assets/images/logo-icon.png') }}" alt="IT Molen template"></span>
            <a class="nav-lock waves-effect waves-dark ml-auto hidden-md-down" href="javascript:void(0)"><i class="mdi mdi-toggle-switch"></i></a>
            <a class="nav-toggler waves-effect waves-dark ml-auto hidden-sm-up" href="javascript:void(0)"><i class="ti-close"></i></a>
        </div>
        <div class="scroll-sidebar">
            < x-Navigation />
        </div>
    </aside>
@section('title', 'Invoice create')
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
                    <h2 class="text-themecolor">Invoices</h2>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Invoice</li>
                        </ol>
                        <a href="{{ route('sales.index') }}" title=""><button type="button" class="btn btn-info d-lg-block m-l-15"><i class="fa fa-eye"></i> View List</button></a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="#">
                                <div class="form-body">
                                    <div class="row>">
                                        <div class="col-md-3 float-right">
                                            <p>Vehicle Status :
                                            <input type="text" id="CheckVehicle" onClick="this.setSelectionRange(0, this.value.length)" placeholder="Vehicle Status"  class="form-control">
                                            </p>
                                            <span id="vehicle_status">Already Exists</span>
                                            <br>
                                            <span id="last_filled" style="color: black;background-color: lawngreen;"></span>
                                        </div>
                                        <div class="col-md-3 float-right">
                                            <p>New Balance: <input style="color: darkmagenta;" type="text" value="0.00" id="balance" class="form-control balance" disabled>
                                                <input type="hidden" value="0.00" id="balance" class="form-control balance" tabindex="-1">
                                            </p>
                                        </div>
                                        <div class="col-md-3 float-right">
                                            <p>Previous Closing : <input style="color: red;" type="text" value="0.00" class="form-control closing" id="closing" readonly>
                                                <input type="hidden" value="0.00" class="form-control closing" tabindex="-1">
                                            </p>
                                        </div>
                                    </div>
                                    <input type="hidden" name="SaleNumber" id="SaleNumber" value="{{ $saleNo ?? "" }}">

                                    <div class="table-responsive">
                                        <table class="table color-bordered-table success-bordered-table" style="overflow: hidden;z-index: 999;height:350px;" id="scroll_table">
                                            <thead>
                                            <tr>
                                                <th style="width: 150px">Product</th>
                                                <th style="width: 100px">Date</th>
                                                <th style="width: 150px">Pad #</th>
                                                <th style="width: 200px">Customer</th>
                                                <th style="width: 150px">Vehicle</th>
                                                <th>Quantity</th>
                                                <th>Unit Price</th>
                                                <th style="width: 120px">VAT</th>
                                                <th>Amount</th>
                                            </tr>
                                            </thead>
                                            <tbody id="newRow">
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="Product_id" class="form-control product_id slct" id="product_id">
                                                            <option readonly="" disabled selected>--Product--</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}" {{ ($product->id == 1) ? 'selected':'' }}>{{ $product->Name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td><input type="date" name="createdDate" value="{{ $init_data['last_date'] ?? date('Y-m-d') }}" id="createdDate" class="form-control createdDate" placeholder=""></td>
                                                <td>
                                                    <input type="text" id="PadNumber" onClick="this.setSelectionRange(0, this.value.length)" placeholder="Pad Number" value="{{ $init_data['pad_no'] ?? "" }}" class="PadNumber form-control">
                                                    <span class="text-danger" id="already_exist">Already Exists</span>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="customer" class=" customer_id chosen-select" id="customer_id" style="z-index: 9999 !important;overflow: hidden !important;display: block;" autofocus>
                                                            <option readonly="" disabled selected>--Customer--</option>
                                                            @foreach($customers as $customer)
                                                                <option value="{{ $customer->id }}">{{ $customer->Name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <select name="vehicle" id="vehicle" class="form-control vehicle_id slct chosen-select">
                                                            <option class="opt" value="0">Vehicle</option>
                                                        </select>
                                                    </div>
                                                </td>

                                                <td hidden="">
                                                    <div class="form-group">
                                                        <select name="unit" id="unit" class="form-control unit_id">
                                                            <option class="opt" value="1">Unit</option>
                                                        </select>
                                                    </div>
                                                </td>

                                                <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)"  placeholder="Quantity" class="quantity form-control" id="cur_qty" autocomplete="off">
                                                    <input type="hidden" placeholder="Total" class="total form-control">
                                                    <input type="hidden" placeholder="Single Row Vat" value="0.00" class="singleRowVat form-control">
                                                </td>

                                                <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Price" id="Rate" class="price form-control" autocomplete="off"></td>

                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="VAT" id="VAT" class="VAT form-control">
                                                    </div>
                                                </td>

                                                <td><input type="hidden" placeholder="Total" class="rowTotal form-control">
                                                    <input type="text" placeholder="Total" class="rowTotal form-control">
                                                </td>
                                                <h3 class="required"> * Please Verify all data before submit.</h3>
                                                <span id="remaining_qty" style="color:green;font-weight:bold;font-size:x-large;"></span>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group" hidden>
                                                <textarea name="" id="description" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Note"></textarea>
                                            </div>
                                            <div class="table-responsive" style="margin-top: 20px">
                                                <table class="table color-table inverse-table">
                                                    <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th style="width: 100px">Pad #</th>
                                                        <th style="width: 210px">Customer</th>
                                                        <th style="width: 100px">Vehicle</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Price</th>
                                                        <th>Amount</th>
                                                        <th>Paid</th>
                                                        <th>Time</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($salesRecords as $records)
                                                        <tr id="rowData" style="background: #1285ff;color: white;font-size: 12px">
                                                            <td>
                                                                @if (!empty($records->sale_details[0]->createdDate))
                                                                    {{ date('d-M', strtotime($records->sale_details[0]->createdDate)) }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if (!empty($records->sale_details[0]->PadNumber))
                                                                     {{ $records->sale_details[0]->PadNumber }}
                                                                @endif
                                                            </td>
                                                            <td>{{ $records->customer->Name ?? "" }}</td>
                                                            <td>
                                                                @if (!empty($records->sale_details[0]->vehicle->registrationNumber))
                                                                    {{ $records->sale_details[0]->vehicle->registrationNumber }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if (!empty($records->sale_details[0]->Quantity))
                                                                    {{ $records->sale_details[0]->Quantity }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if (!empty($records->sale_details[0]->Price))
                                                                       {{ $records->sale_details[0]->Price }}
                                                                @endif
                                                             </td>
                                                            <td>{{ $records->grandTotal }}</td>
                                                            <td>{{ $records->paidBalance }}</td>
                                                            <td>{{ $records->updated_at->diffForHumans() }}</td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <p>Total Vat: <input type="text" value="0.00" class="form-control TotalVat" disabled="" tabindex="-1">
                                                <input type="hidden" value="0.00" class="form-control TotalVat">
                                            </p>

                                        </div>

                                        <div class="col-md-2">

                                            <p>Grand Total: <input type="text" value="0.00" class="form-control GTotal" disabled="">
                                                <input type="hidden" value="0.00" class="form-control GTotal" tabindex="-1" >
                                            </p>

                                            <p>Cash Paid: <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" class="form-control cashPaid"></p>

                                            <div class="form-actions">
                                                <p>&nbsp;</p>
                                                <button type="button" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                                <a href="{{ route('sales.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
$(document).ready(function () {
    $('#already_exist').hide();
    $('#PadNumber').keyup(function () {
        var PadNumber = 0;
        PadNumber = $('#PadNumber').val();
        if (PadNumber > 0)
        {
            var data={PadNumber:PadNumber};
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ URL('CheckPadExist') }}",
                type: "post",
                data: data,
                dataType: "json",
                success: function (result) {
                    if (result === true)
                    {
                        $('#already_exist').show();
                    }
                    else
                    {
                        $('#already_exist').hide();
                    }
                },
                error: function (errormessage) {
                    alert(errormessage);
                }
            });
        }
    });
});
</script>

<script>
    $(document).ready(function () {
        $('#vehicle_status').hide();
        $('#last_filled').hide();
        $('#CheckVehicle').keyup(function () {
            var CheckVehicle = 0;
            CheckVehicle = $('#CheckVehicle').val();

            if (CheckVehicle > 0)
            {
                var data={CheckVehicle:CheckVehicle};
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ URL('CheckVehicleStatus') }}",
                    type: "post",
                    data: data,
                    dataType: "json",
                    success: function (result) {
                        //var result=JSON.parse(result);
                        var result=JSON.parse(JSON.stringify(result));
                        if (result.result === true)
                        {
                            $('#vehicle_status').show();
                            $('#last_filled').show();
                            $('#vehicle_status').html(result.customer);
                            $('#last_filled').html(result.last_filled);
                            if(result.status==1)
                            {
                                $('#vehicle_status').css("color","green");
                                $('#last_filled').css("color","snow");
                                $('#last_filled').css("background-color","green");
                            }
                            else
                            {
                                $('#vehicle_status').css("color","red")
                                $('#last_filled').css("color","snow");
                                $('#last_filled').css("background-color","red");
                            }
                        }
                        else if(result.result === false)
                        {
                            $('#vehicle_status').show();
                            $('#vehicle_status').html('Not Found...');
                            $('#last_filled').hide();
                            $('#last_filled').html('');
                            $('#vehicle_status').css("color","red");
                        }
                        else
                        {
                            $('#vehicle_status').hide();
                        }
                    },
                    error: function (errormessage) {
                        alert(errormessage);
                    }
                });
            }

            if ($.trim($("#CheckVehicle").val()) == "") {
                $('#vehicle_status').hide();
            }
        });
    });
</script>
<script>
    window.onload = function () {
        document.getElementById('customer_id').focus();
    };

    $(document).ready(function () {
        $('html, body').animate({
            scrollTop: $('.page-titles').offset().top
        }, 'slow');
    });
</script>
<script>
    $(document).ready(function () {
        $(document).ready(function () {
            $('#submit').click(function () {
                $('#submit').text('please wait...');
                $('#submit').attr('disabled',true);
                var supplierNew = $('.customer_id').val();
                if (supplierNew != null)
                {
                    var insert = [], orderItem = [], nonArrayData = "";
                    $('#newRow tr').each(function () {
                        var currentRow = $(this).closest("tr");
                        if (validateRow(currentRow)) {
                            var quantity=currentRow.find('.quantity').val();
                            var price=currentRow.find('.price').val();
                            quantity=parseFloat(quantity).toFixed(2);
                            price=parseFloat(price);
                            orderItem =
                                {
                                    product_id: currentRow.find('.product_id').val(),
                                    unit_id: currentRow.find('.unit_id').val(),
                                    vehicle_id: currentRow.find('.vehicle_id').val(),
                                    Quantity: quantity,
                                    Price: price,
                                    rowTotal: currentRow.find('.total').val(),
                                    Vat: currentRow.find('.VAT').val(),
                                    rowVatAmount: currentRow.find('.singleRowVat').val(),
                                    rowSubTotal: currentRow.find('.rowTotal').val(),
                                    PadNumber: currentRow.find('.PadNumber').val(),
                                    createdDate: currentRow.find('.createdDate').val(),
                                };
                            insert.push(orderItem);
                        }
                        else
                        {
                            return false;
                        }

                    });
                    let details = {
                        SaleNumber: $('#SaleNumber').val(),
                        SaleDate: $('#createdDate').val(),
                        Total: $('.total').val(),
                        subTotal: $('.rowTotal').val(),
                        totalVat: $('.TotalVat').val(),
                        grandTotal: $('.GTotal').val(),
                        paidBalance: $('.cashPaid').val(),
                        remainingBalance: $('#balance').val(),
                        lastClosing: $('#closing').val(),
                        customer_id:$('#customer_id').val(),
                        customerNote:$('#description').val(),
                        orders: insert,
                    }
                    if (insert.length > 0) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        var Datas = {Data: details};
                        $.ajax({
                            url: "{{ route('sales.store') }}",
                            type: "post",
                            data: Datas,
                            success: function (result) {
                                var result=JSON.parse(result);
                                if (result.result === false) {
                                    alert(result.message);
                                    window.location.href = "{{ route('sales.create') }}";
                                } else {
                                    window.location.href = "{{ route('sales.create') }}";
                                }
                            },
                            error: function (errormessage) {
                                alert(errormessage);
                            }
                        });
                    } else
                    {
                        alert('Please Add item to list');
                        $('#submit').text('Save');
                        $('#submit').attr('disabled',false);
                    }
                }
                else
                {
                    alert('Select Customer first')
                    $('#submit').text('Save');
                    $('#submit').attr('disabled',false);
                }

            });
            //////// end of submit Records /////////////////
            //////// validate rows ////////
            function validateRow(currentRow) {
                var isvalid = true;
                var rate = 0, product = 0, quantity = 0, vehicle = $('.vehicle_id').val();
                if (parseInt(vehicle) === 0 || vehicle === ""){
                    isvalid = false;
                }
                product = currentRow.find('.product').val();
                quantity  = currentRow.find('.quantity').val();
                quantity = parseFloat(quantity).toFixed(2);
                rate = currentRow.find('.price').val();
                rate = parseFloat(rate).toFixed(2)
                if (parseInt(product) === 0 || product === ""){
                    //alert(product);
                    isvalid = false;
                }
                // if (parseFloat(quantity) == 0 || quantity == "")
                // {
                //     isvalid = false;
                // }
                if (parseFloat(rate) == 0 || rate == "")
                {
                    isvalid = false
                }
                return isvalid;
            }
            ////// end of validate row ///////////////////
            $('.customer_id').change(function () {
                var Id = 0;
                Id = $(this).val();
                if (Id > 0)
                {
                    $.ajax({
                        // headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        url: "{{ URL('salesCustomerDetails') }}/" + Id,
                        type: "get",
                        dataType: "json",
                        success: function (result) {
                            if (result !== "Failed") {
                                $('#Rate').val(result.customers[0].customer_prices[0].Rate);
                                $('#VAT').val(result.customers[0].customer_prices[0].VAT);

                                $("#vehicle").html('');
                                var vehicleDetails = '';
                                // vehicleDetails += '<option value="">' + 'Select' + '</option>';
                                if (result.customers[0].vehicles.length > 0)
                                {
                                    for (var i = 0; i < result.customers[0].vehicles.length; i++) {
                                        vehicleDetails += '<option value="' + result.customers[0].vehicles[i].id + '">' + result.customers[0].vehicles[i].registrationNumber + '</option>';
                                    }
                                }
                                else {
                                    vehicleDetails += '<option value="0">No Data</option>';
                                }
                                $("#vehicle").append(vehicleDetails);
                                $("#vehicle").trigger("chosen:updated");

                                var rate = result.customers[0].customer_prices[0].Rate;
                                var vat = result.customers[0].customer_prices[0].VAT;
                                rate=parseFloat(rate).toFixed(2)
                                vat=parseFloat(vat).toFixed(2)
                                totalWithCustomer(vat, rate);
                                $('#closing').val(result.closing);
                                $('#remaining_qty').html(result.advance_booking);
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
    ////////////// end of customer select ////////////////
    /////////// product select //////////////
    $(document).on("change", '.product_id', function () {
        var currentRow = $(this).closest('tr');
        var productId = $(this).val();
        productInfoId(productId, currentRow);
        //currentRow.find('.quantity').val('');
    });

        function productInfoId(Id, currentRow) {
        if (Id > 0)
        {
            $.ajax({
                url: "{{ URL('productsDetails') }}/" + Id,
                type: "get",
                dataType: "json",
                success: function (result) {
                    if (result !== "Failed") {
                        $("#unit").html('');
                        var unitDetails = '';
                        if (result.units.length > 0)
                        {
                            for (var i = 0; i < result.units.length; i++) {
                                unitDetails += '<option value="' + result.units[i].id + '">' + result.units[i].Name + '</option>';
                            }
                        }
                        else {
                            unitDetails += '<option value="0">No Data</option>';
                        }
                        $("#unit").append(unitDetails);
                         // currentRow.find('.unit').val(result.unit.Name);
                    } else {
                        alert(result);
                    }
                },
                error: function (errormessage) {
                    alert(errormessage);
                }
            });
        }
        CountTotalVat();
    }
    ////////////////////////// end of products select //////////
    });
</script>
<script>
    $( "#customer_id" ).change(function() {
        var customer_name=$( "#customer_id option:selected" ).text();

        if(customer_name==='CASH')
        {
            $('.quantity').focus();
        }
    });
</script>
<script>
    $( "#cur_qty" ).focusin(function() {
        $(this).css({"background-color": "orange"});
    });
    $( "#cur_qty" ).focusout(function() {
        $(this).css({"background-color": "white"});
    });
</script>
    <script>
        $(document).on("keypress",'.quantity', function (event) {
            return isNumber(event, this)
        });
        $(document).on("keypress",'.price', function (event) {
            return isNumber(event, this)
        });

        $(document).on("keypress",'.total', function (event) {
            return isNumber(event, this)
        });

        $(document).on("keypress",'.cashPaid', function (event) {
            return isNumber(event, this)
        });

        ////////////////// accept number function ////////////////
        function isNumber(evt, element) {
            var charCode = (evt.which) ? evt.which : event.keyCode
            if (
                (charCode !== 46 || $(element).val().indexOf('.') !== -1) &&      // “.” CHECK DOT, AND ONLY ONE.
                (charCode < 48 || charCode > 57))
                return false;
            return true;
        }
        //////////////// end of accept number function //////////////

        //////////////////////// Add price ///////////
        $(document).on("keyup",'.price', function () {
            var Currentrow = $(this).closest("tr");
            var QTY = Currentrow.find('.quantity').val();
            if (parseFloat(QTY) >= 0.0)
            {
                var Total = parseFloat(QTY) * parseFloat(Currentrow.find('.price').val());
                //alert(Total);
                Total=roundToTwo(Total);
                Currentrow.find('.total').val(Total);
            }
            var vat = Currentrow.find('.VAT').val();
            vat=roundToTwo(vat);
            RowSubTalSubtotal(vat, Currentrow);
            CountTotalVat();
            ApplyCashPaid();
            apply_closing();
        });
        ////////// end of add price /////////////////

        //////////////////////// Add quantity ///////////
        $(document).on("keyup",'.quantity', function () {
            var Currentrow = $(this).closest("tr");
            var QTY = $(this).val();
            if (parseFloat(QTY) >= 0)
            {
                var Total = parseFloat(QTY) * parseFloat(Currentrow.find('.price').val());
                Total=roundToTwo(Total);
                //alert(Total);
                Currentrow.find('.total').val(Total);
            }
            var vat = Currentrow.find('.VAT').val();
            vat=roundToTwo(vat);
            RowSubTalSubtotal(vat, Currentrow);
            CountTotalVat();
            ApplyCashPaid();
            apply_closing();
        });
        ///////// end of add quantity ///////////////////



        //////////////////////// Add quantity ///////////
        $(document).on("keyup",'.total', function () {
            var Currentrow = $(this).closest("tr");
            var tl = $(this).val();
            Currentrow.find('.total').val(tl);
            var vat = Currentrow.find('.VAT').val();
            vat=roundToTwo(vat);
            RowSubTalSubtotal(vat, Currentrow);
            CountTotalVat();
            apply_closing();
        });
        ///////// end of add quantity ///////////////////

        /////// vat //////////////////
        $(document).on("change", '.VAT', function () {
            var CurrentRow = $(this).closest("tr");
            var vat = CurrentRow.find('.VAT').val();
            vat=roundToTwo(vat);
            RowSubTalSubtotal(vat, CurrentRow);
            CountTotalVat();
            apply_closing();
        });
        ////////////// end of vat /////////////////

        /////// vat //////////////////
        $(document).on("keyup", '.VAT', function () {
            var CurrentRow = $(this).closest("tr");
            var vat = CurrentRow.find('.VAT').val();
            vat=roundToTwo(vat);
            RowSubTalSubtotal(vat, CurrentRow);
            CountTotalVat();

        });
        ////////////// end of vat /////////////////

        ///// row Sub Total ///////////////////////
        function RowSubTalSubtotal(vat, CurrentRow) {
            Total = 0;
            Total = CurrentRow.find('.total').val();
            if (parseInt(vat) === 0 && typeof (vat) != "undefined" && vat !== ""){
                if (!isNaN(Total) && typeof (Total) != "undefined")
                {
                    CurrentRow.find('.rowTotal').val(parseFloat(Total).toFixed(2));
                    //CurrentRow.find('.rowTotal').val(Total);
                    //CurrentRow.find('.rowTotal').val(parseFloat(Total).toFixed(2))
                    return;
                }
            }

            if (!isNaN(Total) && Total !== "" && typeof (vat) != "undefined")
            {
                var InputVatValue = parseFloat((Total / 100) * vat);
                var ValueWTV = parseFloat(InputVatValue) + parseFloat(Total);
                // if (!isNaN(ValueWTV))
                // {
                //     CurrentRow.find('.rowTotal').val(parseFloat(ValueWTV).toFixed(2));
                // }
                CurrentRow.find('.rowTotal').val(parseFloat(ValueWTV).toFixed(2));
                CurrentRow.find('.singleRowVat').val(parseFloat(InputVatValue).toFixed(2));
            }
        }
        /////////////// end of row sub total ///////////////////////////


        //////////// total vat /////////////////
        function CountTotalVat() {
            var TotalVat = 0;
            var Gtotal = 0;
            var ToatWTVAT = 0;

            $('#newRow tr').each(function () {
                if ($(this).find(".rowTotal").val().trim() != ""){
                    Gtotal = parseFloat(Gtotal) + parseFloat($(this).find(".rowTotal").val());
                    //alert(Gtotal);
                }
                else {
                    Gtotal = parseFloat(Gtotal);
                }
                if ($(this).find(".total").val().trim() != ""){
                    ToatWTVAT = parseFloat(ToatWTVAT) + parseFloat($(this).find(".total").val());
                    ToatWTVAT = roundToTwo(ToatWTVAT);
                    //alert(ToatWTVAT);
                }
                else {
                    ToatWTVAT = parseFloat(ToatWTVAT);
                    ToatWTVAT = roundToTwo(ToatWTVAT);
                }
                TotalVat = parseFloat(Gtotal) - parseFloat(ToatWTVAT);
                TotalVat = roundToTwo(TotalVat);
                // alert(TotalVat);
            });

            if (!isNaN(TotalVat)){
                $('#TotalVat').text(TotalVat.toFixed(2));
                $('.TotalVat').val(TotalVat.toFixed(2));
            }

            if (!isNaN(ToatWTVAT)){
                $('#SubTotal').text(ToatWTVAT.toFixed(2));
                $('.SubTotal').val(ToatWTVAT.toFixed(2));
            }

            $('#GTotal').text((Gtotal.toFixed(2)));
            $('.GTotal').val((Gtotal.toFixed(2)));

        }
        //////////////// end of total vat /////////////

        /////////////// cash paid ////////////////////
        function ApplyCashPaid() {
            var customer = $("#customer_id option:selected").text();
            if(customer=='cash' || customer=='CASH')
            {
                var GTotal = $('.GTotal').val();
                $('.cashPaid').val(GTotal);
            }
            else
            {
                var GTotal = 0.00;
                $('.cashPaid').val(GTotal);
                $('.cashPaid').prop('readonly', true);
            }
        }
        /////////////// end of cash paid ////////////////////

        $(document).on("keyup",'.cashPaid',function () {
            var GTotal = $('.GTotal').val();
            var Input = parseFloat(GTotal + closing - $('.cashPaid').val());
            //var Value = parseFloat(Input) + parseFloat(GTotal);
            var rr= $('.balance').val((Input.toFixed(2)));
            apply_closing();
        });


        function totalWithCustomer(vat, rate)
        {
            //var Currentrow = $(this).closest("tr");
            var QTY = $('.quantity').val();
            if (parseInt(QTY) >= 0)
            {
                var Total = parseInt(QTY) * parseFloat(rate);
                //alert(Total);
                $('.total').val(Total);
            }

            Total = 0;
            Total = $('.total').val();
            //alert(Total);

            var InputVatValue = parseFloat((Total / 100) * vat);
            var ValueWTV = parseFloat(InputVatValue) + parseFloat(Total);
            $('.rowTotal').val(parseFloat(ValueWTV).toFixed(2));
            $('.singleRowVat').val(parseFloat(InputVatValue).toFixed(2));

            CountTotalVat();
        }

        function roundToTwo(num) {
            return +(Math.round(num + "e+2")  + "e-2");
        }

        function apply_closing()
        {
            // remaining balance = grand total + account closing - cash paid
            var grand_total = $('.GTotal').val();
            grand_total=parseFloat(grand_total).toFixed(2);
            grand_total=roundToTwo(grand_total);

            var closing = $('#closing').val();
            closing=parseFloat(closing).toFixed(2);
            closing=roundToTwo(closing);

            var cash_paid = $('.cashPaid').val();
            cash_paid=parseFloat(cash_paid).toFixed(2);
            cash_paid=roundToTwo(cash_paid);

            var remaining_balance=grand_total+closing-cash_paid;
            $('.balance').val((remaining_balance.toFixed(2)));
            var customer = $("#customer_id option:selected").text();

            if(cash_paid>grand_total)
            {
                $('.cashPaid').val((grand_total.toFixed(2)));
            }

            if(remaining_balance<0 && customer!=='cash' || customer!=='CASH')
            {
                //$('.cashPaid').attr('readonly', true);
            }
            else
            {
                $('.cashPaid').attr('readonly', false);
            }
            if(customer==='cash' || customer==='CASH')
            {
                $('.cashPaid').attr('readonly', true);
            }
        }


    </script>
{{--<script src="{{ asset('admin_assets/assets/dist/invoice/invoice.js') }}"></script>--}}
<footer class="footer">
    Powered by <a href="https://itmolen.nl/">IT Molen</a> | © A Product of wahid group of companies
</footer>
</div>
<script src="{{ asset('admin_assets/assets/node_modules/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('admin_assets/assets/dist/js/perfect-scrollbar.jquery.min.js') }}"></script>
<script src="{{ asset('admin_assets/assets/dist/js/sidebarmenu.js') }}"></script>
<script src="{{ asset('admin_assets/assets/dist/js/custom.min.js') }}"></script>
<script src="{{ asset('admin_assets/assets/dist/js/chosen.jquery.min.js') }}" type="text/javascript"></script>
</body>
</html>
