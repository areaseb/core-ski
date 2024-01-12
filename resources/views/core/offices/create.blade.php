@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}offices">Sedi</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Crea Sede'])


@section('content')

    {!! Form::open(['url' => url('offices'), 'autocomplete' => 'off', 'id' => 'officeForm']) !!}
        <div class="row">
            {!! Form::hidden('previous', url()->previous()) !!}
            @include('areaseb::components.errors')
            @include('areaseb::core.offices.form')
        </div>
    {!! Form::close() !!}

@stop
