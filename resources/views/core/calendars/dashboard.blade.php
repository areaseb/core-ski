@php

    //$calendarClass = new Areaseb\Core\Models\Calendar;

    $events = [];
    if($user->contact->master != null){
        $events = Areaseb\Core\Models\Ora::where('id_maestro', $user->contact->master->id)->where('data', \Carbon\Carbon::now()->today()->format('Y-m-d'))->orderBy('ora_in');
    }
    else{
        $events = Areaseb\Core\Models\Ora::where('data', \Carbon\Carbon::now()->today()->format('Y-m-d'))->orderBy('ora_in');
    }
    
@endphp

<div class="col-md-6">
    <div class="card card-outline card-danger">

        <div class="card-header">
            <h3 class="card-title">Ore a planning</h3>
        </div>
        <div class="card-body">
        	<div class="table-responsive">
	            <table class="table table-sm expandable">
	                <thead>
	                    <tr>
	                        <th width="10%">Ora</th>
	                        @if(!auth()->user()->hasRole('Maestro'))
	                        	<th>Maestro</th>
	                        @endif
	                        <th>Cliente</th>
	                        <th>Ritrovo</th>
	                        <th>Specialità</th>
	                        <th>Note</th>
	                        <th></th>
	                    </tr>
	                </thead>
	                <tbody>
	                    @foreach($events->orderBy('ora_in', 'ASC')->get() as $event)
	                    	@php
	                    		list($tipo, $id) = explode('_', $event->id_cliente);
	                    		
	                    		if(date('m') < 6){
	                    			$year_from = date('Y')-1;
	                    		} else {
	                    			$year_from = date('Y');
	                    		}
	                    		if(date('m') >= 6){
	                    			$year_to = date('Y')+1;
	                    		} else {
	                    			$year_to = date('Y');
	                    		}
	                    		
	                    		if($tipo == 'T' || $tipo == 'Y'){
	                        		$lista_ore = Areaseb\Core\Models\Ora::where('id_cliente', $event->id_cliente)->where('data', '>=', $year_from.'-06-01')->where('data', '<=', $year_to.'-05-31')->orderBy('data')->orderBy('ora_in')->get();
	                        	} elseif($tipo == 'C'){
	                        		$lista_ore = Areaseb\Core\Models\CollettivoAllievi::where('id_collettivo', $id)->where('id_maestro', $event->id_maestro)->where('giorno', $event->data)->get();
	                        	} else {
	                        		$lista_ore = array();
	                        	}
	                 
	                        	
	                    	@endphp
	                            	
	                        <tr id="row-{{$event->id}}">
	                            <td>{{substr($event->ora_in, 0, -3)}} - {{substr($event->ora_out, 0, -3)}}</td>                            
	                        	@if(!auth()->user()->hasRole('Maestro'))
	                        		<td>{{ Areaseb\Core\Models\Master::find($event->id_maestro)->contact->fullname }}</td>
	                        	@endif
	                            <td>  
	                            	@if($tipo == 'T')
		                            	@if(Areaseb\Core\Models\Contact::find($id))
		                            		{{Areaseb\Core\Models\Contact::find($id)->fullname}}
		                            	@endif
	                            	@elseif($tipo == 'Y')
		                            	@if(Areaseb\Core\Models\Company::find($id))
		                            		{{Areaseb\Core\Models\Company::find($id)->rag_soc}}
		                            	@endif
	                            	@elseif($tipo == 'C')
		                            	@if(Areaseb\Core\Models\Collettivo::find($id))
		                            		Coll. {{Areaseb\Core\Models\Collettivo::find($id)->nome}}
		                            	@endif
	                            	@elseif($tipo == 'L')
		                            	@if(Areaseb\Core\Models\Label::find($id))
		                            		{{Areaseb\Core\Models\Label::find($id)->nome}}
		                            	@endif
	                            	@endif
	                            	
	                            </td>
	                            <td>{{$event->ritrovo}}</td>
	                            <td>
	                            	@php
	                            		$lista = explode(',', $event->specialita);
	                            	@endphp
	                            	
	                            	@if(count($lista) > 0)
	                            		@foreach($lista as $spec)
	                            			@if($spec != "")
	                            				{{ (new Areaseb\Core\Models\Specialization)->where('id', $spec)->first()->nome }}
	                            			@endif                            			
	                            		@endforeach
	                            	@endif
	                            </td>
	                            <td>{{$event->note}}</td>
	                        </tr>
	                        @if($tipo == 'T' || $tipo == 'Y')
	                        	<tr class="d-none bg-secondary" id="row-{{$event->id}}-expand">
	                        		<td><b>Data</b></td>
	                        		<td><b>Pax</b></td>
	                    			<td @if(!auth()->user()->hasRole('Maestro')) colspan="1" @else colspan="2" @endif><b>Ore</b></td>
	                    			<td colspan="2"><b>Maestro</b></td>
	                    			<td><b>Note</b></td>
	                        	</tr>
	                        @elseif($tipo == 'C')
	                        	<tr class="d-none bg-secondary" id="row-{{$event->id}}-expand">
	                        		<td colspan="2"><b>Partecipante</b></td>
	                    			<td @if(auth()->user()->hasRole('Maestro')) colspan="2" @endif><b>Età</b></td>
	                    			<td colspan="2"><b>Livello</b></td>
	                    			<td><b>Note</b></td>
	                        	</tr>
	                        @endif
	                        @foreach($lista_ore as $lo)
		                        <tr class="d-none bg-secondary" id="row-{{$event->id}}-expand">
		                        	@if($tipo == 'T' || $tipo == 'Y')
		                        		
	                        			@php
	                        				$origin = date_create($lo->data . ' ' . substr($lo->ora_in, 0, 8));
											$target = date_create($lo->data . ' ' . substr($lo->ora_out, 0, 8));
											$interval = date_diff($origin, $target);
											$diff_ore = $interval->format('%H');
											$diff_min = $interval->format('%I');
											if($diff_min == 30){
												$diff_ore += 0.5;
											}
	                        			@endphp
	                        			<td>{!! date('d/m/Y', strtotime($lo->data)) !!}</td>
	                        			<td>{{ $lo->pax }}}</td>
	                        			<td @if(auth()->user()->hasRole('Maestro')) colspan="2" @endif>{{$diff_ore}}</td>
	                        			<td colspan="2">{!! Areaseb\Core\Models\Master::find($lo->id_maestro)->contact->fullname !!}</td>
	                        			<td>{{$lo->note}}</td>
		                        		
		                        		
		                        	@elseif($tipo == 'C')
		                        	
		                        		<td colspan="2">{!! Areaseb\Core\Models\Contact::find($lo->partecipante)->fullname !!}</td>
		                    			<td @if(auth()->user()->hasRole('Maestro')) colspan="2" @endif>{{$lo->eta}}</td>
		                    			<td colspan="2">{{$lo->livello}}</td>
		                    			<td>{{$lo->note}}</td>
			                    		
		                        	@endif
		                        </tr>
		                	@endforeach
	                    @endforeach
	                </tbody>
	            </table>
	        </div>
        </div>
        @if($events->count())
            <div class="card-footer bg-danger p-0">
                <a href="{{url('/planning')}}" class="btn btn-sm btn-block">Vedi Planning</a>
            </div>
        @endif
    </div>

</div>


@push('scripts')
    <script>
        $('table.expandable tr').on('click', function(){
            let rowName = $(this).attr('id');
            $('tr#'+rowName+'-expand').toggleClass('d-none');
        });
    </script>
@endpush