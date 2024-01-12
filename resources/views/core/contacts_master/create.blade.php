@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}contacts_master">Maestri</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Crea Maestro'])


@section('content')

    {!! Form::open(['url' => url('contacts-master'), 'autocomplete' => 'off', 'id' => 'contactForm']) !!}
        <div class="row">
            @include('areaseb::components.errors')
            {!! Form::hidden('prev', url()->previous()) !!}
            @include('areaseb::core.contacts_master.form')
        </div>
    {!! Form::close() !!}

@stop
