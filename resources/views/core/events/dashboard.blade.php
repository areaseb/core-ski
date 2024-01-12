@php

    $eventClass = new Areaseb\Core\Models\Event;
    $calendarClass = new Areaseb\Core\Models\Calendar;

    $calendarIds = $calendarClass::where('nome', '!=', 'global')->where('user_id', 1)->pluck('id')->toArray();
    $calendarIds[] = $user->calendars()->first()->id;

    $query = $eventClass::whereIn('calendar_id', $calendarIds)->where('done', false);

    $events = (clone $query)->whereBetween('starts_at', [\Carbon\Carbon::now()->startOfDay(),\Carbon\Carbon::now()->endOfDay()]);

    if($events->count() < 5)
    {
        $events = (clone $query)->whereBetween('starts_at', [\Carbon\Carbon::now()->startOfDay(),\Carbon\Carbon::tomorrow()->endOfDay()]);
    }

    if($events->count() < 5)
    {
        $events = (clone $query)->whereBetween('starts_at', [\Carbon\Carbon::now()->startOfDay(),\Carbon\Carbon::today()->addDays(7)->endOfDay()]);
    }

    $realClaendarId = ($events)->distinct('calendar_id')->pluck('calendar_id')->toArray();

@endphp

<div class="col-md-6">
    <div class="card card-outline card-danger">

        <div class="card-header">
            <h3 class="card-title">Eventi in scadenza</h3>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Scadenza</th>
                        <th>Titolo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events->orderBy('starts_at', 'ASC')->take(5)->get() as $event)
                        <tr>
                            <td>{{$event->starts_at->format('d/m/Y H:i')}}</td>
                            <td colspan="2">
                                @if($event->eventable_id)
                                    @if($event->eventable_type == 'Illuminate\\Support\\Collection')

                                    @else
                                        @if($event->eventable)
                                            @includeIf('areaseb::core.events.types.'.strtolower($event->eventable->class), ['event' => $event])
                                        @else
                                            {{$event->title ?? 'title missing'}}
                                        @endif
                                    @endif
                                @else
                                    {{$event->title ?? 'title missing'}}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($events->count())
            <div class="card-footer bg-danger p-0">
                @php
                if(count($realClaendarId) > 1)
                {
                    $url = "calendars/overlayed?ids=".implode(',', $realClaendarId);
                }
                else
                {
                    $url = "calendars/".$realClaendarId[0];
                }
                @endphp
                <a href="{{url($url)}}" class="btn btn-sm btn-block">Vedi Calendario</a>
            </div>
        @endif
    </div>

</div>

