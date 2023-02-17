@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}products">Prodotti</a></li>
        <li class="breadcrumb-item"><a href="{{$product->url}}">{{$product->nome}}</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica Prodotto'])


@section('content')

    {!! Form::model($product, ['url' => $product->url, 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'productForm']) !!}
        <div class="row">
            @include('areaseb::components.errors')
            @include('areaseb::core.accounting.products.form')
        </div>
    {!! Form::close() !!}

@stop
