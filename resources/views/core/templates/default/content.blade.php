@extends('areaseb::core.templates.default.layout')

@section('content')

    {!!Areaseb\Core\Models\Template::getLastDefaultNewsletter()!!}

@stop
