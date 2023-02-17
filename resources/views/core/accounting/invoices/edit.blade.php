@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}invoices">Fatture</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica Fattura N.'.$invoice->numero])


@section('content')

    {!! Form::model($invoice, ['url' => $invoice->url, 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'productForm']) !!}
        <div class="row">
            {!! Form::hidden('previous', url()->previous()) !!}
            @include('areaseb::components.errors')
            @include('areaseb::core.accounting.invoices.form')
        </div>
    {!! Form::close() !!}
@include('areaseb::core.accounting.invoices.form-components.modal-storno')
@stop
