@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}offices">Sedi</a></li>
    <li class="breadcrumb-item"><a href="#">{{$office->rag_soc}}</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica Sede'])


@section('content')

    {!! Form::model($office, ['url' => 'offices/'.$office->id, 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'officeForm']) !!}
        <div class="row">

            {!! Form::hidden('previous', url()->previous()) !!}
            @include('areaseb::components.errors')
            @include('areaseb::core.offices.form')
        </div>
    {!! Form::close() !!}

@stop
