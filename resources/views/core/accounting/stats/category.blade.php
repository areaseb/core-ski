@extends('areaseb::layouts.app')

@section('css')
<style>

</style>
@stop

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}stats/categorie?year={{date('Y')}}">Statistiche Prodotti </a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => $category->nome])

@section('content')
    <div class="row">

        <div class="col">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Fatturato & totali</h3>

                    <div class="card-tools">
                        <div class="row">
                        	<div class="col-md-3">
                        		{!!Form::open(['method' => 'GET', 'style' => 'display: inline'])!!}
                        		Anno
	                            {!!Form::select('year', [
	                                'tutti' => 'Totale',
	                                date('Y') => date('Y')." - ".date('Y') + 1,
	                                date('Y') - 1 => (date('Y') - 1)." - ".date('Y'),
	                                date('Y') - 2 => (date('Y') - 2)." - ".(date('Y') - 1),
	                                date('Y') - 3 => (date('Y') - 3)." - ".(date('Y') - 2),
	                            ], [request('year')], [ 'class'=> 'form-control form-control-sm selectYear'])!!}
                            </div>
                            <div class="col-md-3">
	                            Da <input type="date" name="data_in" value="{{request()->get('data_in')}}" class="form-control" id="data_in">
	                        </div>
	                        <div class="col-md-3">    
	                            A <input type="date" name="data_out" value="{{request()->get('data_out')}}" class="form-control" id="data_out">
	                        </div>
	                        <div class="col-md-3">    
	                            <br><button type="submit" class="btn btn-warning btn-lg" id="submitForm"><i class="fa fa-search"></i></button> 
	                            {!!Form::close()!!}
	                            @if(request()->get('data_in') || request()->get('data_out'))
	                            	<a href="@if(date('m') >= 6) {{url('stats/categorie/'.$category->id.'?year='.date('Y'))}} @else {{url('stats/categorie/'.$category->id.'?year='.(date('Y')-1))}} @endif"><button type="submit" class="btn btn-secondary btn-lg" id="submitForm"><i class="fa fa-redo"></i></button></a>
	                            @endif
	                        </div>
                        </div>
                    </div>

                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="chart">
                                <canvas id="barChartT" style="min-height: 450px; height: 450px; max-height: 450px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="chart">
                                <canvas id="barChartF" style="min-height: 450px; height: 450px; max-height: 450px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
    <div class="row">
        @foreach($data as $prod_id => $values)
            <div class="col-12 col-xs-6 col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h6 class="mb-1 text-center font-weight-bold">{{\Areaseb\Core\Models\Product::find($prod_id)->nome}}</h6>
                        <p class="mb-0">Venduti: {{$values['totali']}}</p>
                        <p class="mb-0">Fatturato: {{$values['fatturato']}}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if(count($sectors))
        <div class="row">
            <div class="col-12">
                <hr>
            </div>
            <div class="col-12">
                <h5 class="mb-3">Categorie clienti con più acquisti</h5>
            </div>
            @foreach($sectors as $sector_id => $count)
                <div class="col-6 col-xs-3 col-md-2 mb-2">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h6 class="mb-1 font-weight-bold">{{\Areaseb\Core\Models\Sector::find($sector_id)->nome}}</h6>
                            <p class="mb-0">Venduti: {{$count}}</p>
                            <p class="mb-0">Fatturato: € {{number_format($totalAmountSectors[$sector_id],2,',','.')}}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@stop


@section('scripts')
    <script src="{{asset('plugins/chart.js/Chart.min.js')}}"></script>
    <script>

    var areaChartDataF = {
      labels  : [{!!$graphData['labels']!!}],
      datasets: [
        {
          label               : 'Fatturato',
          backgroundColor     : 'rgba(210, 214, 222, 1)',
          borderColor         : 'rgba(210, 214, 222, 1)',
          pointRadius         : false,
          pointColor          : 'rgba(210, 214, 222, 1)',
          pointStrokeColor    : '#c1c7d1',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(220,220,220,1)',
          data                : [{!!$graphData['fatturato']!!}]
        },
      ]
    }

    var barChartCanvasF = $('#barChartF').get(0).getContext('2d')
    var barChartDataF = jQuery.extend(true, {}, areaChartDataF)
    var temp0 = areaChartDataF.datasets[0]
    barChartDataF.datasets[0] = temp0

    var barChartOptionsF = {
      responsive              : true,
      maintainAspectRatio     : false,
      datasetFill             : false
    }

    var barChartF = new Chart(barChartCanvasF, {
      type: 'bar',
      data: barChartDataF,
      options: barChartOptionsF
    })






    var areaChartDataT = {
      labels  : [{!!$graphData['labels']!!}],
      datasets: [
        {
          label               : 'Venduti',
          backgroundColor     : 'rgba(60,141,188,0.9)',
          borderColor         : 'rgba(60,141,188,0.8)',
          pointRadius          : false,
          pointColor          : '#3b8bba',
          pointStrokeColor    : 'rgba(60,141,188,1)',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data                : [{!!$graphData['totali']!!}]
        },
      ]
    }

    var barChartCanvasT = $('#barChartT').get(0).getContext('2d')
    var barChartDataT = jQuery.extend(true, {}, areaChartDataT)
    var temp1 = areaChartDataT.datasets[0]
    barChartDataT.datasets[0] = temp1

    var barChartOptionsT = {
      responsive              : true,
      maintainAspectRatio     : false,
      datasetFill             : false
    }

    var barChartT = new Chart(barChartCanvasT, {
      type: 'bar',
      data: barChartDataT,
      options: barChartOptionsT
    })


    $('select.selectYear').select2({placeholder:'Cambia Anno', allowClear: true});
    $('select.selectYear').on('change', function(){
        let val = $(this).find('option:selected').val();
        if(val != 'tutti')
        {
            window.location.href = baseURL+'stats/categorie/{{$category->id}}?year='+val;
        }
        else
        {
            window.location.href = baseURL+'stats/categorie/{{$category->id}}';
        }
    });


    $('a#menu-stats-categorie').addClass('active');
    $('a#menu-stats-aziende').parent('li').parent('ul.nav-treeview').css('display', 'block');
    $('a#menu-stats-aziende').parent('li').parent('ul').parent('li.has-treeview ').addClass('menu-open');


    </script>
@stop
