@php
    $monthly = $Stat::TotaleMese(date('Y'));
    $monthlyVat = $Stat::TotaleMeseVat(date('Y'));
    $monthData = [];
@endphp
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-info">

            @foreach($monthly as $year => $month_stats)

                @if(isset($monthly[$year-1]))

                    <div class="card-header">
                        <h3 class="card-title">Fatturato Mensile {{$year}} </h3>
                    </div>
                    <div class="card-body pl-1 pr-1 pb-0" >
                        <div class="row">

                            @php
                                $md = '';
                            @endphp

                            @for($x=1;$x<=12;$x++)
                                @php
                                    $current = $monthly[$year][$x];
                                    $currentVat = $monthlyVat[$year][$x];
                                    // $currentVat = $month_vat_stats[date('Y')][$x];
                                    $last = $monthly[($year-1)][$x];
                                    $perc = 0;
                                    if($current != 0 && $last != 0)
                                    {
                                        $perc = round((1-$current/$last)*100);
                                        $perc = ($last > $current) ? -abs($perc) : abs($perc);
                                    }

                                    $md .= '"'.$current.'",'

                                @endphp
                                <div class="col-3 col-lg-1 ">
                                    <div class="small-box text-center">
                                        @lang('dates.ms'.$x)<br>
                                        @if($current != 0)
                                            <small>€ {{number_format($current, 0, ',', '.')}}</small>
                                        @endif
                                        @if($current == $last || $current == 0)
                                            <small class="text-muted"><i class="fas fa-minus text-sm"></i></small>
                                        @elseif($current > $last)
                                            <small class="text-success"><i class="fas fa-arrow-up text-sm"></i>@if($perc != 0) {{$perc}}% @endif</small>
                                        @else
                                            <small class="text-danger"><i class="fas fa-arrow-down text-sm"></i>@if($perc != 0) {{$perc}}% @endif</small>
                                        @endif
                                        @if($current != 0)
                                            <br><small>€ {{number_format($currentVat, 0, ',', '.')}} IVA</small>
                                        @else
                                            <br><small>&nbsp;</small>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                            @php
                                $monthData[$year] = substr($md, 0, -1);
                            @endphp

                        </div>
                    </div>
                @endif

            @endforeach

            <div class="card-header">
                <h3 class="card-title">Grafico Fatturato Mensile</h3>
            </div>
            <div class="card-body pl-1 pr-1 pb-0">
                <div class="chart">
                    <canvas id="barChartM" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
    <script>

    var cData = {
      labels  : [@lang('dates.months_short')],
      datasets: [
        {
          label               : "{{date('Y')-2}}",
          backgroundColor     : 'rgba(60,141,188,0.9)',
          borderColor         : 'rgba(60,141,188,0.8)',
          pointRadius          : false,
          pointColor          : '#3b8bba',
          pointStrokeColor    : 'rgba(60,141,188,1)',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data                : [{!!$monthData[(date('Y')-2)]!!}]
        },
        {
          label               : "{{date('Y')-1}}",
          backgroundColor     : 'rgba(210, 214, 222, 1)',
          borderColor         : 'rgba(210, 214, 222, 1)',
          pointRadius         : false,
          pointColor          : 'rgba(210, 214, 222, 1)',
          pointStrokeColor    : '#c1c7d1',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(220,220,220,1)',
          data                : [{!!$monthData[(date('Y')-1)]!!}]
        },
        {
          label               : "{{date('Y')}}",
          backgroundColor     : 'rgba(10, 214, 222, 1)',
          borderColor         : 'rgba(10, 214, 222, 1)',
          pointRadius         : false,
          pointColor          : 'rgba(10, 214, 222, 1)',
          pointStrokeColor    : '#c63a00',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(20,220,220,1)',
          data                : [{!!$monthData[date('Y')]!!}]
      },
  ]
    }

    var bcCanvas = $('#barChartM').get(0).getContext('2d')
    var bcData = jQuery.extend(true, {}, cData)
    var temp4 = cData.datasets[0]
    var temp5 = cData.datasets[1]
    var temp6 = cData.datasets[2]
    bcData.datasets[0] = temp4
    bcData.datasets[1] = temp5
    bcData.datasets[2] = temp6

    var bcOptions = {
      responsive              : true,
      maintainAspectRatio     : false,
      datasetFill             : false,
      legend: {
        display: false
      },
    }

    var bc = new Chart(bcCanvas, {
      type: 'bar',
      data: bcData,
      options: bcOptions
    })

    </script>
@endpush
