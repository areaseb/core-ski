<div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">
				<div class="row">
                        <div class="col-6">
                        </div>
                        <div class="col-6 text-right">

                            <div class="card-tools">

                                <div class="btn-group" role="group">
                                    <a class="btn btn-primary" href="#" onclick="scegliOperazione(0)"> Fattura le ore aperte SELEZIONATE</a>
									<a class="btn btn-primary" href="#" onclick="scegliOperazione(1)" style="margin-left:10px">Fattura TUTTO ciò che è aperto</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <br>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-php">
                            <thead>
                                <tr>
									<th>Data</th>
									<th>Ora</th>
                                    <th>Ore</th>
                                    <th>Ritrovo</th>
                                    <th>Pax</th>
									<th>Disciplina</th>
									<th>Maestro</th>
                                    <th>Note lezione</th>
									<th>Importo</th>
									<th></th>
									<th></th>
                                </tr>
                            </thead>
                            <tbody>
                            	@php
                            		$tot_ore = 0;
                            	@endphp
                            	
                            	@if(isset($ore))
	                                @foreach($ore->sortBy([['data', 'asc'],['ora_in', 'asc']]) as $dd)
	                                
		                                @php	                               
		                                	if($dd->ora_in != '' && $dd->ora_out != '' && substr($dd->id_cliente, 0, 1) != 'L') {
												$hour_a = \Carbon\Carbon::createFromFormat('H:i:s', $dd->ora_in);
												$hour_b = \Carbon\Carbon::createFromFormat('H:i:s', $dd->ora_out);

												$tot_ore += $hour_a->diffInSeconds($hour_b);
											}
											
											$client_invoices = \Areaseb\Core\Models\Invoice::where('company_id', $company->id)->pluck('id')->toArray();
											$item = \Areaseb\Core\Models\Item::where('ora_id', $dd->id)->whereIn('invoice_id', $client_invoices)->first();
											if($item){
												$invoice_id = $item->invoice_id;
												$item_id = $item->id;
											} else {
												$invoice_id = null;
												$item_id = null;
											}
											
		                                @endphp
		                                
		                                <tr>
		                                    <td> <a href="/planning?day={{ date("d-m-Y", strtotime($dd->data))}}#{{$dd->id_maestro}}">{{ date("d/m/Y", strtotime($dd->data))}}</a></td>
											<td> {{ 'dalle '.substr($dd->ora_in,0,5).' alle '.substr($dd->ora_out,0,5)}}</td>
		                                    <td> {{$dd->datediff}}</td>
		                                    <td> {{$dd->ritrovo}}</td>
		                                    <td> {{$dd->pax}}</td>
											<td> {{$dd->disciplina_desc}}</td>
											<td> {{$dd->maestro}}</td>
		                                    <td> {{$dd->note}}</td>
											<td> {{'€ '.$dd->importo}}</td>
											<td>
												@if($invoice_id)
		                                        	<a href="/invoices/{{$invoice_id}}" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-eye"></i></a>
		                                        	<a href="/invoices/{{$invoice_id}}/edit" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
		                                       	@endif
												<a href="#" onclick="openModalDoc({{$dd->id_cc}},{{$dd->id}},'{{$dd->sede_lbl}}','{{$invoice_id}}')" class="btn btn-success btn-icon btn-sm" title="Aggiungi voce a documento"><i class="fa fa-file"></i></a>
												
											</td>
											<td>
												<input type="checkbox" id="{{$item_id}}" data-oraid="{{$dd->id}}" data-invoiceid="{{$invoice_id}}" class="cbOp">
											</td>
		                                </tr>
	                                
	                                @endforeach
	                            @endif
                                
                                <tr>
                                	<td><b>TOTALE</b></td>
                                	<td colspan="10"><b>{!! $tot_ore / 60 / 60 !!} ore</b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>



				

                

            </div>
        </div>
</div>
