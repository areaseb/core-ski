@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Insoluti'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">
                    <h3 class="card-title">Insoluti</h3>
                    <div class="card-tools">
                        <div class="form-group mr-3 mb-0 mt-2" style="float:left;">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="customSwitch1" @if(request()->input()) checked @endif>
                                <label class="custom-control-label" for="customSwitch1">Ricerca Avanzata</label>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-body">

                    @include('areaseb::core.accounting.invoices.components.search', ['url' => url('insoluti')])
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
                                    <th >Status</th>
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
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input switch" data-id="{{$invoice->id}}" id="customSwitch-{{$invoice->id}}" @if($invoice->saldato) checked @endif>
                                                <label class="custom-control-label saldato" for="customSwitch-{{$invoice->id}}"></label>
                                            </div>
                                            <a href="{{url('invoices/'.$invoice->id.'/edit-saldo')}}" id="mod-{{$invoice->id}}" data-title="Inserisci Saldo" data-toggle="modal" data-target="#modal" class="btn btn-default btn-sm btn-modal d-none">Crea Ruolo</a>
                                        </td>
                                        <td class="text-center">{!!$invoice->status_formatted!!}</td>
                                        <td class="text-center">
                                            @include('areaseb::core.accounting.invoices.components.index-actions')
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

    @include('areaseb::core.accounting.invoices.components.stats-insoluti')



@stop

@section('scripts')
<script>
$('#customSwitch1').on('change', function(){
    if($(this).prop('checked') === true)
    {
        $('#advancedSearchBox').removeClass('d-none');
    }
    else
    {
        $('#advancedSearchBox').addClass('d-none');
    }
});

$('select.custom-select').on('change', function(){
    $('#formFilter').submit();
});

$('#refresh').on('click', function(e){
    e.preventDefault();
    let currentUrl = window.location.href;
    let arr = currentUrl.split('?');
    window.location.href = arr[0];
});



$('input.switch').on('click', function (e){

    $("#mod-"+$(this).attr('data-id')).trigger('click');
    $('#data_saldo').datetimepicker({ format: 'MM/DD/YYYY' });

    let data = {
        saldato: 0,
        _token: "{{csrf_token()}}",
        id: $(this).attr('data-id')
    };
    if($(this).is(":checked"))
    {
        data.saldato = 1;
    }
    $.post(baseURL+'api/invoices/saldato', data).done(function( response ) {
        new Noty({
            text: response,
            type: 'success',
            theme: 'bootstrap-v4',
            timeout: 2500,
            layout: 'topRight'
        }).show();
    });
});

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
