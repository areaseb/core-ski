<div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">
                </div>
                <div class="card-body">

                    <br>
                    <div class="table-responsive">
                    	
                    	<div id="accordion">
							<div class="card card-primary">
								<div class="card-header">
									<h4 class="card-title w-100">
										<a class="d-block w-100" data-toggle="collapse" href="#collapseOne" aria-expanded="true">
											Storico ore
										</a>
									</h4>
								</div>
								<div id="collapseOne" class="collapse show" data-parent="#accordion" style="">
									<div class="card-body">
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
													<th></th>
				                                </tr>
				                            </thead>
				                            <tbody>
				                            	@php
				                            		$tot_ore = 0;
				                            	@endphp
				                        		
				                                @foreach(\Areaseb\Core\Http\Controllers\ContactController::getStoricoOreCliente($contact->id)[0]->sortBy([['data', 'asc'],['ora_in', 'asc']]) as $dd)
				                                
				                                	@php	                               
					                                	if($dd->ora_in != '' && $dd->ora_out != '' && substr($dd->id_cliente, 0, 1) != 'L') {
															$hour_a = \Carbon\Carbon::createFromFormat('H:i:s', $dd->ora_in);
															$hour_b = \Carbon\Carbon::createFromFormat('H:i:s', $dd->ora_out);

															$tot_ore += $hour_a->diffInSeconds($hour_b);
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
														<td>
															@if($dd->invoice_id)
						                                        <a href="/invoices/{{$dd->invoice_id}}" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-eye"></i></a>
						                                    @endif						                                    
														</td>
					                                </tr>
				                                
				                                @endforeach
				                                
				                                <tr>
				                                	<td><b>TOTALE</b></td>
				                                	<td colspan="10"><b>{!! $tot_ore / 60 / 60 !!} ore</b></td>
				                                </tr>
				                            </tbody>
				                        </table>
									</div>
								</div>
							</div>
							<div class="card card-success">
								<div class="card-header">
									<h4 class="card-title w-100">
										<a class="d-block w-100" data-toggle="collapse" href="#collapseTwo">
											Storico collettivi
										</a>
									</h4>
								</div>
								<div id="collapseTwo" class="collapse" data-parent="#accordion">
									<div class="card-body">
										<table class="table table-sm table-bordered table-striped table-php">
				                            <thead>
				                                <tr>
				                                    <th>Collettivo</th>
													<th>Data</th>
													<th>Ora</th>
													<th>Disciplina</th>
													<th>Maestro</th>
				                                    <th>Sede</th>
													<th></th>
				                                </tr>
				                            </thead>
				                            <tbody>
				                            	
				                                @foreach(\Areaseb\Core\Http\Controllers\ContactController::getStoricoOreCliente($contact->id)[1]->sortBy([['data', 'asc'],['ora_in', 'asc']]) as $dd)
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
														<a href="/collective/{{$dd->collett_id}}" class="btn btn-primary btn-icon btn-sm"><i class="fas fa-users"></i></a>
														@if($dd->invoice_id)
															<a href="/invoices/{{$dd->invoice_id}}" class="btn btn-secondary btn-icon btn-sm"><i class="fa fa-eye"></i></a>
														@endif
													</td>
				                                </tr>
				                                
				                                @endforeach
				                                
				                            </tbody>
				                        </table>
									</div>
								</div>
							</div>
						</div>
                    	
                    	
                        
                    </div>
                </div>



				

                

            </div>
        </div>
</div>
