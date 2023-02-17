@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}contacts">Contatti</a></li>
        <li class="breadcrumb-item"><a href="{{$contact->url}}">{{$contact->fullname}}</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica Contatto'])


@section('content')

    {!! Form::model($contact, ['url' => route('contacts.update', $contact->id), 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'contactForm']) !!}
        <div class="row">
            @include('areaseb::components.errors')
            {!! Form::hidden('prev', url()->previous()) !!}
            @include('areaseb::core.contacts.form')
        </div>
    {!! Form::close() !!}

@stop
