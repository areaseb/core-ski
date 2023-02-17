@extends('areaseb::pdf.invoices.layout')


<div class="container">
    <div class="row">
        <div class="col-xs-4">
            <img class="img-responsive" src="{{Areaseb\Core\Models\Setting::FatturaLogo()}}" style="max-width:96%;text-align: left; padding-top:30px;">
        </div>
        <div class="col-xs-4"></div>
        <div class="col-xs-4">
            <br><br>
            <p>Spett.<br><b>{{$invoice->company->rag_soc}}</b></p>
            <p>{{$invoice->company->address}}<br>
            {{$invoice->company->zip}} {{$invoice->company->city}},
                @if($invoice->company->nation == 'IT')
                    ({{$invoice->company->city()->first()->sigla_provincia}})
                @endif
             <br>
            {{$invoice->company->nation}}</p>
        </div>
    </div>
</div>

<div class="container">

    <div class="row mt-4">
        <div class="col-xs-3 p-1 pl-3 border border-bottom-0 border-right-0"><strong>{{$invoice->titolo}}</strong></div>
        <div class="col-xs-2 p-1 pl-3 border border-bottom-0 border-right-0"><strong>Data: {{$invoice->data->format('d/m/Y')}}</strong></div>
        <div class="col-xs-4 p-1 pl-3 border border-bottom-0 border-right-0"><strong>P.IVA / C.F.: @if($invoice->company->private) {{$invoice->company->cf}} @else {{$invoice->company->piva}} @endif </strong></div>
        <div class="col-xs-3 p-1 pl-3 border border-bottom-0"><strong>Codice SDI: {{$invoice->company->sdi}}</strong></div>
    </div>

    <div class="row">
        <div class="col-xs-5 p-1 pl-3 border border-right-0"><strong>Riferimento: {{$invoice->riferimento}}</strong></div>
        <div class="col-xs-7 p-1 pl-3 border"><strong>Tipo di pagamento: {{$invoice->tipo_pagamento}}</strong></div>
    </div>

</div>

@php
    $discount = 0;
    foreach($invoice->items as $item)
    {
        $discount += $item->sconto;
    }
@endphp

<div class="container">
    <div class="row">
        <div style="overflow: hidden;">
            <table class="table table-sm mt-3">
                <thead class="mb-3">
                    <tr class="blue">
                        <th class="c30 pl-3">Descrizione</th>
                        <th class="c10">Quantit&agrave;</th>
                        <th class="c15">Prezzo</th>
                        @if($discount > 0)
                            <th class="c10">Sconto %</th>
                        @endif
                        <th class="c15">Tot. riga</th>
                        <th class="c10">IVA%</th>
                        <th class="c10">IVA</th>
                    </tr>
                </thead>

                <tbody style="border-top:10px solid #fff;">
                    <tr><td colspan="7" class="bb bt p-0 h0"></td></tr>
                    @foreach($invoice->items as $item)
                        <tr class="pt-5 pb-5 h50">
                            <td class="c30 pl-3 border-top-0 bl">
                                <b>{{$item->product->nome}}</b><br>
                                <span class="text-muted fsSmall">{{$item->descrizione}}</span>
                            </td>
                            <td class="c10 border-top-0 blr fsSmaller">{{$item->qta}}</td>
                            <td class="c15 border-top-0 br fsSmaller">&euro; {{$item->importo_decimal}} </td>
                            @if($discount > 0)
                                <td class="c10 border-top-0 blr fsSmaller">{{round($item->sconto, 2)}}</td>
                            @endif
                            <td class="c15 border-top-0 fsSmaller">&euro; {{$item->totale_riga_decimal}}</td>
                            <td class="c10 border-top-0 bl fsSmaller">{{number_format($item->perc_iva, 2, ',', '')}}</td>
                            <td class="c15 border-top-0 blr fsSmaller">&euro; {{$item->iva_decimal}}</td>
                        </tr>
                    @endforeach
                    {{-- @for($x = 0; $x < (6-count($invoice->items)); $x++)
                        <tr>
                            <td class="border-top-0 bl"><br><br></td>
                            <td class="border-top-0 blr"><br><br></td>
                            <td class="border-top-0"><br><br></td>
                            <td class="border-top-0 blr"><br><br></td>
                            <td class="border-top-0 "><br><br></td>
                            <td class="border-top-0 bl"><br><br></td>
                            <td class="border-top-0 blr"><br><br></td>
                        </tr>
                    @endfor --}}
                    <tr><td colspan="7" class="bb bt p-0"></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="container">

    <div class="row mt-4">
        <div class="col-xs-4 p-1 pl-3 border border-bottom-0 border-right-0"><strong>Imponibile:</strong> &euro; {{number_format($invoice->imponibile, 2, ',', '.')}}</div>
        <div class="col-xs-5 p-1 pl-3 border border-bottom-0 border-right-0"><strong>Arrotondamento:</strong> € {{ number_format($invoice->rounding, 2, ',', '.') }}</div>
        <div class="col-xs-3 p-1 pl-3 border border-bottom-0">
            @if($invoice->spese)
                &nbsp;
            @else
                <strong>Spese incasso:</strong> 0
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-xs-4 p-1 pl-3 border border-right-0" style="min-height:111px;"><strong>Riepilogo IVA:</strong><br>
            <div class="row">
                @if($invoice->items()->whereNotNull('exemption_id')->exists())
                    @foreach($invoice->items_grouped_by_esenzione as $perc => $values)
                        <div class="col-xs-5">{{$values['imponibile']}}</div>
                        <div class="col-xs-7">
                            <strong>IVA {{$values['val']}}%:</strong> {{$values['iva']}}<br>
    						<i style="font-size:10px;">{{$values['exemption']}}</i>
                        </div>
                        <div class="col-xs-12" style="height: 1px;">&nbsp;</div>
                    @endforeach
                @else
                    @foreach($invoice->items_grouped_by_perc_iva as $perc => $values)
                        <div class="col-xs-5">&euro; {{$values['imponibile']}}</div>
                        <div class="col-xs-7"><strong>IVA {{$perc}}%:</strong> &euro; {{$values['iva']}} </div>
                    @endforeach
                @endif
            </div>
        </div>
        <div class="col-xs-2 p-1 pl-3 border border-right-0" style="min-height:111px;"><strong>Totale IVA:</strong> <br>
             &euro; {{$invoice->iva_decimal}}
            <br><br><br>
        </div>
        <div class="col-xs-6 p-1 pl-3 border"><strong>Netto a pagare:</strong><br>
            <h2 class="text-center" style="margin-top:22px; font-size:44px;"> {{$invoice->total_formatted}}</h2>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-xs-12 p-1 pl-3 border">
            <strong>Scadenze rate e relativo importo</strong><br><br>
            {{-- <strong>{{$invoice->data_scadenza->format('d/m/Y')}}:</strong> {{$invoice->total_formatted}} --}}

            @if($invoice->rate)

                @php
                    $rate = explode(',', $invoice->rate);
                    $n_rate = count($rate);

                    $total = number_format($invoice->total, 2, '.', '');
                    if($invoice->split_payment)
                    {
                        $total = number_format($invoice->imponibile, 2, '.', '');
                    }
                    $amount_rata = number_format($total / $n_rate, 2, '.', '');
                    $amount_payed = 0;
                @endphp

                @foreach($rate as $value)

                    @php
                        $scadenza_rata = \Carbon\Carbon::createFromFormat('d/m/Y', trim($value))->format('d/m/Y');

                    @endphp

                    <strong>{{$scadenza_rata}}:</strong>

                    @if($loop->last)
                        &euro; {{number_format($total - $amount_payed , 2, ',', '.')}}
                    @else
                        &euro; {{number_format($amount_rata, 2, ',', '.')}} |
                    @endif

                    @php
                        $amount_payed += $amount_rata;
                    @endphp

                @endforeach

            @else
                <strong>{{$invoice->data_scadenza->format('d/m/Y')}}: </strong>

                @if($invoice->split_payment)
                    {{ $invoice->imponibile_formatted }}
                @else
                    {{ $invoice->total_formatted }}
                @endif

            @endif
        </div>
    </div>

</div>

{{-- @stop --}}
