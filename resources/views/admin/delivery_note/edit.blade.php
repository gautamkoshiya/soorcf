@extends('shared.layout-admin')
@section('title', 'Edit Delivery Note')
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
    <script src="{{ asset('admin_assets/assets/dist/ckeditor/ckeditor.js') }}"></script>
    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h2 class="text-themecolor">Delivery Note</h2>
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Delivery Notes</li>
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
                                    <input type="hidden" name="delivery_note_id" id="delivery_note_id" value="{{ $delivery_note->id }}">
                                    <div class="row p-t-20">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Customer :- <span class="required">*</span></label>
                                                <select class="form-control custom-select customer_id select2 chosen-select" name="customer_id" id="customer_id" required>
                                                    <option value="">--Select Customer--</option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}" {{ ($customer->id == $delivery_note->customer->id) ? 'selected':'' }}>{{ $customer->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Project :- <span class="required">*</span></label>
                                                <select class="form-control custom-select project_id select2 chosen-select" name="project_id" id="project_id" required>
                                                    <option value="">--Select Project--</option>
                                                    @foreach($projects as $project)
                                                        <option value="{{ $project->id }}" {{ ($project->id == $delivery_note->project->id) ? 'selected':'' }}>{{ $project->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Product :- <span class="required">*</span></label>
                                                <select class="form-control custom-select product_id select2 chosen-select" name="product_id" id="product_id" required>
                                                    <option value="">--Select Product--</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" {{ ($product->id == $delivery_note->product->id) ? 'selected':'' }}>{{ $product->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Unit :- <span class="required">*</span></label>
                                                <select class="form-control custom-select unit_id select2 chosen-select" name="unit_id" id="unit_id" required>
                                                    <option value="">--Select Unit--</option>
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit->id }}" {{ ($unit->id == $delivery_note->unit->id) ? 'selected':'' }}>{{ $unit->Name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label">Order Reference :- <span class="required">*</span></label>
                                                <input type="text" name="OrderReference" id="OrderReference" class="form-control" placeholder=" PO reference number" value="{{ $delivery_note->OrderReference }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label">Quantity :- <span class="required">*</span></label>
                                                <input type="text" name="Quantity" id="Quantity" class="form-control" placeholder="Quantity" value="{{ $delivery_note->Quantity }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">DO Number :- <span class="required">*</span></label>
                                                <input type="text" class="form-control DoNumber" name="DoNumber" id="DoNumber" value="{{ $delivery_note->DoNumber }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Date :- <span class="required">*</span></label>
                                                <input type="date" name="createdDate" id="createdDate" class="form-control" value="{{ $delivery_note->createdDate }}" placeholder="dd/mm/yyyy">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label> Description :- <span class="required">*</span></label>
                                                <textarea class="form-control Description" id="Description" placeholder="Enter the Description" name="Description">{{ $delivery_note->Description }}</textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-actions">
                                                <p>&nbsp;</p>
                                                <button type="button" class="btn btn-success" id="submit"> <i class="fa fa-check"></i> Save</button>
                                                <a href="{{ route('delivery_notes.index') }}" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true"></i> Cancel</a>
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
        CKEDITOR.replace( 'Description' );
        CKEDITOR.editorConfig = function( config )
        {
            config.height = '900px';
        };
    </script>
    <script>
        $(document).ready(function () {
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

                if (DoTrim(document.getElementById('customer_id').value).length == 0)
                {
                    if(fields != 1)
                    {
                        document.getElementById("customer_id").focus();
                    }
                    fields = '1';
                    $("#customer_id").addClass("error");
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

                if (DoTrim(document.getElementById('product_id').value).length == 0)
                {
                    if(fields != 1)
                    {
                        document.getElementById("product_id").focus();
                    }
                    fields = '1';
                    $("#product_id").addClass("error");
                }

                if (DoTrim(document.getElementById('unit_id').value).length == 0)
                {
                    if(fields != 1)
                    {
                        document.getElementById("unit_id").focus();
                    }
                    fields = '1';
                    $("#unit_id").addClass("error");
                }

                if (DoTrim(document.getElementById('OrderReference').value).length == 0)
                {
                    if(fields != 1)
                    {
                        document.getElementById("OrderReference").focus();
                    }
                    fields = '1';
                    $("#OrderReference").addClass("error");
                }

                if (DoTrim(document.getElementById('Quantity').value).length == 0)
                {
                    if(fields != 1)
                    {
                        document.getElementById("Quantity").focus();
                    }
                    fields = '1';
                    $("#Quantity").addClass("error");
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
                    if(validateForm())
                    {
                        $('#submit').text('please wait...');
                        $('#submit').attr('disabled',true);
                        var desc = CKEDITOR.instances.Description.getData();
                        let details = {
                            customer_id: $('#customer_id').val(),
                            project_id: $('#project_id').val(),
                            product_id: $('#product_id').val(),
                            unit_id: $('#unit_id').val(),
                            DoNumber: $('#DoNumber').val(),
                            createdDate: $('#createdDate').val(),
                            OrderReference: $('#OrderReference').val(),
                            Quantity: $('#Quantity').val(),
                            Description: desc,
                        }
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        var Data = {Data: details};
                        var Id = $('#delivery_note_id').val();
                        $.ajax({
                            url: "{{ URL('DeliveryNoteUpdate') }}/" + Id,
                            type: "post",
                            data: Data,
                            success: function (result) {
                                if (result === true) {
                                    window.location.href = "{{ route('delivery_notes.index') }}";
                                } else {
                                    alert('Something Went Wrong please try again...');
                                    window.location.href = "{{ route('delivery_notes.create') }}";
                                }
                            },
                            error: function (errormessage) {
                                alert(errormessage);
                            }
                        });
                    }
                    else
                    {
                        alert('Please enter all required data then proceed....');
                    }
                });
            });
            ////////////// end of customer select ////////////////
        });
    </script>
@endsection
