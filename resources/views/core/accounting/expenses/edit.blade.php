@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}expenses">Spese</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica Spesa'])


@section('content')

    {!! Form::model($expense, ['url' => $expense->url, 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'expenseForm']) !!}
        <div class="row">
            @include('areaseb::components.errors')
            @include('areaseb::core.accounting.expenses.form')
        </div>
    {!! Form::close() !!}

@stop
