@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}contacts">Contatti</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Crea Contatto'])


@section('content')

    {!! Form::open(['url' => url('contacts'), 'autocomplete' => 'off', 'id' => 'contactForm']) !!}
        <div class="row">
            @include('areaseb::components.errors')
            {!! Form::hidden('prev', url()->previous()) !!}
            @include('areaseb::core.contacts.form')
        </div>
    {!! Form::close() !!}

@stop
