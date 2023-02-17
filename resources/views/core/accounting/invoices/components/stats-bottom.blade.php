<div class="row">
    <div class="col-lg-9 col-sm-8 col-12">
        <div class="small-box bg-info">
            <div class="inner">
                <div class="row">
                    <div class="col-3">
                        <h5 class="mb-0">{{$totQuery->imponibile}}</h5>
                        <p>Imponibile {{date('Y')}}</p>
                    </div>
                    <div class="col-9">
                        <div class="chart">
                            <canvas id="lineChart" style="min-height: 150px; height: 150px; max-height: 150px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-4 col-12">
        <div class="small-box bg-success">
            <div class="inner" style="height:170px;">
                <h3 class="mb-0">{{$totQuery->imposte}}</h3>
                <p>Imposte  {{date('Y')}}</p>
            </div>
            <div class="icon"><i class="ion ion-stats-bars" style="top:40%"></i></div>
        </div>
    </div>
</div>


@push('scripts')

<script src="{{asset('plugins/chart.js/Chart.min.js')}}"></script>
<script>

var data = {
  labels  : [{!!$graphData['labels']!!}],
  datasets: [
    {
      label               : 'Imponibile',
      backgroundColor     : 'rgba(255,255,255,0.7)',
      borderColor         : 'rgba(255,255,255,0.7)',
      pointRadius          : false,
      pointColor          : '#000',
      pointStrokeColor    : 'rgba(255,255,255,0.7)',
      pointHighlightFill  : '#fff',
      pointHighlightStroke: 'rgba(255,255,255,0.7)',
      data                : [{{$graphData['data']}}]
    }
  ]
}


var options = {
  maintainAspectRatio : false,
  responsive : true,
  legend: {
    display: false
  },
  scales: {
    xAxes: [{
      gridLines : {
        display : true,
      }
    }],
    yAxes: [{
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
lineChartOptions.datasetFill = false

var lineChart = new Chart(lineChartCanvas, {
    type: 'line',
    data: lineChartData,
    options: lineChartOptions
});

</script>

@endpush
