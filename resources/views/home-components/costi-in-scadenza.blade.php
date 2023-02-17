@php
$totCosts = 0;
@endphp

<div class="col-sm-6">
    <div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title">Costi in scadenza da saldare</h3>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <thead>
                <th>Numero</th>
                <th>Fornitore</th>
                <th>Scadenza</th>
                <th>Totale</th>
            </thead>
            <tbody>
                @foreach(Areaseb\Core\Models\Cost::inScadenzaPrev(90) as $cost)
                    @php
                        $payments = 0;
                        if(\Areaseb\Core\Models\CostPayment::where('cost_id', $cost->id)->exists())
                        {
                            $payments = \Areaseb\Core\Models\CostPayment::where('cost_id', $cost->id)->sum('amount');
                        }
                        $tot = $cost->totale - $payments;
                        $totCosts += $tot;
                    @endphp
                    <tr>
                        <td>{{$cost->numero}}</td>
                        <td>{{$cost->company->rag_soc}}</td>
                        <td>{{$cost->data_scadenza->format('d/m/Y')}}</td>
                        <td>
                            € {{number_format($tot, 2, ',', '.')}}
                            @if($payments) <del>{{$cost->totale_formatted}}</del> @endif
                        </td>
                    </tr>
                @endforeach
                @if($totCosts)
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="text-center"><b>Totale</b></td>
                        <td>€ {{number_format($totCosts,2,',','.')}}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    </div>
</div>
