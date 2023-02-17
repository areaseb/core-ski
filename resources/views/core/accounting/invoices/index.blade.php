@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Fatture'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">
                    <h3 class="card-title">Fatture</h3>
                    <div class="card-tools">
                        {{-- <div class="form-group mr-3 mb-0 mt-2" style="float:left;">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="customSwitch1" @if(request()->input()) checked @endif>
                                <label class="custom-control-label" for="customSwitch1">Ricerca Avanzata</label>
                            </div>
                        </div> --}}

                        @can('invoices.write')
                            @if(config('core.modules')['fe'])
                                <div class="btn-group" role="group">
                                    <button id="create" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" data-display="static" aria-expanded="false">
                                        XML
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item export" title="importa fattura da xml" href="{{url('api/invoices/export/' . str_replace(request()->url(), '',request()->fullUrl()) )}}"><i class="fas fa-download"></i> Esporta</a>
                                        <a class="dropdown-item" title="importa fattura da xml" href="{{url('api/invoices/import')}}"><i class="fas fa-upload"></i> Importa Xml</a>
                                    </div>
                                </div>
                            @endif
                            <a class="btn btn-primary" href="{{url('invoices/create')}}"><i class="fas fa-plus"></i> Crea Fattura</a>
                            @if(\Areaseb\Core\Models\Setting::emailFatture())

                                @php
                                    $invoiceSetting = \Areaseb\Core\Models\Setting::where('model', 'Fatture')->first();
                                @endphp

                                <a title="personalizza email" class="btn btn-default" href="{{url('settings/'.$invoiceSetting->id.'/edit')}}"><i class="fas fa-cog"></i></a>
                            @endif

                        @endcan
                    </div>

                </div>
                <div class="card-body">

                    @include('areaseb::core.accounting.invoices.components.search', ['url' => url('invoices')])
                    <div class="table-responsive">
                        <table id="table" class="table table-sm table-font-xs table-bordered table-striped table-php">
                            <thead>
                                <tr>
                                    <th data-field="tipo" data-order="asc" style="width:70px;">Tipo <i class="fas fa-sort"></i></th>
                                    <th data-field="numero" data-order="asc" style="width:73px;">Numero <i class="fas fa-sort"></i></th>
                                    <th data-field="data" data-order="asc" style="width:77px;">Data <i class="fas fa-sort"></i></th>
                                    <th>Ragione Sociale</th>
                                    <th data-field="imponibile" data-order="asc">Imponibile <i class="fas fa-sort"></i></th>
                                    <th class="d-none d-xl-table-cell" style="width:55px;">Imp.</th>
                                    <th>Tot.</th>
                                    <th data-field="data_scadenza" data-order="asc" style="width:77px;">Scadenza <i class="fas fa-sort"></i></th>
                                    <th class="d-none d-xl-table-cell">Pagamento</th>
                                    <th style="width:55px;">Saldato</th>
                                    @if(config('core.modules')['fe'])
                                        <th >Status</th>
                                    @endif
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                    <tr id="row-{{$invoice->id}}">
                                        <td class="text-center">{{$invoice->tipo_formatted}}</td>
                                        <td class="text-center">{{$invoice->numero}}</td>
                                        <td>{{$invoice->data->format('d/m/Y')}}</td>
                                        <td>{{$invoice->company->rag_soc}}</td>
                                        <td>{{$invoice->imponibile_formatted}}</td>
                                        <td class="d-none d-xl-table-cell" class="text-center">{{$invoice->percent_iva}}</td>
                                        <td>{{$invoice->total_formatted}}</td>
                                        <td>{{$invoice->data_scadenza->format('d/m/Y')}}</td>
                                        <td class="d-none d-xl-table-cell">{{$invoice->tipo_pagamento}}</td>
                                        <td class="text-center">
                                            @if(($invoice->tipo == 'F') || ($invoice->tipo == 'A'))
                                                @if($invoice->payment_status)
                                                    <a href="{{route('invoices.payments.show', $invoice->id)}}" class="btn btn-default btn-sm" style="background-color:{{$invoice->payment_color}}; color:#000;">
                                                        {{$invoice->payment_status}}%
                                                    </a>
                                                @else
                                                    <a href="{{route('invoices.payments.show', $invoice->id)}}" class="btn btn-default">
                                                        @if($invoice->saldato)
                                                            <i class="fa text-success fa-check"></i>
                                                        @else
                                                            <i class="fa text-danger fa-times"></i>
                                                        @endif
                                                    </a>
                                                @endif
                                            @endif
                                        </td>
                                        @if(config('core.modules')['fe'])
                                            @if($invoice->tipo != 'P')
                                                <td class="text-center">{!!$invoice->status_formatted!!}</td>
                                            @else
                                                <td></td>
                                            @endif
                                        @endif
                                        <td class="text-center">
                                            @include('areaseb::core.accounting.invoices.components.index-actions')
                                            @includeIf('fe.fatture-in-cloud')
                                        </td>
                                    </tr>

                                @endforeach
                            </tbody>
                        </table>
                    <div class="table-responsive">

                </div>
                <div class="card-footer text-center">
                    <p class="text-left text-muted">{{$invoices->count()}} of {{ $invoices->total() }} fatture</p>
                    {{ $invoices->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
@if(request()->input())
    @include('areaseb::core.accounting.invoices.components.stats-bottom-nograph')
@else
    @include('areaseb::core.accounting.invoices.components.stats-bottom')
    @include('areaseb::core.accounting.invoices.components.stats-months')
@endif


@stop

@section('scripts')
<script>
// $('#customSwitch1').on('change', function(){
//     if($(this).prop('checked') === true)
//     {
//         $('#advancedSearchBox').removeClass('d-none');
//     }
//     else
//     {
//         $('#advancedSearchBox').addClass('d-none');
//     }
// });

$('select.custom-select').on('change', function(){
    $('#formFilter').submit();
});

$('#refresh').on('click', function(e){
    e.preventDefault();
    let currentUrl = window.location.href;
    let arr = currentUrl.split('?');
    window.location.href = arr[0];
});

$('a.notice').on('click', function(e){
    e.preventDefault();
    $('form#noticeForm-'+$(this).attr('data-id')).submit()
});


@if(config('core.modules')['fe'])
    $('a.sendFe').on('click', function(e){
        e.preventDefault();
        let id = $(this).attr('data-id');
        $.get(baseURL+'api/invoices/'+id+'/check', function( response ){
            if(response.status !== true)
            {
                window.location.href = baseURL+"companies/"+response.id+"/edit?q="+response.field;
            }
            else
            {
                $('form#sendFe'+id)[0].submit();
            }
        });
    });


    $('a.exportFe').on('click', function(e){
        e.preventDefault();
        let id = $(this).attr('data-id');
        let base = baseURL+'invoices/'+id+'/export';
        axios.post(base, {_token:token}).then(response => {
            if(response.data != 'done')
            {
                new Noty({
                    text: response.data,
                    type: 'error',
                    theme: 'bootstrap-v4',
                    layout: 'topRight'
                }).show();
            }
            $('form#exportFe'+id).submit();
        });
    });

@endif

$('a.duplicate').on('click', function(e){
    e.preventDefault();
    let id = $(this).attr('data-id');
    $('form#dform-'+id).submit();
});

$('a.feNoSet').on('click', function(e){
    e.preventDefault();
    new Noty({
        text: "Non hai scelto il connettore per la fattura elettronica",
        type: 'error',
        theme: 'bootstrap-v4',
        timeout: 2500,
        layout: 'topRight'
    }).show();
});


$('input.switch').on('click', function (e){
    window.location.href = baseURL+'payments/'+$(this).attr('data-id');
    e.preventDefault();
});

// $('a.export').on('click', function(e){
//     e.preventDefault();
//     console.log('ciaoo');
// });

$('a.sendToClient').on('click', function(e){
    e.preventDefault();
    let token = "{{csrf_token()}}";
    $.post(baseURL+'pdf/send/'+$(this).attr('data-id'), {_token: token}).done(function( response ) {
        console.log(response);
        if(response == 'done')
        {
            new Noty({
                text: "Email Inviata",
                type: 'success',
                theme: 'bootstrap-v4',
                timeout: 2500,
                layout: 'topRight'
            }).show();
        }
        else if(response == 'error')
        {
            new Noty({
                text: "Errore",
                type: 'error',
                theme: 'bootstrap-v4',
                timeout: 2500,
                layout: 'topRight'
            }).show();
        }
        else
        {
            new Noty({
                text: response,
                type: 'warning',
                theme: 'bootstrap-v4',
                timeout: 2500,
                layout: 'topRight'
            }).show();
        }
    });
});

</script>

@stop
