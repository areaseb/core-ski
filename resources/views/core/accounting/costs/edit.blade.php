@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}costs">Costi</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica Prodotto'])


@section('content')

    {!! Form::model($cost, ['url' => $cost->url, 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'costForm']) !!}
        <div class="row">

            {!! Form::hidden('previous', url()->previous())  !!}

            @include('areaseb::components.errors')
            @include('areaseb::core.accounting.costs.form')
        </div>
    {!! Form::close() !!}

@stop
