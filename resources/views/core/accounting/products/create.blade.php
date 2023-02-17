@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}products">Prodotti</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Crea Prodotto'])


@section('content')

    {!! Form::open(['url' => url('products'), 'autocomplete' => 'off', 'id' => 'productForm']) !!}
        <div class="row">
            @include('areaseb::components.errors')
            @include('areaseb::core.accounting.products.form')
        </div>
    {!! Form::close() !!}

@stop
