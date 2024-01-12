@extends('areaseb::layouts.app')

@section('css')
    <link rel="stylesheet" href="{{asset('calendar/css/app.css')}}">
@stop

@section('meta_title')
    <title>Calendario | {{config('app.name')}}</title>
@stop



@section('css')
<style>
.select2-selection__choice:nth-child(1) {
  background-color: #3788d8 ! important;
  border-color: #3788d8 ! important;
}
.select2-selection__choice:nth-child(2) {
  background-color: #ff1a1a ! important;
  border-color: #ff1a1a ! important;
}
.select2-selection__choice:nth-child(3) {
  background-color: #009933 ! important;
  border-color: #009933 ! important;
}
.select2-selection__choice:nth-child(4) {
  background-color: #ffa31a ! important;
  border-color: #ffa31a ! important;
}

</style>
@stop

@section('content')

    @include('areaseb::core.calendars.calendar')
    @include('areaseb::core.events.create')
    @include('areaseb::core.events.show-event')

@stop

<?php

$value = Cookie::get('calendar-cookie');
?>

{{$value}}
@section('scripts')

    <script src="{{asset('plugins/jquery-ui/jquery-ui.min.js')}}"></script>
    <script src="{{asset('calendar/js/app.js')}}"></script>


<script>

var calendar_cookie = '<?php echo $value; ?>';
console.log(calendar_cookie);
    function setCookie(type){
        $.ajax({
        url: '/set-cookie',
        type: 'POST',
        data: {
            type: type,
            _token: "{{ csrf_token() }}",
        },
        dataType: 'json',
        success: function(data) {
        },
        error: function() {}
    });
    }
    $(function () {
        moment.locale('it');
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'it',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            },
            plugins: [ 'bootstrap', 'interaction', 'dayGrid', 'timeGrid','timeGridPlugin' ],
            initialView: 'timeGridWeek',
            header: {left: 'prev,next today',center: 'title',right: 'dayGridMonth,timeGridWeek,timeGridDay'},
            themeSystem: 'bootstrap',
            events: "{{url('api/calendars/'.$calendar->id.'/events')}}",
            editable: true,
            selectable: true,
            eventLimit: true,
            
            customButtons: {
            	dayGridMonth: {
	                text: 'Mese',
	                click: function() {
	                    setCookie('mese');           
	                    calendar.changeView('dayGridMonth');
	                }
	            },
	            timeGridWeek: {
	                text: 'Settimana',
	                click: function() {
	                    setCookie('settimana');           
	                    calendar.changeView('timeGridWeek');
	                }
	            },
	            timeGridDay: {
	                text: 'Giorno',
	                click: function() {
	                    setCookie('giorno');
	                    calendar.changeView('timeGridDay');
	                }
	            },
            },
            dateClick: function(info) {
                eventModal(info, calendar);
            },
            eventClick: function(info) {
                showModal(info.event.id)
            }
        });
        calendar.render();

        $('select.selectCalendar').select2({placeholder:'Cambia calendario'});
        $('select.selectCalendar2').select2({placeholder:'Cambia calendario'});

        $('button.overlay').on('click', function(e){
            e.preventDefault();
            let selected = '?ids=';let count = 0;
            $.each($('select.selectCalendar').select2('data'), function(){
                selected += $(this)[0].id+'-';
                count++;
            })
            if(count > 0)
            {
                selected = selected.substring(0, selected.length - 1);
                window.location.href = "{{url('calendars')}}/overlayed"+selected;
            }
        });


        $('button.overlay2').on('click', function(e){
            e.preventDefault();
            let selected = '?ids=';let count = 0;
            $.each($('select.selectCalendar2').select2('data'), function(){
                selected += $(this)[0].id+'-';
                count++;
            })
            if(count > 0)
            {
                selected = selected.substring(0, selected.length - 1);
                window.location.href = "{{url('calendars')}}/overlayed"+selected;
            }
        });

        $('a#menu-cal').addClass('active');

        let url = "{{request()->url()}}";
        let arr = url.split('/');
        let cal_id = arr.slice(-1)[0];
        $('select.selectCalendar').val([cal_id]).change();
        $('select.selectCalendar2').val([cal_id]).change();

		
		if(calendar_cookie == 'mese')
            calendar.changeView('dayGridMonth');
        if(calendar_cookie == 'settimana')
            calendar.changeView('timeGridWeek');
        if(calendar_cookie == 'giorno')
            calendar.changeView('timeGridDay');
            

    });
</script>
@stop
