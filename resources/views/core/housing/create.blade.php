@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}housing">Alloggi</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Crea Alloggio'])


@section('content')

    {!! Form::open(['url' => url('housing'), 'autocomplete' => 'off', 'id' => 'alloggioForm']) !!}
        <div class="row">
            {!! Form::hidden('previous', url()->previous()) !!}
            @include('areaseb::components.errors')
            @include('areaseb::core.housing.form')
        </div>
    {!! Form::close() !!}

@stop
