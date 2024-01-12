@extends('areaseb::layouts.app')

@section('css')
<style>

</style>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Statistiche Maestri'])

@section('content')
    <div class="row">

        <div class="col">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Ore & redditività</h3>

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
	                            	<a href="@if(date('m') >= 6) {{url('stats/maestri?year='.date('Y'))}} @else {{url('stats/maestri?year='.(date('Y')-1))}} @endif"><button type="submit" class="btn btn-secondary btn-lg" id="submitForm"><i class="fa fa-redo"></i></button></a>
	                            @endif
	                        </div>
                        </div>
                    </div>

                </div>
                <div class="card-body">
                    <div class="row">
                        	
                        	@php
                        		
                        		$discipline = [1, 2, 4];
                        		
                        	@endphp
                        	
                        	@foreach($discipline as $disciplina)
                        	
	                        	<div class="col-md-4 col-xs-12">
	                        		
	                        		@php
	                        			switch($disciplina){
	                        				case 1:
	                        					$disc = 'Discesa';
	                        					break;
	                        				case 2:
	                        				case 3:
	                        					$disc = 'Fondo';
	                        					break;
	                        				case 4:
	                        					$disc = 'Snowboard';
	                        					break;
	                        			}
	                        		@endphp
	                        		
	                        		<h3>{{$disc}}</h3>
	                        		<table class="table table-responsive">
	                    				<tr>
	                    					<th>Maestro</th>
	                    					<th class="text-center">Ore</th>
	                    					<th class="text-center">Redditività</th>
	                    				</tr>
	                    				@php
	                    					$tot_disciplina = 0;
	                    				@endphp
	                    				
	                    				@foreach($masters->where('disciplina', $disciplina) as $maestro)
	                    					@if(isset($ore_maestro[$maestro->id]))
	                    						@php
	                    							$tot_disciplina += $ore_maestro[$maestro->id];
	                    						@endphp
			                    				<tr>
			                    					<td><a href="/stats/maestro/{{$maestro->id}}">{{$maestro->contact->fullname}}</a> {{-- \Areaseb\Core\Models\Contact::where('id', $maestro->contact_id)->first()->fullname --}}</td>
			                    					<td class="text-center"> {!! number_format($ore_maestro[$maestro->id], 2, ',', '.') !!}</td>
			                    					<td class="text-center">{!! number_format(($ore_maestro[$maestro->id] / $fatturato) * 100, 2, ',', '.') !!}</td>
			                    				</tr>
			                    			@endif
		                    			@endforeach
	                    				
	                    				<tr>
	                    					<td><b>Totale ore</b></td>
	                    					<td colspan="2"><b>{!! number_format($tot_disciplina, 2, ',', '.') !!}</b></td>
	                    				</tr>
	                    			</table>
	                        	</div>
                        	
                        	@endforeach
                        	
                    </div>
                </div>
            </div>
        </div>

    </div>
@stop


@section('scripts')
    <script>

    


    $('select.selectYear').select2({placeholder:'Cambia Anno', allowClear: true});
    $('select.selectYear').on('change', function(){
        let val = $(this).find('option:selected').val();
        if(val != 'tutti')
        {
            window.location.href = baseURL+'stats/maestri?year='+val;
        }
        else
        {
            window.location.href = baseURL+'stats/maestri';
        }
    });


    $('a#menu-stats-maestri').addClass('active');
    $('a#menu-stats-aziende').parent('li').parent('ul.nav-treeview').css('display', 'block');
    $('a#menu-stats-aziende').parent('li').parent('ul').parent('li.has-treeview ').addClass('menu-open');


    </script>
@stop
