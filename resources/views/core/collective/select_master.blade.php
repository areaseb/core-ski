@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <!--<li class="breadcrumb-item"><a href="{{config('app.url')}}collective">Gestione Collettivi</a></li>-->
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Inserisci nuovo corso - Seleziona Maestri'])


@section('content')
{!! Form::open(['url' => '/collective/step2', 'autocomplete' => 'off', 'id' => 'contactForm']) !!}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">

                    <div class="row">
                    </div>

                </div>
                <div class="card-body">
                {!! $strHtml !!}
                </div>


            </div>
        </div>

    
    </div>
{!! Form::close() !!}
@stop