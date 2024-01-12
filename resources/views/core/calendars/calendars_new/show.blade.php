@extends('areaseb::layouts.app')

@section('meta_title')
    <title>Calendario | {{config('app.name')}}</title>
@stop

@section('content')

    @include('areaseb::core.calendars_new.calendar')
	
    @include('areaseb::core.events.show-event')

@stop

@section('scripts')
<script src="{{asset('calendar_sch/index.global.js')}}"></script>
<script>

document.addEventListener('DOMContentLoaded', function() {
    let calendarEl = document.getElementById('calendarContainer');

	let calendar = new FullCalendar.Calendar(calendarEl, {		
		schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',

		initialView: 'resourceTimelineDay',		
		locale: 'it',
	    eventTimeFormat: {
	        hour: '2-digit',
	        minute: '2-digit',
	        hour12: false
	    },

		//plugins: [ 'bootstrap', 'interaction', 'dayGrid', 'timeGrid','timeGridPlugin' ],
        //initialView: 'timeGridWeek',
        //header: {left: 'prev,next today',center: 'title',right: 'dayGridMonth,timeGridWeek,timeGridDay'},
        themeSystem: 'bootstrap',
        editable: true,
        selectable: true,

		resources: [
			@foreach($calendars as $calendar)
				{ id: {{$calendar->id}}, title: '{{$calendar->user->name}} ({{$calendar->id}})' },				
			@endforeach		
		],

		events: "{{url('api/calendars_timeline/events')}}",

		dateClick: function(info) {
            eventModal(info, calendar);
        },
        eventClick: function(info) {
            showModal(info.event.id)
        }
	});

    calendar.render();
});

</script>
@stop
