@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}collective">Gestione Collettivi</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Inserisci nuovo corso'])


@section('content')

    {!! Form::open(['url' => url('collective'), 'autocomplete' => 'off', 'id' => 'collectiveForm']) !!}
        <div class="row">
            @include('areaseb::components.errors')
            {!! Form::hidden('prev', url()->previous()) !!}
            @include('areaseb::core.collective.form')
        </div>
    {!! Form::close() !!}

@stop
