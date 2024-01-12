@php

    $eventClass = new Areaseb\Core\Models\Ora;
    $calendarClass = new Areaseb\Core\Models\Calendar;

    $events = [];
    if($user->maestro != null){
        $query = $eventClass::where('id_maestro', $user->maestro->id);

        $events = $query->where('data', \Carbon\Carbon::now()->today()->format('Y-m-d'))->orderBy('ora_in');
    }
    else{
        $events = $eventClass::where('data', \Carbon\Carbon::now()->today()->format('Y-m-d'))->orderBy('ora_in');
    }
    

@endphp

<div class="col-6">
    <div class="card card-outline card-danger">

        <div class="card-header">
            <h3 class="card-title">Ore a planning</h3>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Ora</th>
                        <th>Cliente</th>
                        <th>Ritrovo</th>
                        <th>Specialità</th>
                        <th>Note</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events->orderBy('ora_in', 'ASC')->get() as $event)
                        <tr>
                            <td>{{substr($event->ora_in, 0, -3)}} - {{substr($event->ora_out, 0, -3)}}</td>
                            <td>
                                @php
                                    $cc = (new Areaseb\Core\Models\Contact)->where('id', explode('_', $event->id_cliente)[1])->first();
                                @endphp
                            	@if(substr($event->id_cliente, 0, 1) == 'Y')
                            		{{ (new Areaseb\Core\Models\Company)->where('id', explode('_', $event->id_cliente)[1])->first()->rag_soc }}
                            	@else
                            		{{ $cc != null ? $cc->fullname : '' }}
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
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($events->count())
            <div class="card-footer bg-danger p-0">
                <a href="{{url('/planning')}}" class="btn btn-sm btn-block">Vedi Planning</a>
            </div>
        @endif
    </div>

</div>

