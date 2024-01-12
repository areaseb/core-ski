@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}collective">Gestione Collettivi</a></li>
    <li class="breadcrumb-item"><a href="">{{$collective->nome}}</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica corso'])


@section('content')

    {!! Form::model($collective, ['url' => route('collective.update', $collective->id), 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'collectiveForm']) !!}
        <div class="row">
            @include('areaseb::components.errors')
            {!! Form::hidden('prev', url()->previous()) !!}
            @include('areaseb::core.collective.form')
        </div>
    {!! Form::close() !!}

@stop
