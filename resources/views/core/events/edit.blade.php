@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}calendars/{{$event->calendar_id}}">Calendario</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica Evento'])

@section('content')

    {!! Form::model($event, ['url' => url('events/'.$event->id), 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'eventForm']) !!}
        <div class="row">
            @include('areaseb::components.errors')
            @include('areaseb::core.events.form')
        </div>
    {!! Form::close() !!}

    {!! Form::open(['method' => 'delete', 'url' => url('events/'.$event->id), 'id' => "form-delete-event-".$event->id]) !!}
        <button type="submit" id="{{$event->id}}" class="btn btn-danger btn-icon btn-sm d-none"><i class="fa fa-trash"></i></button>
    {!! Form::close() !!}

@stop
