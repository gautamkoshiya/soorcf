@extends('shared.layout-admin')
@section('title', 'Customer Prices')

@section('content')

    <style>
        .header1 {
            color: #f1f1f1;
        }
        .content {
            padding: 16px;
        }
        .sticky {
            position: fixed;
            top: 148px;
            width: 100%
        }
        .sticky + .content {
            padding-top: 102px;
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
                            <li class="breadcrumb-item active">customer prices</li>
                        </ol>
                       <button type="button" class="btn btn-info d-lg-block m-l-15 insert"><i class="fa fa-plus-circle"></i> Create New</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">prices</h4>
                            @if (Session::has('update'))
                                <div class="alert alert-success">
                                    <ul>
                                        <li>{!! Session::get('update') !!}</li>
                                        {{Session::forget('update')}}
                                    </ul>
                                </div>
                            @endif
                            <form action="{{ route('customer_prices.store') }}" method="post" accept-charset="utf-8">
                              @csrf
                            <div class="table-responsive content">
                                <table class="table full-color-table full-info-table hover-table">
                                    <thead class="customer_price_header header1" id="customer_price_header">
                                    <tr>
                                        <th width="100">ID</th>
                                        <th width="220">Customer Name</th>
                                        <th width="100">Price</th>
{{--                                        <th>Description</th>--}}
                                        <th width="100">VAT</th>
                                        <th width="100">Limit</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($customers as $customers)
                                        <tr>
                                            <td>{{ $customers->id }}
                                                <input type="hidden" class="form-control" name="customer_id[]" value="{{ $customers->id }}">

                                                @if(!empty($customers->customer_prices[0]->id))
                                                   <input type="hidden" class="form-control" name="id[]" value="{{ $customers->customer_prices[0]->id }}">
                                                @else
                                                   <input type="hidden" class="form-control" placeholder="id" name="id[]">
                                                @endif
                                            </td>
                                            <td>{{ $customers->Name }}</td>
                                            <td>
                                                @if(!empty($customers->customer_prices[0]->Rate))
                                                   <input type="text" class="form-control" name="Rate[]" value="{{ $customers->customer_prices[0]->Rate }}">
                                                @else
                                                   <input type="text" class="form-control" placeholder="Rate" value="0.00" name="Rate[]" value="">
                                                @endif
                                            </td>

                                            {{--<td>
                                                @if(!empty($customers->customer_prices[0]->Description))
                                                   <input type="text" class="form-control" name="Description[]" value="{{ $customers->customer_prices[0]->Description }}">
                                                @else
                                                   <input type="text" class="form-control" placeholder="Description" name="Description[]">
                                                @endif
                                            </td>--}}

                                            <td>
                                                @if(!empty($customers->customer_prices[0]->VAT))
                                                   <input type="text" class="form-control" name="VAT[]" value="{{ $customers->customer_prices[0]->VAT }}">
                                                @else
                                                   <input type="text" class="form-control" placeholder="VAT" value="0.00" name="VAT[]">
                                                @endif
                                            </td>

                                            <td>
                                                @if(!empty($customers->customer_prices[0]->customerLimit))
                                                   <input type="text" class="form-control" name="customerLimit[]" value="{{ $customers->customer_prices[0]->customerLimit }}">
                                                @else
                                                   <input type="text" class="form-control" placeholder="customerLimit" value="0.00" name="customerLimit[]">
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <input type="submit" value="Submit" class="form-control btn btn-secondary" name="submit">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.onscroll = function() {myFunction()};

        var header = document.getElementById("customer_price_header");

        var sticky = header.offsetTop;

        function myFunction() {
            if (window.pageYOffset > sticky) {
                header.classList.add("sticky");
                $('.header1').css({display:'table'});
            } else {
                header.classList.remove("sticky");
                $('.header1').css({display:'contents'});
            }
        }
    </script>
@endsection
