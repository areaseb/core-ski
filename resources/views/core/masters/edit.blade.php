@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}masters">Maestri</a></li>
    <li class="breadcrumb-item"><a href="{{$company->url}}">{{$company->rag_soc}}</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica Maestro'])


@section('content')

    {!! Form::model($company, ['url' => '/masters/'.$company->id, 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'masterForm']) !!}
        <div class="row">
            {!! Form::hidden('previous', url()->previous()) !!}
            @include('areaseb::components.errors')
            @include('areaseb::core.masters.form')
        </div>
    {!! Form::close() !!}

@stop
