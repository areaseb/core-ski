@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}offices">Sedi</a></li>
    <li class="breadcrumb-item"><a href="{{'contacts-office/'.$contact->id}}">{{$contact->fullname}}</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica Contatto'])


@section('content')

    {!! Form::model($contact, ['url' => 'contacts-office/'.$contact->id, 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'contactForm']) !!}
        <div class="row">
            @include('areaseb::components.errors')
            {!! Form::hidden('prev', url()->previous()) !!}
            @include('areaseb::core.contacts_office.form')
        </div>
    {!! Form::close() !!}

@stop
