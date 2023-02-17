@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Dashboard'])

@section('content')
    <div class="row">
        @if($view->clients)
            @can('companies.read')
                @include('areaseb::home-components.clients')
            @endcan
        @endif

        @if($view->invoices)
            @can('invoices.read')
                @include('areaseb::home-components.grafico-fatturato')
            @endcan
            @can('costs.read')
                @include('areaseb::home-components.costi-in-scadenza')
            @endcan
            @can('invoices.read')
                @include('areaseb::home-components.fatture-in-scadenza')
            @endcan
        @endif

        @isset($view->killer_quote)
            @if($view->killer_quote)
                @includeFirst(['killer.dashboard', 'killerquote::dashboard'])
            @endif
        @endisset

        @if($view->events)
            @includeIf('areaseb::core.events.dashboard')
        @endif

        @isset($view->renewals)
            @if($view->renewals)
                @includeIf('renewals::dashboard')
            @endif
        @endisset

        @isset($view->projects)
            @if($view->projects)
                @includeIf('projects::dashboard')
            @endif
        @endisset



    </div>
@stop


@section('scripts')
    <script src="{{asset('plugins/chart.js/Chart.min.js')}}"></script>
@stop
