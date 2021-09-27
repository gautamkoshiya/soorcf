@extends('shared.layout-admin')
@section('title', 'View Top Customers')

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
                            <li class="breadcrumb-item active">Top Customers</li>
                        </ol>
                       </div>
                </div>
            </div>
            <h2>{{$title}}</h2>
            <div>
                <canvas id="myChart" style="width:1200px;max-width:1500px;height: 500px;"></canvas>
            </div>
        </div>
    </div>

    <script>
        var xValues = {!! json_encode($all_names) !!};
        var yValues = {!! json_encode($all_amount) !!};

        new Chart("myChart", {
            type: "bar",
            data: {
                labels: xValues,
                datasets: [{
                    label: 'Quantity',
                    fill: false,
                    lineTension: 0,
                    backgroundColor: "rgba(142, 68, 212,1.0)",
                    borderColor: "rgba(0,0,255,0.1)",
                    data: yValues
                }]
            },
            options: {
                legend: {display: false},
                scales: {
                    yAxes: [{ticks: {min: 6, max:16}}],
                },
                responsive: true,
            }
        });
    </script>
@endsection
