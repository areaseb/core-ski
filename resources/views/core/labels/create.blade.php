@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}labels">Segnaposto</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Crea Segnaposto'])


@section('content')

    {!! Form::open(['url' => url('labels'), 'autocomplete' => 'off', 'id' => 'labelForm']) !!}
        <div class="row">
            {!! Form::hidden('previous', url()->previous()) !!}
            @include('areaseb::components.errors')
            @include('areaseb::core.labels.form')
        </div>
    {!! Form::close() !!}

@stop
