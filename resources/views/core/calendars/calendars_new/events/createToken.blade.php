@extends('areaseb::layouts.clean')

@section('meta_title')
<title>Aggiungi Evento</title>
@stop

@section('content')

    {!! Form::open(['url' => url()->current(), 'autocomplete' => 'off', 'id' => 'eventForm']) !!}
        <div class="row">
            @include('areaseb::core.events.formToken')
        </div>
    {!! Form::close() !!}

@stop
