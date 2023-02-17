@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}costs">Acquisti</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Crea Acquisto'])


@section('content')

    {!! Form::open(['url' => url('costs'), 'autocomplete' => 'off', 'id' => 'costForm']) !!}
        <div class="row">
            @include('areaseb::components.errors')
            @include('areaseb::core.accounting.costs.form')
        </div>
    {!! Form::close() !!}

@stop
