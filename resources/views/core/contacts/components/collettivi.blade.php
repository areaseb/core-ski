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
                                    <a class="btn btn-primary" href="#" onclick="scegliOperazione(0)"> Fattura i Collettivi aperti SELEZIONATI</a>
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
                                    <th>Collettivo</th>
									<th>Data</th>
									<th>Ora</th>
									<th>Disciplina</th>
									<th>Maestro</th>
                                    <th>Centro costo</th>
                                    <th>Importo</th>
									<th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            	@if(isset($coll))
	                                @foreach($coll as $dd)
	                                <tr>
										<td> <a href="/collective/{{$dd->collett_id}}">{{$dd->collettivo}}</a></td>
										<td> 
											@foreach($dd->date->distinct()->orderBy('giorno')->pluck('giorno')->toArray() as $data)
												{{ date("d/m/Y", strtotime($data))}}<br>
											@endforeach
										</td>
										<td> {{ 'dalle '.substr($dd->ora_in_inv,0,5).' alle '.substr($dd->ora_out_inv,0,5)}}</td>
										<td> {{$dd->disciplina_desc}}</td>
										<td> {!! $dd->maestri!!}</td>
	                                    <td> {{$dd->sede_lbl}}</td>
										<td>
											@if(isset($dd->saldo))
												Importo: € {{number_format($dd->importo, 2, ',', '.')}}<br>
												Acconto 1: € {{number_format($dd->acc_1, 2, ',', '.')}}<br>
												Acconto 2: € {{number_format($dd->acc_2, 2, ',', '.')}}<br>
												Saldo: € {{number_format($dd->saldo, 2, ',', '.')}}
											@endif	
										</td>
										<td>
											<a href="/collective/{{$dd->collett_id}}" class="btn btn-primary btn-icon btn-sm"><i class="fas fa-users"></i></a>
											@if($dd->id)
												<a href="/invoices/{{$dd->id}}" class="btn btn-secondary btn-icon btn-sm"><i class="fa fa-eye"></i></a>
											@endif
											<a href="#" onclick="openModalDoc({{$dd->sede_id}},{{$dd->hours}},'{{$dd->sede_lbl}}','{{$dd->id}}')" class="btn btn-success btn-icon btn-sm"><i class="fa fa-file"></i></a>
									
										</td>
	                                    <td>
											<input type="checkbox" id="{{$dd->item_id}}" data-oraid="{{$dd->hours.'*T_'.$contact->id}}" data-invoiceid="{{$dd->id}}" class="cbOp">
										</td>
	                                </tr>
	                                
	                                @endforeach
	                            @endif
                                
                            </tbody>
                        </table>
                    </div>
                </div>



            </div>
        </div>
    </div>