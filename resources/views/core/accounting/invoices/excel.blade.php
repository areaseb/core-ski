<table>
    <thead>
        <tr>
            <th>Sede</th>
            <th>Tipo</th>
            <th>Numero</th>
            <th>Data</th>
            <th>Ragione Sociale</th>
            <th>Imponibile</th>
            <th>Imp.</th>
            <th>Tot.</th>
            <th>Scadenza</th>
            <th>Pagamento</th>
            <th>Saldato</th>
            <th >Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoices as $invoice)
            <tr>
            	@php
            		if($invoice->bollo){
						$imponibile = $invoice->imponibile - $invoice->bollo;
					} else {
						$imponibile = $invoice->imponibile;
					}
					
			        $totale = $imponibile - $invoice->ritenuta + $invoice->iva + $invoice->bollo;
            	@endphp
                <td>{!!$invoice->branch_name!!}</td>
                <td>{{$invoice->tipo_formatted}}</td>
                <td>{{$invoice->numero}}</td>
                <td>{{$invoice->data->format('d/m/Y')}}</td>
                <td>{{$invoice->company != null ? $invoice->company->rag_soc : ($invoice->contact($invoice->contact_id)->nome.' '.$invoice->contact($invoice->contact_id)->cognome .' (Contatto)')}}</td>
                <td>{{$imponibile}}</td>
                <td>{{$invoice->percent_iva}}</td>
                <td>{{$totale}}</td>
                <td>{{$invoice->data_scadenza != null ? $invoice->data_scadenza->format('d/m/Y') : ''}}</td>
                <td>{{config('invoice.payment_modes')[$invoice->tipo_saldo]}}</td>
                <td>
                    @if($invoice->payment_status)
                        {{$invoice->payment_status}}%
                    @else
                        @if($invoice->saldato)
                            Sì
                        @else
                            No
                        @endif
                    @endif
                </td>
                @if(config('core.modules')['fe'])
                    @if($invoice->tipo != 'P' && $invoice->tipo != 'R')
                        <td>{!!$invoice->status_formatted!!}</td>
                    @else
                        <td>&nbsp;</td>
                    @endif
                @endif
            </tr>

        @endforeach
    </tbody>
</table>
