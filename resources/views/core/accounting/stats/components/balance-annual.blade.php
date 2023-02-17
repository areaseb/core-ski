<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Tabella Bilanci Annuali</h3>
            </div>
            <div class="card-body">
                <div class="row">

                    @foreach(range(date('Y')-3, date('Y')) as $year)
                        @if( intval(str_replace("€ ","", $Stat::Totale($year)) ) )
                            <div class="col-12 col-md-3">
                                <div class="card card-outline card-primary">
                                    <div class="card-header text-center" style="padding:.3rem">
                                        <h3 class="card-title w100">{{$year}}</h3>
                                    </div>
                                    <div class="card-body text-center" style="padding:.9rem .5rem">
                                        <span class="text-success"><small><b>Fatturato</b></small><br>{{ $Stat::TotaleImponibile($year) }}</span><br>
                                        <span class="text-danger"><small><b>Costi</b></small><br>{{ $Stat::TotaleCostiImponibile($year) }}</span><br>
                                        <span class="text-warning"><small><b>Utili</b></small><br>{{ $Stat::UtiliImponibile($year) }}</span><br>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Bilanci annuali divisi per mese</h3>
            </div>
            <div class="card-body" style="height:274px;">
                <div class="chart">
                   <canvas id="lineChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                 </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
    <script>

    var data = {
      labels  : ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dec'],
      datasets: [
        {
          label               : '{{date("Y")-3}}',
          backgroundColor     : 'rgba(60,141,188,0.9)',
          borderColor         : 'rgba(60,141,188,0.8)',
          pointRadius          : false,
          pointColor          : '#3b8bba',
          pointStrokeColor    : 'rgba(60,141,188,1)',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data                : [{{$graphData[(date('Y')-3)]}}]
        },
        {
          label               : '{{date("Y")-2}}',
          backgroundColor     : 'rgba(210, 214, 222, 1)',
          borderColor         : 'rgba(210, 214, 222, 1)',
          pointRadius         : false,
          pointColor          : 'rgba(210, 214, 222, 1)',
          pointStrokeColor    : '#c1c7d1',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(220,220,220,1)',
          data                : [{{$graphData[(date('Y')-2)]}}]
        },
        {
          label               : '{{date("Y")-1}}',
          backgroundColor     : 'rgba(10, 214, 222, 1)',
          borderColor         : 'rgba(10, 214, 222, 1)',
          pointRadius         : false,
          pointColor          : 'rgba(10, 214, 222, 1)',
          pointStrokeColor    : '#c63a00',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(20,220,220,1)',
          data                : [{{$graphData[(date('Y')-1)]}}]
      },
      {
        label               : '{{date("Y")}}',
        backgroundColor     : 'rgba(220, 53, 69, 1)',
        borderColor         : 'rgba(220, 53, 69, 1)',
        pointRadius         : false,
        pointColor          : 'rgba(220, 53, 69, 1)',
        pointStrokeColor    : '#c63a00',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(20,220,220,1)',
        data                : [{{$graphData[(date('Y'))]}}]
      }
      ,]
    }


    var options = {
      maintainAspectRatio : false,
      responsive : true,
       scales: {
        xAxes: [{
          gridLines : {
            display : true,
          }
        }],
        yAxes: [{
          ticks: {min: {{round($graphData['min'])}}},
          gridLines : {
            display : true,
          }
        }]
      }
    }

    var lineChartCanvas = $('#lineChart').get(0).getContext('2d')
    var lineChartOptions = jQuery.extend(true, {}, options)
    var lineChartData = jQuery.extend(true, {}, data)
    lineChartData.datasets[0].fill = false;
    lineChartData.datasets[1].fill = false;
    lineChartData.datasets[2].fill = false;
    lineChartData.datasets[3].fill = false;
    lineChartOptions.datasetFill = false

    var lineChart = new Chart(lineChartCanvas, {
        type: 'line',
        data: lineChartData,
        options: lineChartOptions
    });

</script>

@endpush
