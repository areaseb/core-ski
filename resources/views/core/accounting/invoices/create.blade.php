@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}invoices">Fatture</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Crea Fattura'])


@section('content')

    {!! Form::open(['url' => url('invoices'), 'autocomplete' => 'off', 'id' => 'invoiceForm', 'class' => 'form-horizontal']) !!}

        {!! Form::hidden('previous', url()->previous()) !!}

        <div class="row">
            @include('areaseb::components.errors')
            @include('areaseb::core.accounting.invoices.form')
        </div>
    {!! Form::close() !!}



@include('areaseb::core.accounting.invoices.form-components.modal-storno')

@stop
