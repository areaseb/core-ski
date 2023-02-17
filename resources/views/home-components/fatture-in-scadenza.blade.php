@php
$totFatture = 0;
@endphp

<div class="col-sm-6">
    <div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title">Fatture clienti in scadenza</h3>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <thead>
                <th>Numero</th>
                <th>Cliente</th>
                <th>Scadenza</th>
                <th>Totale</th>
            </thead>
            <tbody>
                @foreach(Areaseb\Core\Models\Invoice::inScadenzaPrev(30) as $invoice)
                    @php
                        $totFatture += $invoice->imponibile+$invoice->iva;
                    @endphp
                    <tr>
                        <td>{{$invoice->numero}}</td>
                        <td>{{$invoice->company->rag_soc}}</td>
                        <td>{{$invoice->data_scadenza->format('d/m/Y')}}</td>
                        <td>{{Areaseb\Core\Models\Primitive::NF($invoice->imponibile+$invoice->iva)}}</td>
                    </tr>
                @endforeach
                @if($totFatture)
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="text-center"><b>Totale</b></td>
                        <td>€ {{number_format($totFatture,2,',','.')}}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    </div>
</div>
