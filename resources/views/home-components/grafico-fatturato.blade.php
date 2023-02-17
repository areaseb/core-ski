@php

  		//colori

  		if($imponibile_precedente == 0) {
			   $percentuale_imponibile = -100;
			} else {
			   $percentuale_imponibile = ((array_sum($array_imponibile) - $imponibile_precedente) / $imponibile_precedente) * 100;
			}

			if($percentuale_imponibile < -5){
				$colore_imp = "red";
				$freccia_imp = "down";
			} elseif($percentuale_imponibile >= -5 && $percentuale_imponibile <= 5){
				$colore_imp = "yellow";
				$freccia_imp = "left";
			} else {
				$colore_imp = "green";
				$freccia_imp = "up";
			}
			$percentuale_imponibile = number_format($percentuale_imponibile, 2, ".", ",");


			if($costo_precedente == 0) {
			   $percentuale_costo = -100;
			} else {
			   $percentuale_costo = ((array_sum($array_costo) - $costo_precedente) / $costo_precedente) * 100;
			}

			if($percentuale_costo < -5){
				$colore_costo = "green";
				$freccia_costo = "down";
			} elseif($percentuale_costo >= -5 && $percentuale_costo <= 5){
				$colore_costo = "yellow";
				$freccia_costo = "left";
			} else {
				$colore_costo = "red";
				$freccia_costo = "up";
			}
			$percentuale_costo = number_format($percentuale_costo, 2, ".", ",");

            if($utile_precedente == 0)
            {
                $percentuale_utile = 0;
            }
            else
            {
                $percentuale_utile = ((array_sum($array_utile) - $utile_precedente) / $utile_precedente) * 100;
            }

			if($utile_precedente < 0){
				$percentuale_utile = -1 * $percentuale_utile;
			}

			if($percentuale_utile > 30){
				$card = "success";
			} elseif($percentuale_utile > 0 && $percentuale_utile <= 30){
				$card = "warning";
			} else {
				$card = "danger";
			}

			if($percentuale_utile < -5){
				$colore_utile = "red";
				$freccia_utile = "down";
			} elseif($percentuale_utile >= -5 && $percentuale_utile <= 5){
				$colore_utile = "yellow";
				$freccia_utile = "left";
			} else {
				$colore_utile = "green";
				$freccia_utile = "up";
			}
			$percentuale_utile = number_format($percentuale_utile, 2, ".", ",");

@endphp




<div class="col-12">
    <div class="card card-outline card-{{$card}}">
	    <div class="card-header">
	        <h3 class="card-title"><b>Vendite 01/01 - {{date('d/m')}} {{date('Y')}}</b></h3>
	    </div>
	    <div class="box-body">
	        <div class="chart">
				<canvas id="salesChart" style="height: 250px;"></canvas>
			</div>
	    </div>
	    <div class="card-footer">
	        <div class="row">
	        	<div class="col-sm-4 col-xs-6">
					<div class="description-block border-right">
						<span class="description-percentage text-{{$colore_imp}}"><i class="fa fa-caret-{{$freccia_imp}}"></i> {{$percentuale_imponibile}}%</span>
						<h5 class="description-header">&euro; {!! number_format(array_sum($array_imponibile), 2, ",", ".") !!}</h5>
						<span class="description-text">Fatturato</span>
					</div>
				</div>
				<div class="col-sm-4 col-xs-6">
					<div class="description-block border-right">
						<span class="description-percentage text-{{$colore_costo}}"><i class="fa fa-caret-{{$freccia_costo}}"></i> {{$percentuale_costo}}%</span>
						<h5 class="description-header">&euro; {!! number_format(array_sum($array_costo), 2, ",", ".") !!}</h5>
						<span class="description-text">Costi</span>
					</div>
				</div>

				<div class="col-sm-4 col-xs-6">
					<div class="description-block">
						<span class="description-percentage text-{{$colore_utile}}"><i class="fa fa-caret-{{$freccia_utile}}"></i> {{$percentuale_utile}}%</span>
						<h5 class="description-header">&euro; {!! number_format(array_sum($array_utile), 2, ",", ".") !!}</h5>
						<span class="description-text">Utile</span>
					</div>
				</div>
			</div>
	    </div>
    </div>
</div>

@push('scripts')

<script>
$(function () {

    // Get context with jQuery - using jQuery's .get() method.
    var areaChartCanvas = $('#salesChart').get(0).getContext('2d')

    var areaChartData = {
      labels  : [@php $mesi = array('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'); for($m = 0; $m <= date('m')-1; $m++){echo "'".$mesi[$m]."'"; if($m != date('m')){echo ", ";}}@endphp],
      datasets: [
        {
			label               : 'FATTURATO',
			backgroundColor     : 'rgba(0,153,51,0.3)',
			borderColor         : 'rgba(0,153,51,0.3)',
          	pointRadius         : false,
			pointColor          : 'rgba(0,153,51,0.3)',
			pointStrokeColor    : '#c1c7d1',
			pointHighlightFill  : '#fff',
			pointHighlightStroke: 'rgba(220,220,220,1)',
			data                : [@php for($m = 0; $m <= date('m')-1; $m++){ echo $array_imponibile[$m]; if($m != date('m')){print", ";}  } @endphp]
		},
		{
			label               : 'COSTI',
			backgroundColor     : 'rgba(204,0,0,0.3)',
			borderColor         : 'rgba(204,0,0,0.3)',
          	pointRadius         : false,
			pointColor          : 'rgba(204,0,0,0.3)',
			pointStrokeColor    : 'rgba(60,141,188,1)',
			pointHighlightFill  : '#fff',
			pointHighlightStroke: 'rgba(60,141,188,1)',
			data                : [@php for($m = 0; $m <= date('m')-1; $m++){ echo $array_costo[$m]; if($m != date('m')){print", ";}  } @endphp]
		},
		{
			label               : 'UTILE',
			backgroundColor     : 'rgba(255,204,0,0.3)',
			borderColor         : 'rgba(255,204,0,0.3)',
          	pointRadius         : false,
			pointColor          : 'rgba(255,204,0,0.3)',
			pointStrokeColor    : 'rgba(60,141,188,1)',
			pointHighlightFill  : '#fff',
			pointHighlightStroke: 'rgba(60,141,188,1)',
			data                : [@php for($m = 0; $m <= date('m')-1; $m++){ echo $array_utile[$m]; if($m != date('m')){print", ";}  } @endphp]
		}
      ]
    }

    var areaChartOptions = {
      maintainAspectRatio : false,
      responsive : true,
      legend: {
        display: false
      },
      scales: {
        xAxes: [{
          gridLines : {
            display : false,
          }
        }],
        yAxes: [{
          gridLines : {
            display : false,
          }
        }]
      }
    }

    // This will get the first returned node in the jQuery collection.
    var areaChart = new Chart(areaChartCanvas, {
      type: 'line',
      data: areaChartData,
      options: areaChartOptions
    })

});
</script>

@endpush
