@extends('shared.layout-admin')
@section('title', 'View Customer Acquisition Analysis')

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
                            <li class="breadcrumb-item active">Customer Acquisition Analysis</li>
                        </ol>
                       </div>
                </div>
            </div>

            <div>
                <canvas id="myChart" style="width:100%;max-width:1000px;height: 350px;"></canvas>
            </div>

            <div class="table-responsive">
                <table border="1" cellpadding="2" cellspacing="2">
                    <thead>
                    <tr>
                        <th>Date</th>
                        @foreach ($all_dates as $date)
                            <th>{{ date('d-M', strtotime($date)) }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>No.of New Customer</td>
                    @foreach ($all_customer as $item)
                        <td>{{$item}}</td>
                    @endforeach
                    </tr>
                    </tbody>
                </table>
            </div>
            <br>
        </div>
    </div>

    <script>
        var xValues = {!! json_encode($all_dates) !!};
        var yValues = {!! json_encode($all_customer) !!};

        new Chart("myChart", {
            type: "line",
            data: {
                labels: xValues,
                datasets: [{
                    label: 'Quantity',
                    fill: false,
                    lineTension: 0,
                    backgroundColor: "rgba(0,0,255,1.0)",
                    borderColor: "rgba(0,0,255,0.1)",
                    data: yValues
                }]
            },
            options: {
                legend: {display: false},
                scales: {
                    yAxes: [{ticks: {min: 6, max:16}}],
                }
            }
        });
    </script>
@endsection
