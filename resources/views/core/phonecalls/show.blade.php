@extends('areaseb::layouts.basic')

@section('meta_title')
<title>{{$company->rag_soc}}</title>
@stop

@section('content')

<div class="row">
    <div class="col-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dati Azienda</h3>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">Ragione Sociale <b>{{$company->rag_soc}}</b></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Contatto <b>{{$company->contacts()->first()->fullname}}</b></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Telefono <b>{{$company->phone}}</b></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Cellulare <b>{{$company->mobile}}</b></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Email <b>{{$company->email}}</b></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Note</h3>
            </div>
            <div class="card-body">

                @include('areaseb::core.notes.index')

                <div class="row">
                    <div class="col"></div>
                    <div class="col"><a href="{{route('notes.create')}}?company_id={{$company->id}}" data-title="Aggiungi nota" class="btn btn-primary btn-block btn-modal"> Aggiungi Nota</a></div>
                    <div class="col"></div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="row">
    @if($company->invoices()->exists())
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Fatture</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-font-xs table-bordered table-striped expandable">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Numero</th>
                                    <th>Data</th>
                                    <th>Imponibile</th>
                                    <th>Tot.</th>
                                    <th>Scadenza</th>
                                    <th>Saldato</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($company->invoices()->orderBy('data','DESC')->get() as $invoice)
                                    <tr id="row-{{$invoice->id}}">
                                        <td class="text-center">{{$invoice->tipo}}</td>
                                        <td class="text-center">{{$invoice->numero}}</td>
                                        <td>{{$invoice->data->format('d/m/Y')}}</td>
                                        <td>{{$invoice->imponibile_formatted}}</td>
                                        <td>{{$invoice->total_formatted}}</td>
                                        <td>{{$invoice->data_scadenza->format('d/m/Y')}}</td>
                                        <td class="text-center">
                                            @if($invoice->saldato)
                                                Sì
                                            @else
                                                No
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="d-none" id="row-{{$invoice->id}}-expand">
                                        <td colspan="2" class="text-center"><b>Prodotti</b></td>
                                        <td colspan="7">
                                            @foreach($invoice->items as $item)
                                                <p style="margin:3px;">{{$item->qta}} x {{$item->product->nome}} <small>({{$item->product->descrizione}})</small> Tot: € {{number_format($item->importo+$item->iva, '2', ',', '.')}}</p>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(\Illuminate\Support\Facades\Schema::hasTable('killer_quotes'))
        @if(\KillerQuote\App\Models\KillerQuote::where('company_id', $company->id)->exists())
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Preventivi</h3>
                    </div>
                    <div class="card-body">
                        @foreach(\KillerQuote\App\Models\KillerQuote::where('company_id', $company->id)->get() as $quote)
                            {{$quote->id}}
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif

</div>

@stop



@section('scripts')
    <script>
        $('table.expandable tr').on('click', function(){
            let rowName = $(this).attr('id');
            $('tr#'+rowName+'-expand').toggleClass('d-none');
        });
    </script>
@stop
