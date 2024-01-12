@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}housing">Alloggi</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica Alloggio'])


@section('content')

    {!! Form::model($housing, ['url' => url("housing/$housing->id"), 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'alloggioForm']) !!}
        <div class="row">
            {!! Form::hidden('previous', url()->previous()) !!}
            @include('areaseb::components.errors')
            @include('areaseb::core.housing.form')
        </div>
    {!! Form::close() !!}

@stop
