@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}companies">Clienti</a></li>
    <li class="breadcrumb-item"><a href="{{$company->url}}">{{$company->rag_soc}}</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica Cliente'])


@section('content')

    {!! Form::model($company, ['url' => $company->url, 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'companyForm']) !!}
        <div class="row">
            {!! Form::hidden('previous', url()->previous()) !!}
            @include('areaseb::components.errors')
            @include('areaseb::core.companies.form')
        </div>
    {!! Form::close() !!}

@stop
