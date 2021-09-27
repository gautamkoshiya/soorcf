@extends('shared.layout-admin')
@section('title', 'View Expense Analysis By Supplier')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.2.1/dist/chart.min.js"></script>
    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">Expense Analysis By Supplier</li>
                        </ol>
                       </div>
                </div>
            </div>
            <h4>{{$title}}</h4>
            <div class="row">
                <div class="col-md-3">
                    <div class="table-responsive">
                        <table border="1" cellpadding="2" cellspacing="2">
                            <thead>
                            <tr>
                                <th>Supplier Name</th>
                                <th>Expense Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($final_array as $item)
                                <tr>
                                    <td>{{$item['supplier_name']}}</td>
                                    <td style="text-align: right">{{$item['total_expense']}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <h4>Sum of Expense : {{$sum_of_expenses}}</h4>
                    <br>
                </div>
                <div class="col-md-9">
                    <div>
                        <canvas id="myChart" style="width:100%;max-width:600px"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var xValues = {!! json_encode(array_column($final_array,'supplier_name')); !!};
        var yValues = {!! json_encode(array_column($final_array,'total_expense')) !!};

        // var xValues = ["Italy", "France", "Spain", "USA", "Argentina"];
        // var yValues = [55, 49, 44, 24, 15];
        var barColors = [
            "#b91d47",
            "#00aba9",
            "#2b5797",
            "#e8c3b9",
            "#1e7145"
        ];

        new Chart("myChart", {
            type: "doughnut",
            data: {
                labels: xValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: yValues
                }]
            },
        });
    </script>
@endsection
