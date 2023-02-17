@php
    $arrQ = []; $barData = [];
    foreach(range(date('Y'), date('Y')-3) as $year)
    {
        $arrQ[$year][1] = $Stat::totMonthImponibile($year, 1, 3);
        $arrQ[$year][2] = $Stat::totMonthImponibile($year, 4, 3);
        $arrQ[$year][3] = $Stat::totMonthImponibile($year, 7, 3);
        $arrQ[$year][4] = $Stat::totMonthImponibile($year, 10, 3);
    }
    $barData[1] = $arrQ[date('Y')-3][1].', '.$arrQ[date('Y')-2][1].', '.$arrQ[date('Y')-1][1].', '.$arrQ[date('Y')][1];
    $barData[2] = $arrQ[date('Y')-3][2].', '.$arrQ[date('Y')-2][2].', '.$arrQ[date('Y')-1][2].', '.$arrQ[date('Y')][2];
    $barData[3] = $arrQ[date('Y')-3][3].', '.$arrQ[date('Y')-2][3].', '.$arrQ[date('Y')-1][3].', '.$arrQ[date('Y')][3];
    $barData[4] = $arrQ[date('Y')-3][4].', '.$arrQ[date('Y')-2][4].', '.$arrQ[date('Y')-1][4].', '.$arrQ[date('Y')][4];
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Tabella Fatturato Trimestrale</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <th style="border-top:none;"></th>
                                <th style="border-top:none;">Primo</th>
                                <th style="border-top:none;">Secondo</th>
                                <th style="border-top:none;">Terzo</th>
                                <th style="border-top:none;">Quarto</th>
                            </thead>
                            <tbody>
                                @foreach($arrQ as $year => $values)
                                    <tr>
                                        <td><b>{{$year}}</b></td>
                                        <td>€ {{number_format($values[1], 0, ',', '.')}}</td>
                                        <td>€ {{number_format($values[2], 0, ',', '.')}}</td>
                                        <td>€ {{number_format($values[3], 0, ',', '.')}}</td>
                                        <td>€ {{number_format($values[4], 0, ',', '.')}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Grafico Fatturato Trimestrale</h3>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="barChart" style="min-height: 260px; height: 260px; max-height: 260px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script>

    var chartData = {
      labels  : ['{{date('Y')-4}}', '{{date('Y')-3}}', '{{date('Y')-2}}', '{{date('Y')-1}}'],
      datasets: [
        {
          label               : '1st',
          backgroundColor     : 'rgba(60,141,188,0.9)',
          borderColor         : 'rgba(60,141,188,0.8)',
          pointRadius          : false,
          pointColor          : '#3b8bba',
          pointStrokeColor    : 'rgba(60,141,188,1)',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data                : [{{$barData[1]}}]
        },
        {
          label               : '2nd',
          backgroundColor     : 'rgba(210, 214, 222, 1)',
          borderColor         : 'rgba(210, 214, 222, 1)',
          pointRadius         : false,
          pointColor          : 'rgba(210, 214, 222, 1)',
          pointStrokeColor    : '#c1c7d1',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(220,220,220,1)',
          data                : [{{$barData[2]}}]
        },
        {
          label               : '3rd',
          backgroundColor     : 'rgba(10, 214, 222, 1)',
          borderColor         : 'rgba(10, 214, 222, 1)',
          pointRadius         : false,
          pointColor          : 'rgba(10, 214, 222, 1)',
          pointStrokeColor    : '#c63a00',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(20,220,220,1)',
          data                : [{{$barData[3]}}]
      },
      {
        label               : '4th',
        backgroundColor     : 'rgba(50, 114, 222, 1)',
        borderColor         : 'rgba(50, 114, 222, 1)',
        pointRadius         : false,
        pointColor          : 'rgba(50, 114, 222, 1)',
        pointStrokeColor    : '#a5e8d9',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(50,120,220,1)',
        data                : [{{$barData[4]}}]
      },
  ]
    }

    var barChartCanvas = $('#barChart').get(0).getContext('2d')
    var barChartData = jQuery.extend(true, {}, chartData)
    var temp0 = chartData.datasets[0]
    var temp1 = chartData.datasets[1]
    var temp2 = chartData.datasets[2]
    var temp3 = chartData.datasets[3]
    barChartData.datasets[0] = temp0
    barChartData.datasets[1] = temp1
    barChartData.datasets[2] = temp2
    barChartData.datasets[3] = temp3

    var barChartOptions = {
      responsive              : true,
      maintainAspectRatio     : false,
      datasetFill             : false,
      legend: {
        display: false
      },
    }

    var barChart = new Chart(barChartCanvas, {
      type: 'bar',
      data: barChartData,
      options: barChartOptions
    })

    </script>
@endpush
