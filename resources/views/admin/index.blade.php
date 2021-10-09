@extends('shared.layout-admin')
@section('title', 'SOORCF')
@section('content')
    {{-- data coming from admin controller --}}
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <div class="row">
                            <div class="col-md-3">
                                <h2>Dashboard</h2></div>
                            <div class="col-md-3">
                                @if(session('role_name')=='superadmin' or session('role_name')=='admin')
                                    <a href="javascript:void(0)" onclick="return get_dashboard_data(1)"><button style="color: black;font-weight: 600;" id="submit" type="button" class="btn btn-warning">Get Dashboard Data / OR it will appear after 1 minutes.</button></a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card-group">
                    <!-- card -->
{{--                    <div class="card o-income">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex m-b-30 no-block">--}}
{{--                                <h4 class="card-title m-b-0 align-self-center">Daily Income</h4>--}}
{{--                                <div class="ml-auto">--}}
{{--                                    <select class="custom-select border-0">--}}
{{--                                        <option selected="">Today</option>--}}
{{--                                        <option value="1">Tomorrow</option>--}}
{{--                                    </select>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div id="income" style="height:260px; width:100%;"></div>--}}
{{--                            <ul class="list-inline m-t-30 text-center font-12">--}}
{{--                                <li><i class="fa fa-circle text-success"></i> Growth</li>--}}
{{--                                <li><i class="fa fa-circle text-info"></i> Net</li>--}}
{{--                            </ul>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <!-- card -->
{{--                    <div class="card">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex m-b-30 no-block">--}}
{{--                                <h4 class="card-title m-b-0 align-self-center">Visitors</h4>--}}
{{--                                <div class="ml-auto">--}}
{{--                                    <select class="custom-select border-0">--}}
{{--                                        <option selected="">Today</option>--}}
{{--                                        <option value="1">Tomorrow</option>--}}
{{--                                    </select>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div id="visitor" style="height:260px; width:100%;"></div>--}}
{{--                            <ul class="list-inline m-t-30 text-center font-12">--}}
{{--                                <li><i class="fa fa-circle text-primary"></i> Tablet</li>--}}
{{--                                <li><i class="fa fa-circle text-danger"></i> Desktops</li>--}}
{{--                                <li><i class="fa fa-circle text-info"></i> Mobile</li>--}}
{{--                            </ul>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <!-- card -->
                    <div class="card">
                        <div class="p-20 p-t-25">
                            <h4 class="card-title">Your Pending Tasks</h4>
                        </div>
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Task</th>
                                <th scope="col">AssignedBy</th>
                                <th scope="col">Date</th>
                                <th scope="col">Deadline</th>
                                <th scope="col">Code</th>
                                <th scope="col">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            {{--@php $count=0 @endphp
                            @php if(isset($admin['tasks'])) { @endphp
                            @foreach($admin['tasks'] as $single)
                                @php $count++; $style=''; $class=''; @endphp
                                    @php // if($single->CompletionTime<date('h:i:s')) echo "yes"; else echo "no"; ;die; @endphp
                                    @if($single->status==1)
                                        @php $style='background-color: green;color: white;'; @endphp
                                    @elseif($single->Date==date('Y-m-d') && $single->CompletionTime>date('h:i:s') && $single->status==0)
                                        @php $style='background-color: orange;color: green;'; @endphp
                                    @elseif($single->Date<date('Y-m-d') && $single->status==0)
                                        @php $style='background-color: red;color: yellow;'; $class='blink_me'; @endphp
                                    @endif

                                <tr style="@php echo $style; @endphp" class="@php echo $class; @endphp">
                                    <th scope="row">{{$count}}</th>
                                    <td>{{$single->master_task->Name}}</td>
                                    <td>{{$single->master_task->user->name}}</td>
                                    <td>{{date('d-m-Y', strtotime($single->Date))}}</td>
                                    <td>{{$single->CompletionTime}}</td>
                                    <td>{{$single->code}}</td>
                                    <td>{{ ($single->status == 0) ? 'Pending':'Completed' }}</td>
                                </tr>
                            @endforeach
                            @php } @endphp--}}

                            </tbody>
                        </table>
                    </div>

                    <div class="card">
                        <div class="p-20 p-t-25">
                            <h4 class="card-title">Overall Summary</h4>
                        </div>
                        <div class="d-flex no-block align-items-center">
                            <div class="m-l-10 ">
{{--                                <h3 class="m-b-0">Cash On Hand : {{$admin['cash_on_hand']->Differentiate}}</h3>--}}
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex no-block align-items-center">
                            <div class="m-l-10 ">
                                <h3 class="m-b-0">Total Payable : <span id="total_payable">N.A.</span></h3>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex no-block align-items-center">
                            <div class="m-l-10 ">
                                <h3 class="m-b-0">Total Receivable : <span id="total_receivable">N.A.</span></h3>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex no-block align-items-center">
                            <div class="m-l-10 ">
                                <h3 class="m-b-0">Loan Payable : <span id="loan_payable">N.A.</span></h3>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex no-block align-items-center">
                            <div class="m-l-10 ">
                                <h3 class="m-b-0">Loan Receivable : <span id="loan_receivable">N.A.</span></h3>
                            </div>
                        </div>
                        <hr>
                        @if(Session::get('company_id') != 4 && Session::get('company_id') != 5 && Session::get('company_id') != 8)
                        <div class="d-flex no-block align-items-center">
                            <div class="m-l-10 ">
                                <h3 class="m-b-0">Stock On Hand : <span id="stock_on_hand_qty">N.A.</span></h3>
                            </div>
                            <div class="m-l-10 ">
                                <h3 class="m-b-0">Other Stock : <span id="other_stock_qty">N.A.</span></h3>
                            </div>
                            <div class="m-l-10 ">
                                <h3 class="m-b-0">Current Stock : <span id="current_stock_qty">N.A.</span></h3>
                            </div>
                        </div>
                        @endif
                    </div>

                        <div class="card">
                            <div class="p-20 p-t-25">
                                <h4 class="card-title">Today Summary</h4>
                            </div>
                            <div class="d-flex no-block align-items-center">
                                <div class="m-l-10 ">
                                    <h3 class="m-b-0">Today's Cash Sale : <span id="today_total_cash_sale_amount">N.A.</span></h3>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex no-block align-items-center">
                                <div class="m-l-10 ">
                                    <h3 class="m-b-0">Today's Credit Sale : <span id="today_total_credit_sale_amount">N.A.</span></h3>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex no-block align-items-center">
                                <div class="m-l-10 ">
                                    <h3 class="m-b-0">Today's Total Sale : <span id="today_total_sale_amount">N.A.</span></h3>
                                </div>
                            </div>
                            <hr>
                            @if(Session::get('company_id') != 4 && Session::get('company_id') != 5 && Session::get('company_id') != 8)
                            <div class="d-flex no-block align-items-center">
                                <div class="m-l-10 ">
                                    <h3 class="m-b-0">Today's Sales Qty : <span id="today_sales_qty">N.A.</span></h3>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex no-block align-items-center">
                                <div class="m-l-10 ">
                                    <h3 class="m-b-0">Today's Purchase Qty : <span id="today_purchase_qty">N.A.</span></h3>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex no-block align-items-center">
                                <div class="m-l-10 ">
                                    <h3 class="m-b-0">Today's Purchase Amount : <span id="today_purchase_amount">N.A.</span></h3>
                                </div>
                            </div>
                            <hr>
                            @endif
                            <div class="d-flex no-block align-items-center">
                                <div class="m-l-10 ">
                                    <h3 class="m-b-0">Today's Expense : <span id="today_expense_amount">N.A.</span></h3>
                                </div>
                            </div>
                        </div>
                </div>

                {{-- start of card group --}}
                <div class="card-group">
                    <div class="card">
                        <div class="p-20 p-t-25">
                            <h4 class="card-title">Expense Monitor (* current month) | <span id="sum_of_expense"></span> | <span id="average_of_expense"></span></h4>
                        </div>
                        <div id="expense_analysis"></div>
                    </div>
                </div>
                {{-- end of card group --}}

                {{-- start of card group --}}
                <div class="card-group">
                    <div class="card">
                        <div class="p-20 p-t-25">
                            <h4 class="card-title">Expense Analysis By Category</h4>
                        </div>
                        <div id="expense_analysis_by_category"></div>
                    </div>
                </div>
                {{-- end of card group --}}

                {{-- start of card group --}}
                <div class="card-group">
                    <div class="card">
                        <div class="p-20 p-t-25">
                            <h4 class="card-title">Sales Monitor (* current month | QTY) | <span id="sum_of_sales"></span> | <span id="average_of_sales"></span></h4>
                        </div>
                        <div id="sales_analysis"></div>
                    </div>
                </div>
                {{-- end of card group --}}

                {{-- start of card group --}}
                <div class="card-group">
                    <div class="card">
                        <div class="p-20 p-t-25">
                            <h4 class="card-title">Purchase Monitor (* current month | QTY) | <span id="sum_of_purchase"></span> | <span id="average_of_purchase"></span></h4>
                        </div>
                        <div id="purchase_analysis"></div>
                    </div>
                </div>
                {{-- end of card group --}}

                {{-- start of card group --}}
                <div class="card-group">
                    <div class="card">
                        <div class="p-20 p-t-25">
                            <h4 class="card-title">Receivable Summary Monitor (* current month | AED )</h4>
                        </div>
                        <div id="receivable_analysis"></div>
                    </div>
                </div>
                {{-- end of card group --}}

                {{-- start of card group --}}
                <div class="card-group">
                    <div class="card">
                        <div class="p-20 p-t-25">
                            <h4 class="card-title">Payable Summary Monitor (* current month | AED )</h4>
                        </div>
                        <div id="payable_analysis"></div>
                    </div>
                </div>
                {{-- end of card group --}}

                {{-- start of card group --}}
                <div class="card-group">
                    <div class="card">
                        <div class="p-20 p-t-25">
                            <h4 class="card-title">Visa About to Expire</h4>
                        </div>
                        <div id="visa_about_to_expire"></div>
                    </div>
                </div>
                {{-- end of card group --}}

                {{-- start of card group --}}
                <div class="card-group">
                    <div class="card">
                        <div class="p-20 p-t-25">
                            <h4 class="card-title">Driving Licence About to Expire</h4>
                        </div>
                        <div id="driving_licence_about_to_expire"></div>
                    </div>
                </div>
                {{-- end of card group --}}

            </div>
        </div>

    <script>
        function get_dashboard_data(id)
        {
            $('#submit').text('please wait...Fetching Information');
            $('#submit').attr('disabled',true);

            $.ajax({
                url: "{{ URL('GetDashboardData') }}/" + id,
                type: "get",
                success: function (result) {

                    $('#total_payable').html(parseFloat(result.total_payable).toFixed(2));
                    $('#total_receivable').html(parseFloat(result.total_receivable).toFixed(2));
                    $('#loan_payable').html(parseFloat(result.loan_payable).toFixed(2));
                    $('#loan_receivable').html(parseFloat(result.loan_receivable).toFixed(2));
                    $('#stock_on_hand_qty').html(parseFloat(result.stock_qty).toFixed(2));
                    $('#other_stock_qty').html(parseFloat(result.other_stock).toFixed(2));
                    $('#current_stock_qty').html(parseFloat(result.stock_qty+result.other_stock).toFixed(2));

                    $.ajax({
                        url: "{{ URL('GetDashboardData') }}/" + 2,
                        type: "get",
                        success: function (result) {
                            $('#today_total_cash_sale_amount').html(parseFloat(result.today_cash_sale).toFixed(2));
                            $('#today_total_credit_sale_amount').html(parseFloat(result.today_credit_sale).toFixed(2));
                            $('#today_total_sale_amount').html(parseFloat(result.today_total_sale).toFixed(2));
                            $('#today_sales_qty').html(parseFloat(result.today_sale_qty).toFixed(2));
                            $('#today_purchase_qty').html(parseFloat(result.today_purchase_qty).toFixed(2));
                            $('#today_purchase_amount').html(parseFloat(result.today_purchase_amount).toFixed(2));
                            $('#today_expense_amount').html(parseFloat(result.today_expense_amount).toFixed(2));

                            $.ajax({
                                url: "{{ URL('GetDashboardData') }}/" + 3,
                                type: "get",
                                success: function (result) {
                                    $('#expense_analysis').html(result.expense_analysis);
                                    $('#sum_of_expense').html(result.sum_of_expense);
                                    $('#average_of_expense').html(result.average_of_expense);

                                    $('#expense_analysis_by_category').html(result.expense_analysis_by_category);

                                    $.ajax({
                                        url: "{{ URL('GetDashboardData') }}/" + 4,
                                        type: "get",
                                        success: function (result) {
                                            $('#sales_analysis').html(result.sales_analysis);
                                            $('#sum_of_sales').html(result.sum_of_sales);
                                            $('#average_of_sales').html(result.average_of_sales);

                                            $('#purchase_analysis').html(result.purchase_analysis);
                                            $('#sum_of_purchase').html(result.sum_of_purchase);
                                            $('#average_of_purchase').html(result.average_of_purchase);

                                            $.ajax({
                                                url: "{{ URL('GetDashboardData') }}/" + 5,
                                                type: "get",
                                                success: function (result) {
                                                    $('#receivable_analysis').html(result.receivable_analysis);

                                                    $.ajax({
                                                        url: "{{ URL('GetDashboardData') }}/" + 6,
                                                        type: "get",
                                                        success: function (result) {
                                                            $('#payable_analysis').html(result.payable_analysis);

                                                            $.ajax({
                                                                url: "{{ URL('GetDashboardData') }}/" + 7,
                                                                type: "get",
                                                                success: function (result) {
                                                                    $('#visa_about_to_expire').html(result.visa_about_to_expire);
                                                                    $.ajax({
                                                                        url: "{{ URL('GetDashboardData') }}/" + 8,
                                                                        type: "get",
                                                                        success: function (result) {
                                                                            $('#driving_licence_about_to_expire').html(result.driving_licence_about_to_expire);
                                                                            $('#submit').text('Refresh Dashboard Data');
                                                                            $('#submit').attr('disabled',false);
                                                                        },
                                                                        error: function () {
                                                                            alert('No Data Found');
                                                                        }
                                                                    });
                                                                },
                                                                error: function () {
                                                                    alert('No Data Found');
                                                                }
                                                            });
                                                        },
                                                        error: function () {
                                                            alert('No Data Found');
                                                        }
                                                    });
                                                },
                                                error: function () {
                                                    alert('No Data Found');
                                                }
                                            });
                                        },
                                        error: function () {
                                            alert('No Data Found');
                                        }
                                    });
                                },
                                error: function () {
                                    alert('No Data Found');
                                }
                            });
                        },
                        error: function () {
                            alert('No Data Found');
                        }
                    });
                },
                error: function () {
                    alert('No Data Found');
                }
            });
        }

        $(document).ready(function () {
            window.setTimeout(function(){
                get_dashboard_data(1);
            }, 60000);
        });
    </script>
@endsection
