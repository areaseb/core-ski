<div class="row">
    <div class="col">
        <div class="card card-outline card-primary collapsed-card">
            <div class="card-header">
            	@if($invoice->company_id)
                	<h3 class="card-title">Riferimento Fattura</h3>
                @elseif($invoice->contact_id)
                	<h3 class="card-title">Riferimento Ricevuta</h3>
                @endif
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table text-center mb-0">
                        <thead>
                            <tr>
                                <th style="border-top:none;">N.</th>
                                <th style="border-top:none;">Data</th>
                                <th style="border-top:none;">Scadenza</th>
                                <th style="border-top:none;">Imponibile</th>
                                <th style="border-top:none;">IVA</th>
                                <th style="border-top:none;">Totale</th>
                                <th style="border-top:none;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{$invoice->numero}}</td>
                                <td>@if($invoice->data){{$invoice->data->format('d/m/Y')}}@endif</td>
                                <td>@if($invoice->data_scadenza){{$invoice->data_scadenza->format('d/m/Y')}}@endif</td>
                                <td>{{$invoice->imponibile_formatted}}</td>
                                <td>€ {{number_format($invoice->iva, 2, ',', '.')}}</td>
                                <td>{{$invoice->total_formatted}}</td>
                                <td><a href="{{route('invoices.show', $invoice->id)}}" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i> Dettagli</a> </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
