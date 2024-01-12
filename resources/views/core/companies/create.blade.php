@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}companies">Clienti</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Crea Cliente'])


@section('content')

    {!! Form::open(['url' => url('companies'), 'autocomplete' => 'off', 'id' => 'companyForm']) !!}
        <div class="row">
            {!! Form::hidden('previous', url()->previous()) !!}
            @include('areaseb::components.errors')
            @include('areaseb::core.companies.form')
        </div>
    {!! Form::close() !!}

@stop
