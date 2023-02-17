@extends('areaseb::layouts.app')

@section('meta_title')
    @if(isset($template))
        <title>{{$template->nome}}</title>
    @else
        <title>Nuovo Template</title>
    @endif
@stop

@section('css')
<style>
    section.content{padding:0 ! important;}
    .container-fluid{padding:0 ! important;}
</style>
@stop

@section('content')
    <div class="embed-responsive embed-responsive-4by3" style="height:99.9vh">
        @if(isset($template))
            <iframe class="embed-responsive-item" src="{{$template->builder}}" title="Modifica il tuo template"></iframe>
        @else
            <iframe class="embed-responsive-item" src="{{url('template-builder')}}" title="Crea il tuo template"></iframe>
        @endif
    </div>
@stop


@section('scripts')
    <script>
        $('.main-header.navbar.navbar-expand.navbar-white.navbar-light').css('display', 'none');
        $('body').addClass('sidebar-collapse');
    </script>
@stop
