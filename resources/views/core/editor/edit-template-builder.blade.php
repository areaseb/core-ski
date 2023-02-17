@extends('areaseb::layouts.app')

@php
    $template = Areaseb\Core\Models\Template::find($id);
@endphp

@section('meta_title')
    <title>{{$template->nome}}</title>
@stop

@section('css')
<style>
    section.content{padding:0 ! important;}
    .container-fluid{padding:0 ! important;}
</style>
@stop

@section('content')
    <div class="embed-responsive embed-responsive-4by3" style="height:99.9vh">
        <iframe class="embed-responsive-item" src="{{$template->builder}}" title="Modifica il tuo template"></iframe>
    </div>
@stop


@section('scripts')
    <script>
        $('.main-header.navbar.navbar-expand.navbar-white.navbar-light').css('display', 'none');
        $('body').addClass('sidebar-collapse');
    </script>
@stop
