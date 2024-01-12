@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}hangout">Ritrovi</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica Ritrovo'])


@section('content')

    {!! Form::model($hangout, ['url' => url("hangout/$hangout->id"), 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'ritrovoForm']) !!}
        <div class="row">
            {!! Form::hidden('previous', url()->previous()) !!}
            @include('areaseb::components.errors')
            @include('areaseb::core.hangout.form')
        </div>
    {!! Form::close() !!}

@stop
