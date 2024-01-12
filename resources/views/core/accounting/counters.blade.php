@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Contaore'])

@section('content')
    <div class="row">
    	<div class="col-12">
    		<div class="card">
                <div class="card-body">
                	<div class="row">
		        		<div class="col-md-6">
		            
		                    {!! Form::open(['url' => route('counters.in'), 'autocomplete' => 'off', 'id' => 'ingresso']) !!}
					            <button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm" style="margin-top: 3px; margin-bottom: 3px;">
									Ingresso
								</button>
			            	{!! Form::close() !!} 
				                     
		                </div>
		                <div class="col-md-6">
		            
		                    {!! Form::open(['url' => route('counters.out'), 'autocomplete' => 'off', 'id' => 'uscita']) !!}
					            <button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm" style="margin-top: 3px; margin-bottom: 3px;">
									Uscita
								</button>
			            	{!! Form::close() !!} 
			            	
		                </div>
		            </div>
	            </div>	    
	        </div>
	    </div>
    </div>
    
    @if($user->hasRole('super'))
    <div class="row">
    	<div class="col-12">
    		<div class="card">
                <div class="card-body">
                	<div class="row">
		        		<div class="col-md-6">
		            
		                    &nbsp;
				                     
		                </div>
		                <div class="col-md-6">
		                	<div class="row">
		        				<div class="col-md-6">
		        					
		        					@php
		        						for($a = date('Y'); $a >= 2018; $a--){
		        							$anni[$a] = $a;
		        						}
		        					@endphp
		        					
				                    {!! Form::open(['url' => route('counters'), 'autocomplete' => 'off', 'id' => 'filtro', 'method' => 'get']) !!}
				                    	{!! Form::select('utente', ['' => '- Scegli utente -'] + $users, request('utente') ?? $user->id, ['class' => 'form-control']) !!}
				                    	{!! Form::select('mese', ['' => '- Scegli mese -', '01' => 'Gennaio', '02' => 'Febbraio', '03' => 'Marzo', '04' => 'Aprile', '05' => 'Maggio', '06' => 'Giugno', '07' => 'Luglio', '08' => 'Agosto', '09' => 'Settembre', '10' => 'Ottobre', '11' => 'Novembre', '12' => 'Dicembre'], request('mese') ?? date('m'), ['class' => 'form-control']) !!}
				                    	{!! Form::select('anno', ['' => '- Scegli anno -'] + $anni, request('anno') ?? date('Y'), ['class' => 'form-control']) !!}
				                </div>
				                <div class="col-md-6">
							            <button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm" style="margin-top: 3px; margin-bottom: 3px;">
											Vai
										</button>
					            	{!! Form::close() !!} 
			            	
					            </div>
					        </div>
		                </div>
		            </div>
	            </div>	    
	        </div>
	    </div>
    </div>
    @endif
    
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-center">
                    <b>{{sprintf($user_name[0])}}</b>
                </div>
                <div class="card-body" style="min-height: 250px;">
                    
                	@php
                   	
					   	//Fa la differenza tra 2 ore in ore:minuti
						Function conta_ore($o_in, $o_out, $d_in, $d_out) {

						   $o_in = explode(":", $o_in);
						   $o_out = explode(":", $o_out);
						   $d_in = explode("-", $d_in);
						   $d_out = explode("-", $d_out);
						
						   $diff = mktime($o_out[0], $o_out[1], 0,$d_out[1],$d_out[2],$d_out[0]) - mktime($o_in[0], $o_in[1],0,$d_in[1],$d_in[2],$d_in[0]);
							 
							 $h = floor($diff / (60*60));
							 $m = (($diff / 60) % 60);
							 	
							 $ore = str_pad("$h", 2, "0", STR_PAD_LEFT);
							 $minuti = str_pad("$m", 2, "0", STR_PAD_LEFT);
								
						   $tempo = $ore.":".$minuti;
						
						   return $tempo;
						}
                   	
                	@endphp
                	
                	
                	<div class="table-responsive">
						<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th style="text-align: center">Giorno</th>
									<th style="text-align: center">Ingresso</th>
									<th style="text-align: center">Uscita</th>
									<th style="text-align: center">Ore</th>
								</tr>
							</thead>
							
							<tfoot>
								<tr>
									<th style="text-align: center">Giorno</th>
									<th style="text-align: center">Ingresso</th>
									<th style="text-align: center">Uscita</th>
									<th style="text-align: center">Ore</th>
								</tr>
							</tfoot>

							<tbody>
								
								@php
								
								$elenco_date = array();
								$elenco_ore = array();
													
								//tiro fuori dalla query il risultato
								$tot_ore = 0;
								$ore_settimana = 0;
								$intro = 0;
								$ore_mese = 0;
								$settimana = "";
								$mese_old = "";
								
						   		foreach($hours as $in => $out){		
							  		
							  		if($out == ""){
							  			$out = $in;
							  		}
								  		
							  		list($data_in, $ora_in) = explode(" ", $in);
							  		list($data_out, $ora_out) = explode(" ", $out);
							  		
							  		list($anno, $mese, $giorno) = explode("-", $data_in);
						  			
						  			list($h_in, $m_in) = explode(":", $ora_in);
								    list($h_out, $m_out) = explode(":", $ora_out);
										
									$ore_diff = conta_ore($ora_in, $ora_out, $data_in, $data_out);	//ore sessaggesimali
									
									list($ore_d, $minuti_d) = explode(":", $ore_diff);
									$minuti_cento = $minuti_d / 60;
									
									$diff = $ore_d + $minuti_cento;		//"$ore_d.$minuti_cento";
									
									$tot_ore = $tot_ore + $diff;
									$ore_settimana = $ore_settimana + $diff;
										
																	
									//conto settimanale									
									if(date("W", mktime(0, 0, 0, $mese, $giorno, $anno)) != $settimana && $settimana != ""){	//settimana dell'anno diversa o domenica
										
										//se entra nell'if per differenza di settimana devo togliere le ore del giorno corrente perchè sono di proprietà della nuova settimana
										if(date("W", mktime(0, 0, 0, $mese, $giorno, $anno)) != $settimana){
											$ore_sett = $ore_settimana - $diff;
											$ore_settimana = 0;	
											$ore_settimana = $ore_settimana + $diff;
											$giorno_sett = date("w", mktime(0, 0, 0, $mese, $giorno-6, $anno));
										} else {
											$ore_sett = $ore_settimana;
											$ore_settimana = 0;	
											$giorno_sett = date("w", mktime(0, 0, 0, $mese, $giorno, $anno));
										}
									
										switch($giorno_sett){
											case 0:
												$inizio_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno+2, $anno));
												$fine_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno+8, $anno));
												break;
											case 1:
												$inizio_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno+1, $anno));
												$fine_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno+7, $anno));
												break;
											case 2:
												$inizio_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno, $anno));
												$fine_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno+6, $anno));
												break;
											case 3:
												$inizio_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno-1, $anno));
												$fine_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno+5, $anno));
												break;
											case 4:
												$inizio_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno-2, $anno));
												$fine_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno+4, $anno));
												break;
											case 5:
												$inizio_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno-3, $anno));
												$fine_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno+3, $anno));
												break;
											case 6:
												$inizio_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno-4, $anno));
												$fine_sett = date('d.m.Y', mktime(0,0,0, $mese, $giorno+2, $anno));
												break;
										}
										
										$ore_sett = number_format($ore_sett, 2, ",", ".");
																				
										print"<tr style=\"background-color: #cccccc;\">
														<td align=\"left\">
															<b style=\"color: black;\">settimana dal $inizio_sett al $fine_sett</b>
														</td>													
														<td>
															&nbsp;
														</td>
														<td>
															&nbsp;
														</td>
														<td align=\"center\">
															<b style=\"color: black;\">$ore_sett</b>
														</td>
													</tr>";
													
										$intro = 0;
									}	else {
										$intro = 1;
									}	
									
									if($mese == $mese_old){
										$ore_mese += $diff;
									} elseif($mese != $mese_old && $mese_old != "") {
										$ore_mese = number_format($ore_mese, 2, ",", ".");
										
										print"<tr style=\"background-color: #000066\">
														<td align=\"left\">
															<b>totale mese</b>
														</td>													
														<td>
															&nbsp;
														</td>
														<td>
															&nbsp;
														</td>
														<td align=\"center\">
															$ore_mese
														</td>
													</tr>";
										
										$ore_mese = 0;	
										$ore_mese += $diff;		
									}
									
									$settimana = date("W", mktime(0, 0, 0, $mese, $giorno, $anno));
									
									if("$h_in:$m_in" == "$h_out:$m_out"){
										$h_out = "";
										$m_out = "";
									}
																			
							  		print"<tr>
														<td align=\"center\">
															$giorno.$mese.$anno
														</td>
														<td align=\"center\">
															$h_in:$m_in
														</td>
														<td align=\"center\">
															$h_out:$m_out
														</td>
														<td align=\"center\">
															$ore_diff
														</td>
													</tr>";
							  		
							  		$mese_old = $mese;
						  		}
						  		
						  							  		
						  		$tot_ore = number_format($tot_ore, 2, ",", ".");
						  		$settimana = "";
						  		$ore_mese = 0;
						  		$mese_old = "";
						  		
						  		@endphp
									  		
								<tr>
									<td align="left">
										<b>Totale ore</b>
									</td>
									<td>
										&nbsp;
									</td>
									<td>
										&nbsp;
									</td>
									<td align="center">
										<b>{{$tot_ore}}</b>
									</td>
								</tr>
							</tbody>
						</table>
					</div>                	
                    
                </div>
                <div class="card-footer text-center">
                    &nbsp;
                </div>
            </div>
        </div>
    </div>
@stop

