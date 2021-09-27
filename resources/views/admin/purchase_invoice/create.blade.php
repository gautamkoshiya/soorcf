@extends('shared.layout-admin')
@section('title', 'Create Purchase Invoice ')
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
            <h2 class="text-themecolor">Purchase Invoice</h2>
        </div>
        <div class="col-md-7 align-self-center text-right">
            <div class="d-flex justify-content-end align-items-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                    <li class="breadcrumb-item active">Purchase Invoice</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row">
        <h3 class="required"> * Please Verify all data before submit.</h3>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="#">
                        <div class="form-body">
                            <div class="row p-t-20">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Supplier :- <span class="required">*</span></label>
                                        <select class="form-control custom-select supplier_id select2 chosen-select" name="supplier_id" id="supplier_id" required>
                                            <option value="">--Select Supplier--</option>
                                            @foreach($supplier as $single)
                                                <option value="{{ $single->id }}">{{ $single->Name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Project :- <span class="required">*</span></label>
                                        <select class="form-control custom-select project_id select2 chosen-select" name="project_id" id="project_id" required>
                                            <option value="">--Select Project--</option>
                                            @foreach($projects as $project)
                                                <option value="{{ $project->id }}">{{ $project->Name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="feeds p-b-20">
                                        <li>Address <span class="text-muted" id="Address">No Address</span></li>
                                        <li>Mobile <span class="text-muted" id="Mobile">No Mobile</span></li>
                                        <li>Email <span class="text-muted" id="Email">No Email</span></li>
                                        <li>TRN<span class="text-muted" id="TRN"></span></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Invoice Number</label>
                                                <input type="text" class="form-control InvoiceNumber" name="InvoiceNumber" id="InvoiceNumber" value="{{ $InvoiceNumber }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Reference Number</label>
                                                <input type="text" class="form-control ReferenceNumber" name="ReferenceNumber" id="ReferenceNumber">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">From date :- <span class="required">*</span></label>
                                                <input type="date" name="FromDate" id="FromDate" class="form-control" value="{{ date('Y-m-d') }}" placeholder="dd/mm/yyyy">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Due date :- <span class="required">*</span></label>
                                                <input type="date" name="DueDate" id="DueDate" class="form-control" value="{{ date('Y-m-d') }}" placeholder="dd/mm/yyyy">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <h4 class="required" style="float: right;"> * Don't Add New Row Unless You Need It *</h4>
                            <div class="table-responsive">
                                <table class="table color-bordered-table success-bordered-table" style="overflow: hidden;z-index: 999;height:350px;" id="scroll_table">
                                    <thead>
                                    <tr>
                                        <th>Product <span class="required">*</span></th>
                                        <th>Description</th>
                                        <th>UNIT <span class="required">*</span></th>
                                        <th>Quantity <span class="required">*</span></th>
                                        <th>Price <span class="required">*</span></th>
                                        <th>Total</th>
                                        <th>VAT <span class="required">*</span></th>
                                        <th>Subtotal</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody id="newRow">
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <select name="Product_id" class="form-control product_id" id="product_id">
                                                    <option value="" selected>Product</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>

                                        <td>
                                            <input type="text" name="Description" id="Description" placeholder="Description" class="Description form-control">
                                        </td>

                                        <td>
                                            <div class="form-group">
                                                <select name="unit_id" class="form-control unit_id" id="unit_id">
                                                    <option value="" selected>Unit</option>
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit->id }}">{{ $unit->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>

                                        <td><input type="number" placeholder="Quantity" class="quantity form-control" id="cur_qty" value="0" autocomplete="off">
                                            <input type="hidden" placeholder="Total" class="total form-control">
                                            <input type="hidden" placeholder="Single Row Vat" value="0.00" class="singleRowVat form-control">
                                        </td>

                                        <td><input type="number" placeholder="Price" id="Price" value="0" class="price form-control" autocomplete="off"></td>

                                        <td>
                                            <div class="form-group">
                                                <input type="text" id="rowTotal" class="rowTotal form-control" readonly>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="form-group">
                                                <input type="number" placeholder="VAT" value="5" id="VAT" class="VAT form-control">
                                                <input type="hidden" class="hidden_vat" value="0">
                                            </div>
                                        </td>

                                        <td><input type="hidden" placeholder="subtotal" class="rowSubTotal form-control" >
                                            <input type="text" class="rowSubTotal form-control" readonly>
                                        </td>

                                        <td><input class=" btn btn-success addRow" id="addRow" type="button" value="+" /></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Terms & Condition :- </label>
                                            <div class="form-group">
                                                <textarea name="TermsAndCondition" id="TermsAndCondition" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Terms And Condition"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Supplier Note :- </label>
                                            <div class="form-group">
                                                <textarea name="CustomerNote" id="CustomerNote" cols="30" rows="5" class="form-control" style="width: 100%" placeholder="Customer Note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Need Stamp and signature ? :- </label>
                                            <div class="form-group">
                                                <select name="IsNeedStampOrSignature" class="form-control IsNeedStampOrSignature" id="IsNeedStampOrSignature">
                                                    <option value="0" selected>No</option>
                                                    <option value="1">YES</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <p>Subtotal : <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" class="form-control cashPaid" readonly></p>
                                    <p>Total Vat: <input type="text" value="0.00" class="form-control TotalVat" disabled="" tabindex="-1">
                                    <input type="hidden" value="0.00" class="form-control TotalVat"></p>
                                    <p>Discount(AED): <input type="number" value="0.00" class="form-control discount">
                                    <p>Grand Total: <input type="text" value="0.00" class="form-control GTotal" disabled="">
                                    <input type="hidden" value="0.00" class="form-control GTotal" tabindex="-1" ></p>

                                    <div class="form-actions">
                                        <p>&nbsp;</p>
                                        <button type="button" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                        <a href="{{ route('tax_invoices.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
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

    function roundToTwo(num) {
        return +(Math.round(num + "e+2")  + "e-2");
    }

    $(document).on("keyup",'.quantity', function () {
        var Currentrow = $(this).closest("tr");
        var quantity = $(this).val();
        quantity=parseFloat(quantity);
        if (parseInt(quantity) >= 0)
        {
            var price = parseFloat(Currentrow.find('.price').val());
            var VAT = parseFloat(Currentrow.find('.VAT').val());
            Currentrow.find('.rowTotal').val(roundToTwo(quantity*price));
            Currentrow.find('.hidden_vat').val(roundToTwo((quantity*price*VAT)/100));
            Currentrow.find('.rowSubTotal').val(roundToTwo((quantity*price)+(quantity*price*VAT)/100));
            CountTotal()
        }
    });

    $(document).on("keyup",'.price', function () {
        var Currentrow = $(this).closest("tr");
        var price = $(this).val();
        price=parseFloat(price);
        if (parseInt(price) >= 0)
        {
            var quantity = parseFloat(Currentrow.find('.quantity').val());
            var VAT = parseFloat(Currentrow.find('.VAT').val());
            Currentrow.find('.rowTotal').val(roundToTwo(quantity*price));
            Currentrow.find('.hidden_vat').val(roundToTwo((quantity*price*VAT)/100));
            Currentrow.find('.rowSubTotal').val(roundToTwo((quantity*price)+(quantity*price*VAT)/100));
            CountTotal()
        }
    });

    $(document).on("keyup",'.VAT', function () {
        var Currentrow = $(this).closest("tr");
        var VAT = $(this).val();
        VAT=parseFloat(VAT);
        if (parseInt(VAT) >= 0)
        {
            var quantity = parseFloat(Currentrow.find('.quantity').val());
            var price = parseFloat(Currentrow.find('.price').val());
            Currentrow.find('.rowTotal').val(roundToTwo(quantity*price));
            Currentrow.find('.hidden_vat').val(roundToTwo((quantity*price*VAT)/100));
            Currentrow.find('.rowSubTotal').val(roundToTwo((quantity*price)+(quantity*price*VAT)/100));
            CountTotal()
        }
    });

    $(document).on("keyup",'.discount', function () {
        CountTotal();
    });

    function CountTotal() {
        var final_subtotal = 0;
        var final_vat = 0;
        var final_grandtotal = 0;
        var discount = $('.discount').val();
        $('#newRow tr').each(function () {
            if ($(this).find(".rowTotal").val().trim() !== ""){
                final_subtotal = parseFloat(final_subtotal) + roundToTwo(parseFloat($(this).find(".rowTotal").val()));
            }
            if ($(this).find(".rowTotal").val().trim() !== ""){
                final_vat = parseFloat(final_vat) + roundToTwo(parseFloat($(this).find(".hidden_vat").val()));
            }
            if ($(this).find(".rowTotal").val().trim() !== ""){
                final_grandtotal = parseFloat(final_grandtotal) + roundToTwo(parseFloat($(this).find(".rowSubTotal").val()));
            }
        });
        $('.cashPaid').val((final_subtotal.toFixed(2)));
        $('.TotalVat').val((final_vat.toFixed(2)));
        final_grandtotal=final_grandtotal-parseFloat(discount);
        $('.GTotal').val((final_grandtotal.toFixed(2)));
    }
</script>
<script>
    $(document).ready(function () {
        function validateRow(currentRow)
        {
            var isvalid = true;
            product_id = currentRow.find('.product_id').val();
            unit_id = currentRow.find('.unit_id').val();
            quantity = currentRow.find('.quantity').val();
            price = currentRow.find('.price').val();
            if (parseInt(product_id) === 0 || product_id === ""){
                isvalid = false;
            }
            if (parseInt(unit_id) === 0 || unit_id === ""){
                isvalid = false;
            }
            if (parseInt(quantity) === 0 || quantity === ""){
                isvalid = false;
            }
            if (parseInt(price) === 0 || price === ""){
                isvalid = false;
            }
            return isvalid;
        }
        // ///////////////////// Add new Row //////////////////////
        $(document).on("click",'.addRow', function () {
            var currentRow = $(this).closest("tr");
            if(validateRow(currentRow))
            {
                var vat = currentRow.find('.VAT').val();
                var discount = currentRow.find('.discount').val();
                {
                    $('.addRow').removeAttr("value", "");
                    $('.addRow').attr("value", "X");
                    $('.addRow').removeClass('btn-success').addClass('btn-danger');
                    $('.addRow').removeClass('addRow').addClass('remove');

                    var html = '';
                    html += '<tr>';
                    html += '<td><select name="Product_id" class="product_id form-control"><option value="" selected>Product</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->Name }}</option>@endforeach</select></td>';
                    html += '<td><input type="text" placeholder="Description" class="Description form-control"></td>';
                    html += '<td><select name="unit_id" class="unit_id form-control"><option value="" selected>Unit</option>@foreach($units as $unit)<option value="{{ $unit->id }}">{{ $unit->Name }}</option>@endforeach</select></td>';
                    html += '<td><input type="number" placeholder="Quantity" value="0" class="quantity form-control"><input type="hidden" placeholder="Total" class="total form-control"><input type="hidden" placeholder="Total discount" class="totalD form-control"><input type="hidden" placeholder="singleItemVat" class="singleItemVat form-control"></td>';
                    html += '<td><input type="text" placeholder="Price" value="0" class="price form-control">';
                    html += '<td><input type="text" class="rowTotal form-control" readonly></td>';
                    html += '<td><input type="number" id="VAT" value="5" class="VAT form-control"><input type="hidden" class="hidden_vat" value="0"></td>';
                    html += '<td><input type="hidden" class="rowSubTotal form-control"><input type="text"  class="rowSubTotal form-control" readonly></td>';
                    html += '<td><input class="btn btn-success addRow" id="addRow" type="button" value="+" /></td>';
                    html += '</tr>';
                    $('#newRow').append(html);
                }
            }
            else
            {
                alert('Please enter all required details...');
            }
        });
        ///////// end of add new row //////////////

        ////////////// Remove row ///////////////
        $(document).on("click",'.remove', function () {
            var Current = $(this).closest('tr');
            Current.remove();
        });
        // /////////////end remove row //////////////

        function validateAllRows()
        {
            var isvalid = true;
            $('#newRow tr').each(function () {
                var currentRow = $(this).closest("tr");

                product_id = currentRow.find('.product_id').val();
                unit_id = currentRow.find('.unit_id').val();
                quantity = currentRow.find('.quantity').val();
                price = currentRow.find('.price').val();
                if (parseInt(product_id) === 0 || product_id === "") {
                    isvalid = false;
                }
                if (parseInt(unit_id) === 0 || unit_id === "") {
                    isvalid = false;
                }
                if (parseInt(quantity) === 0 || quantity === "") {
                    isvalid = false;
                }
                if (parseInt(price) === 0 || price === "") {
                    isvalid = false;
                }
            });
            return isvalid;
        }

        function DoTrim(strComp)
        {
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

            if (DoTrim(document.getElementById('supplier_id').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("supplier_id").focus();
                }
                fields = '1';
                $("#supplier_id").addClass("error");
            }

            if (DoTrim(document.getElementById('project_id').value).length == 0)
            {
                if(fields != 1)
                {
                    document.getElementById("project_id").focus();
                }
                fields = '1';
                $("#project_id").addClass("error");
            }

            if (fields != "")
            {
                return false;
            }
            else
            {
                return true;
            }
            /*validation*/
        }

        $(document).ready(function () {
        $('#submit').click(function () {
            if(validateForm() && validateAllRows())
            {
                $('#submit').text('please wait...');
                $('#submit').attr('disabled',true);

                var insert = [], orderItem = [];
                $('#newRow tr').each(function () {
                    var currentRow = $(this).closest("tr");

                    var quantity=currentRow.find('.quantity').val();
                    var price=currentRow.find('.price').val();
                    quantity=parseFloat(quantity).toFixed(2);
                    price=parseFloat(price);
                    orderItem =
                        {
                            product_id: currentRow.find('.product_id').val(),
                            Description: currentRow.find('.Description').val(),
                            unit_id: currentRow.find('.unit_id').val(),
                            Quantity: quantity,
                            Price: price,
                            rowTotal: currentRow.find('.rowTotal').val(),
                            VAT: currentRow.find('.VAT').val(),
                            rowVatAmount: currentRow.find('.hidden_vat').val(),
                            rowSubTotal: currentRow.find('.rowSubTotal').val(),
                        };
                    insert.push(orderItem);
                });
                let details = {
                    supplier_id: $('#supplier_id').val(),
                    project_id: $('#project_id').val(),
                    InvoiceNumber: $('#InvoiceNumber').val(),
                    ReferenceNumber: $('#ReferenceNumber').val(),
                    FromDate: $('#FromDate').val(),
                    DueDate: $('#DueDate').val(),
                    TermsAndCondition: $('#TermsAndCondition').val(),
                    CustomerNote: $('#CustomerNote').val(),
                    IsNeedStampOrSignature: $('#IsNeedStampOrSignature').val(),
                    subTotal: $('.cashPaid').val(),
                    totalVat: $('.TotalVat').val(),
                    discount: $('.discount').val(),
                    grandTotal: $('.GTotal').val(),
                    orders: insert,
                }

                if (insert.length > 0)
                {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var Datas = {Data: details};
                    $.ajax({
                        url: "{{ route('purchase_invoices.store') }}",
                        type: "post",
                        data: Datas,
                        success: function (result) {
                            var result=JSON.parse(result);
                            if (result.result === true) {
                                alert(result.message);
                                window.location.href = "{{ route('purchase_invoices.index') }}";
                            } else {
                                alert('Something Went Wrong please try again...');
                                window.location.href = "{{ route('purchase_invoices.create') }}";
                            }
                        },
                        error: function (errormessage) {
                            alert(errormessage);
                        }
                    });
                }
                else
                {
                    alert('Please Add item to list');
                    $('#submit').text('Save');
                    $('#submit').attr('disabled',false);
                }
            }
            else
            {
                alert('Please enter all required data then proceed....');
            }
        });

        $('.supplier_id').change(function () {
            Id = $(this).val();
            if (Id > 0)
            {
                $.ajax({
                    url: "{{ URL('supplierDetails') }}/" + Id,
                    type: "get",
                    dataType: "json",
                    success: function (result) {
                        if (result !== false)
                        {
                            $('#Address').text(result.supplier[0].Address);
                            $('#Mobile').text(result.supplier[0].Mobile);
                            $('#Email').text(result.supplier[0].Email);
                            $('#TRN').text(result.supplier[0].TRNNumber);
                        } else {
                            alert('Supplier information not found....');
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
    });
</script>
@endsection
