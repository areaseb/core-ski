@extends('areaseb::layouts.app')

@section('css')
<style>

</style>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Bilancio'])

@php
    $Stat = new Areaseb\Core\Models\Stat;
@endphp

@section('content')

    @include('areaseb::core.accounting.stats.components.iva')

    @include('areaseb::core.accounting.stats.components.balance-annual')

    @include('areaseb::core.accounting.stats.components.balance-quarterly')

    @include('areaseb::core.accounting.stats.components.balance-monthly')

@stop

@section('scripts')
    <script src="{{asset('plugins/chart.js/Chart.min.js')}}"></script>
    <script>

    $('a#menu-stats-bilancio').addClass('active');
    $('a#menu-stats-aziende').parent('li').parent('ul.nav-treeview').css('display', 'block');
    $('a#menu-stats-aziende').parent('li').parent('ul').parent('li.has-treeview ').addClass('menu-open');

    </script>
@stop
