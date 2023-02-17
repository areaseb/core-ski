@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}newsletters">Newsletters</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Crea Newsletter'])

@section('css')
    <link rel="stylesheet" href="{{asset('plugins/summernote/summernote-bs4.css')}}">
@stop

@section('content')

    {!! Form::open(['url' => url('newsletters'), 'autocomplete' => 'off', 'id' => 'newsletterForm']) !!}
        <div class="row">
            @include('areaseb::components.errors')
            @include('areaseb::core.newsletters.form')
        </div>
    {!! Form::close() !!}

@stop

@section('scripts')
    <script src="{{asset('plugins/summernote/summernote-bs4.min.js')}}"></script>
@stop
