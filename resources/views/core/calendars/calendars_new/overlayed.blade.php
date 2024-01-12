@extends('areaseb::layouts.app')

@section('css')
    <link rel="stylesheet" href="{{asset('calendar/css/app.css')}}">
@stop

@section('meta_title')
    <title>Calendario Multiplo | {{config('app.name')}}</title>
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


@section('scripts')

    <script src="{{asset('plugins/jquery-ui/jquery-ui.min.js')}}"></script>
    <script src="{{asset('calendar/js/app.js')}}"></script>


<script>
    $(function () {
        moment.locale('it');
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'it',
            timeFormat: 'H(:mm)',
            plugins: [ 'bootstrap', 'interaction', 'dayGrid', 'timeGrid' ],
            header: {left: 'prev,next today',center: 'title',right: 'dayGridMonth,timeGridWeek,timeGridDay'},
            themeSystem: 'bootstrap',
            events: "{{url('api/calendars/'.$calendar_ids.'/events')}}",
            editable: true,
            selectable: true,
            eventLimit: true,
            dateClick: function(info) {
                eventModal(info, calendar);
            },
            eventClick: function(info) {
                showModal(info.event.id)
            }
        });
        calendar.render();

        $('select.selectCalendar').select2({placeholder:'Cambia calendario'});

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

        @if(isset($calendar_ids))
            let srtCal = "{{$calendar_ids}}";
            $('select.selectCalendar').val(srtCal.split('-'));
            $('select.selectCalendar').trigger('change');
        @endif

    });
</script>
@stop
