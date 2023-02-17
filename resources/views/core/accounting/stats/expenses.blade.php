@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Costi filtrati per '.$category->nome])

@section('content')
    @foreach($groupedCosts as $company_id => $costs)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    @php
                        $company = \Areaseb\Core\Models\Company::find($company_id);
                        $imponibile = 0;
                        $totale = 0;
                    @endphp
                    <div class="card-header bg-secondary-light">
                        <h3 class="card-title">{{$company->rag_soc}}</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped expenseTables mb-0">
                                <thead>
                                    <tr>
                                        <th>Numero</th>
                                        <th>Data </th>
                                        <th>Data Ric.</th>
                                        <th>Fornitore</th>
                                        <th>Prodotto</th>
                                        <th>Imponibile</th>
                                        <th>IVA</th>
                                        <th>Totale</th>
                                        <th>Scadenza</th>
                                        @can('costs.write')
                                            <th>Saldato</th>
                                        @endcan
                                        <th style="width:150px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($costs as $cost)

                                        <tr>
                                            <td>{{$cost->nome}}</td>
                                            <td>{{$cost->data->format('d/m/Y')}}</td>
                                            <td>
                                                @if(is_null($cost->data_ricezione))
                                                    {{$cost->data->format('d/m/Y')}}
                                                @else
                                                    {{$cost->data_ricezione->format('d/m/Y')}}
                                                @endif

                                            </td>
                                            <td><a class="defaultColor" href="{{$cost->company->url}}">{{$cost->company->rag_soc}}</a></td>
                                            <td>
                                                @if($cost->expense->is_default)
                                                    <b><span class="text-danger">{{$cost->expense->nome}}</span></b>
                                                @else
                                                    {{$cost->expense->nome}}
                                                @endif

                                            </td>

                                            @if($cost->imponibile < 0)
                                                <td class="bg-success disabled">{{$cost->imponibile_formatted}}</td>
                                            @else
                                                <td>{{$cost->imponibile_formatted}}</td>
                                            @endif

                                            <td>{{$cost->iva}}</td>

                                            @if($cost->totale < 0)
                                                <td class="bg-success disabled">{{$cost->totale_formatted}}</td>
                                            @else
                                                <td>{{$cost->totale_formatted}}</td>
                                            @endif

                                            <td>{{$cost->data_scadenza->format('d/m/Y')}}</td>
                                            @can('costs.write')
                                                <td class="text-center">
                                                    @if($cost->saldato)
                                                        <a href="{{route('costs.payments.show', $cost->id)}}" class="btn btn-default"><i class="fa text-success fa-check"></i></a>
                                                    @else
                                                        @if($cost->payment_status)
                                                            <a href="{{route('costs.payments.show', $cost->id)}}" class="btn btn-default btn-sm" style="background-color:{{$cost->payment_color}}; color:#000;">
                                                                {{$cost->payment_status}}%
                                                            </a>
                                                        @else
                                                            <a href="{{route('costs.payments.show', $cost->id)}}" class="btn btn-default"><i class="fa text-danger fa-times"></i></a>
                                                        @endif
                                                    @endif
                                                </td>
                                            @endcan

                                            <td class="pl-2">
                                                @can('costs.write')
                                                    <a href="{{$cost->url}}/edit" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                                    <a href="{{$cost->url}}/media" class="btn btn-info btn-icon btn-sm"><i class="fa fa-image"></i></a>
                                                @endcan
                                                @if(config('core.modules')['fe'])
                                                    @if($cost->media()->xml()->exists())
                                                        @if($cost->media()->pdf()->exists())
                                                            <a href="{{$cost->pdf}}" target="_BLANK" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-file-pdf"></i></a>
                                                        @else
                                                            <a href="{{url('pdf/costs/'.$cost->id)}}" target="_BLANK" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-file-pdf"></i></a>
                                                        @endif
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>

                                        @php
                                            $imponibile += $cost->imponibile;
                                            $totale += $cost->totale
                                        @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer p-0">
                        <div class="row text-right">
                            <div class="col p-3 text-right" style="font-size:150%">
                                <small style="font-weight:bolder;"><b>IMPONIBILE:</b></small> € {{ number_format($imponibile, 2, ',', '.') }}
                                <small style="font-weight:bolder;"><b>TOTALE:</b></small> € {{ number_format($totale, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endforeach

@stop


@section('scripts')

<script>

$('.select2A').select2({width: '100%', placeholder: 'Anno'});
$('.select2A').on('change', function(){
    let anno = $('.select2A').find(':selected').val();
    window.location.href = '?anno='+anno;
});

@stop
