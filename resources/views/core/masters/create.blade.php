@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}companies">Maestri</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Crea Maestro'])


@section('content')

    {!! Form::open(['url' => url('masters'), 'autocomplete' => 'off', 'id' => 'masterForm']) !!}
        <div class="row">
            {!! Form::hidden('previous', url()->previous()) !!}
            @include('areaseb::components.errors')
            @include('areaseb::core.masters.form')
        </div>
    {!! Form::close() !!}

@stop
