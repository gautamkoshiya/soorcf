@extends('shared.layout-admin')
@section('title', 'Meter Reading')

@section('content')

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Meter Reading</h4>
            </div>
            <div class="col-md-7 align-self-center text-right">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                        <li class="breadcrumb-item active">Meter</li>
                    </ol>
                    <button type="button" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-eye"></i> View List</button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="#" enctype="multipart/form-data">
                            <div class="form-body">

                                <div class="row py-2">
                                    <div class="col-md-8">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="date" value="{{ date('Y-m-d') }}" id="meterReadingDate" name="meterReadingDate" class="form-control">
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table color-bordered-table success-bordered-table">
                                        <thead>
                                        <tr>
                                            <th style="width: 150px">Meter</th>
                                            <th style="width: 150px">Start Reading</th>
                                            <th style="width: 150px">End Reading</th>
                                            <th style="width: 150px">Net Reading</th>
                                            <th style="width: 150px">Purchases</th>
                                            <th style="width: 150px">Sales</th>
                                            <th>Description</th>
{{--                                                <th>Image</th>--}}
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="newRow">
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <select name="meter_id" class="form-control meter_id">
                                                        <option value="" selected>Meter</option>
                                                        @foreach($meter_readers as $reader)
                                                            <option value="{{ $reader->id }}">{{ $reader->Name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                            <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Start Reading" class="startReading form-control"></td>
                                            <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="End Reading" class="endReading form-control"></td>
                                            <td><input type="text" value="0.00" placeholder="Net Reading" class="netReading form-control" disabled>
                                                <input type="hidden" value="0.00" placeholder="Net Reading" class="netReading form-control" ></td>
                                            <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Purchases" class="purchases form-control">
                                                <input type="hidden" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Total Row Sale" class="totalRow form-control">
                                            </td>
                                            <td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Sales" class="sales form-control" disabled>
                                                <input type="hidden" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Sales" class="sales form-control">
                                            </td>
                                            <td><input type="text" placeholder="Net Description" class="Description form-control"></td>
{{--                                                <td><input type="file" id="meter_image" name="meter_image"></td>--}}
                                            <td><input class=" btn btn-success addRow" id="addRow" type="button" value="+" /></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <label for="meter_images">Select files:</label>
                                        <input type="file" id="meter_images" name="meter_images[]" multiple>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p>Start Pad: <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $salesByDate['firstPad'] }}" class="form-control startPad"></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p>End Pad: <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="{{ $salesByDate['lastPad'] }}" class="form-control endPad"></p>
                                            </div>
                                        </div>
                                        <p>Total Meter Reading Sale: <input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" class="form-control totalSale" disabled>
                                            <input type="hidden" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" class="form-control totalSale">
                                        </p>
                                        <p>Total Pad Sale: <input type="text" value="{{ $total }}" class="form-control totalPad" disabled>
                                            <input type="hidden" value="{{ $total }}" class="form-control totalPad">
                                        </p>
                                        <p>Difference: <input type="text" value="0.00" class="form-control balance" disabled>
                                            <input type="hidden" value="0.00" class="form-control balance">
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                <a href="{{ route('meter_readings.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
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
        /////////////// Add Record //////////////////////
        $('#submit').click(function () {
            $('#submit').text('please wait...');
            $('#submit').attr('disabled',true);

            var meter_id = $('.meter_id').val();
            if (meter_id != null)
            {
                var insert = [], orderItem = [];
                $('#newRow tr').each(function () {
                    var currentRow = $(this).closest("tr");
                    if (validateRow(currentRow)) {
                        orderItem =
                            {
                                meter_id: currentRow.find('.meter_id').val(),
                                startReading: currentRow.find('.startReading').val(),
                                endReading: currentRow.find('.endReading').val(),
                                netReading: currentRow.find('.netReading').val(),
                                purchases: currentRow.find('.purchases').val(),
                                sales: currentRow.find('.sales').val(),
                                Description: currentRow.find('.Description').val(),
                                //meter_image: currentRow.find('#meter_image').prop('files')[0],
                            };
                        insert.push(orderItem);
                    }
                    else
                    {
                        return false;
                    }
                });
                let details = {
                    meterReadingDate: $('#meterReadingDate').val(),
                    startPad: $('.startPad').val(),
                    endPad: $('.endPad').val(),
                    totalSale: $('.totalSale').val(),
                    totalPad: $('.totalPad').val(),
                    balance: $('.balance').val(),
                    orders: insert,
                }
                if (insert.length > 0) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    var formData = new FormData();
                    let TotalFiles = $('#meter_images')[0].files.length; //Total files
                    let files = $('#meter_images')[0];
                    for (let i = 0; i < TotalFiles; i++) {
                        formData.append('files' + i, files.files[i]);
                    }
                    formData.append('TotalFiles', TotalFiles);
                    formData.append('insert', JSON.stringify(details));

                    $.ajax({
                        url: "{{ route('meter_readings.store') }}",
                        type: "post",
                        data: formData,
                        cache       : false,
                        contentType : false,
                        processData : false,
                        success: function (result) {
                            // if (result === true) {
                                alert("Data Inserted Successfully");
                                window.location.href = "{{ route('meter_readings.index') }}";
                            // } else {
                            //     alert("Something Went Wrong please try again...")
                            // }
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
                alert('Select Meter first')
                $('#submit').text('Save');
                $('#submit').attr('disabled',false);
            }

        });
        //////// end of submit Records /////////////////

        /////////////////// change date //////////////////////////
        $('#meterReadingDate').change(function () {
           var Id = $(this).val();
            $.ajax({
                // headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url: "{{ URL('getSalesByDate') }}/" + Id,
                type: "get",
                dataType: "json",
                statusCode: {
                    500: function() {
                        alert("No Data Available On same Date");
                        $('.startPad').val('');
                        $('.endPad').val('');
                        $('.totalPad').val('');
                    }
                },
                success: function (result) {
                    if (result !== "Failed") {
                        $('.startPad').val(result.firstPad);
                        $('.endPad').val(result.lastPad);
                        $('.totalPad').val((result.total));

                    }
                },
            });
        });

        // ///////////////////// Add new Row //////////////////////
        $(document).on("click",'.addRow', function () {
            var currentRow = $(this).closest("tr");
            if (validateRow(currentRow))
            {
                $('.addRow').removeAttr("value", "");
                $('.addRow').attr("value", "X");
                $('.addRow').removeClass('btn-success').addClass('btn-danger');
                $('.addRow').removeClass('addRow').addClass('remove');

                var html = '';
                html += '<tr>';
                html += '<td><select name="meter_id" class="meter_id form-control"><option value="" selected>Meter</option>@foreach($meter_readers as $reader)<option value="{{ $reader->id }}">{{ $reader->Name }}</option>@endforeach</select></td>';
                html += '<td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Start Reading" class="startReading form-control"></td>';
                html += '<td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="End Reading" class="endReading form-control"></td>';
                html += '<td><input type="text" value="0.00" placeholder="Net Reading" class="netReading form-control" disabled><input type="hidden" value="0.00" placeholder="Net Reading" class="netReading form-control" ></td>';
                html += '<td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Purchases" class="purchases form-control"><input type="hidden" onfocus="this.value=\'\'" value="0.00" placeholder="Total Row Sale" class="totalRow form-control"></td>';
                html += '<td><input type="text" onClick="this.setSelectionRange(0, this.value.length)" value="0.00" placeholder="Sales" class="sales form-control" disabled><input type="hidden" onfocus="this.value=\'\'" value="0.00" placeholder="Sales" class="sales form-control"></td>';
                html += '<td><input type="text" placeholder="Net Description" class="Description form-control"></td>';
                // html += '<td><input type="file" id="meter_image" name="meter_image"></td>';
                html += '<td><input class="btn btn-success addRow" id="addRow" type="button" value="+" /></td>';
                html += '</tr>';
                $('#newRow').append(html);
            }
        });
        ///////// end of add new row //////////////
        ////////////// Remove row ///////////////
        $(document).on("click",'.remove', function () {
            var Current = $(this).closest('tr');
            Current.remove();
            CountTotal()
        });
        // /////////////end remove row //////////////
    });
</script>
{{--<script src="{{ asset('admin_assets/assets/dist/invoice/meterReading.js') }}"></script>--}}
    <script>
        $(document).on("keypress",'.startReading', function (event) {
            return isNumber(event, this)
        });
        $(document).on("keypress",'.endReading', function (event) {
            return isNumber(event, this)
        });

        $(document).on("keypress",'.purchases', function (event) {
            return isNumber(event, this)
        });

        $(document).on("keypress",'.totalSale', function (event) {
            return isNumber(event, this)
        });

        $(document).on("keypress",'.startPad', function (event) {
            return isNumber(event, this)
        });

        $(document).on("keypress",'.endPad', function (event) {
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

        //////////////////////// Add quantity ///////////
        $(document).on("keyup",'.startReading', function () {
            var Currentrow = $(this).closest("tr");
            var startReading = $(this).val();
            if (parseInt(startReading) >= 0)
            {
                var sum1 = parseFloat(Currentrow.find('.endReading').val()) - parseInt(startReading);
                // var sum = parseInt(sum1) - parseFloat(Currentrow.find('.purchases').val());
                Currentrow.find('.netReading').val(sum1);
                // Currentrow.find('.sales').val(sum) ;
                Currentrow.find('.totalRow').val(sum1) ;
                var pur = Currentrow.find('.purchases').val();
                pchase(pur, Currentrow);
                CountTotal()
            }
        });
        ///////// end of add quantity ///////////////////

        //////////////////////// Add quantity ///////////
        $(document).on("keyup",'.endReading', function () {
            var Currentrow = $(this).closest("tr");
            var endReading = $(this).val();
            if (parseInt(endReading) >= 0)
            {
                var sum1 = parseInt(endReading) - parseFloat(Currentrow.find('.startReading').val())
                //var sum = parseInt(sum1) - parseFloat(Currentrow.find('.purchases').val());
                Currentrow.find('.netReading').val(sum1);
                //Currentrow.find('.sales').val(sum) ;
                Currentrow.find('.totalRow').val(sum1) ;
                var pur = Currentrow.find('.purchases').val();
                pchase(pur, Currentrow);
                CountTotal()
            }
        });
        ///////// end of add quantity ///////////////////

        //////////////////////// Add quantity ///////////
        $(document).on("keyup",'.purchases', function () {
            var Currentrow = $(this).closest("tr");
            var purchases = $(this).val();
            if (parseInt(purchases) >= 0)
            {
                var sum = parseFloat(Currentrow.find('.totalRow').val()) - parseInt(purchases);
                Currentrow.find('.sales').val(sum) ;
                CountTotal()
            }
        });
        ///////// end of add quantity ///////////////////

        //////////////////////// Add quantity ///////////
        $(document).on("keyup",'.sales', function () {
            var Currentrow = $(this).closest("tr");
            var sales = $(this).val();
            if (parseInt(sales) >= 0)
            {
                var sum = parseInt(sales) - parseFloat(Currentrow.find('.purchases').val());
                Currentrow.find('.netDifference').val(sum);
                CountTotal()
            }
        });
        ///////// end of add quantity ///////////////////

        //////// validate rows ////////
        function validateRow(currentRow) {
            var isvalid = true;
            var endReading = 0, meter = 0, startReading = 0, meterReadingDate = $('#meterReadingDate').val();
            if (parseInt(meterReadingDate) === 0 || meterReadingDate === ""){
                isvalid = false;
            }
            meter = currentRow.find('.meter_id').val();
            startReading  = currentRow.find('.startReading').val();
            endReading = currentRow.find('.endReading').val();
            if (parseInt(meter) === 0 || meter === ""){
                isvalid = false;
            }
            if (parseInt(startReading) == 0 || startReading == "")
            {
                isvalid = false;
            }
            if (parseInt(endReading) == 0 || endReading == "")
            {
                isvalid = false
            }
            return isvalid;
        }
        ////// end of validate row ///////////////////

        //////////// tatal  /////////////////
        function CountTotal() {
            var Totalvalue = 0;
            var Gtotal = 0;
            $('#newRow tr').each(function () {
                if ($(this).find(".sales").val().trim() !== ""){
                    Gtotal = parseFloat(Gtotal) + parseFloat($(this).find(".sales").val());
                }
                else {
                    Gtotal = parseFloat(Gtotal);
                }
            });
            $('.totalSale').val((Gtotal.toFixed(2)));
            //totalSale=$('.totalSale').val();
            var totalSale=$('.totalSale').val();
            totalSale=parseFloat(totalSale);
            totalSale=totalSale.toFixed(2);
            //console.log(totalSale);
            //console.log($('.totalPad').val());

            //var Input = parseFloat(Gtotal - $('.totalPad').val());
            //var Input = parseFloat($('.totalPad').val() - Gtotal );
            var Input = parseFloat($('.totalPad').val() - totalSale );
            $('.balance').val((Input.toFixed(2)));
        }
        //////////////// end of total  /////////////

        //////////// tatal  /////////////////
        function pchase(pur, Currentrow) {
            var sum = parseFloat(Currentrow.find('.totalRow').val()) - parseInt(pur);
            Currentrow.find('.sales').val(sum) ;
            CountTotal()
        }
    </script>
@endsection
