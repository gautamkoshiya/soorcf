@extends('shared.layout-admin')
@section('title', 'Customer Sales Performance')

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
                            <li class="breadcrumb-item active">Customer Sales Performance</li>
                        </ol>
                       </div>
                </div>
            </div>
            <h3>{{$title}}</h3>
            <div>
                <canvas id="myChart" style="width:100%;max-width:1000px;height: 350px;"></canvas>
            </div>
{{--            @php--}}
{{--                var_dump($all_dates);--}}
{{--                var_dump($all_qty);--}}
{{--            @endphp--}}
        </div>
    </div>

    <script>
        //var xValues = [50,60,70,80,90,100,110,120,130,140,150];
        //var xValues = [50,60,70,80,90,100,110,120,130,140,150];
        var xValues = {!! json_encode($all_dates) !!};
        var yValues = {!! json_encode($all_qty) !!};
        var yValues1 = {!! json_encode($all_amount) !!};

        new Chart("myChart", {
            type: "line",
            data: {
                labels: xValues,
                datasets: [{
                    label: 'Quantity',
                    fill: false,
                    lineTension: 0,
                    backgroundColor: "rgba(172,0,255,1.0)",
                    borderColor: "rgba(172,0,255,0.1)",
                    data: yValues,
                    stack: 'combined',
                    type: 'bar',
                },
                {
                    label: 'Amount',
                    fill: false,
                    lineTension: 0,
                    backgroundColor: "rgba(0,0,255,1.0)",
                    borderColor: "rgba(0,0,255,0.1)",
                    data: yValues1,
                    stack: 'combined',
                },
                // {
                //     label: 'Amount',
                //     fill: false,
                //     lineTension: 0,
                //     backgroundColor: "rgba(255,0,0,1.0)",
                //     borderColor: "rgba(255,0,0,0.1)",
                //     data: yValues1
                // },
                ]
            },
            options: {
                responsive: true,
                legend: {display: false},
                /*scales: {
                    yAxes: [{ticks: {min: 6, max:16}}],
                },*/
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Date',
                            color: '#911',
                            font: {
                                family: 'Comic Sans MS',
                                size: 20,
                                weight: 'bold',
                                lineHeight: 1.2,
                            },
                            padding: {top: 20, left: 0, right: 0, bottom: 0}
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Value',
                            color: '#191',
                            font: {
                                family: 'Times',
                                size: 20,
                                style: 'normal',
                                lineHeight: 1.2
                            },
                            padding: {top: 30, left: 0, right: 0, bottom: 0}
                        }
                    }
                }
            }
        });
    </script>
@endsection
