@extends('areaseb::layouts.app')

@section('css')
<style>

</style>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Statistiche Maestro '.$maestro->contact->fullname])

@section('content')
    <div class="row">

        <div class="col-md-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Statistica Ore</h3>

                    <div class="card-tools">
                        <div class="row">
                        	<div class="col-md-3">
                        		{!!Form::open(['method' => 'GET', 'style' => 'display: inline'])!!}
                        		Anno
	                            {!!Form::select('year', [
	                                'tutti' => 'Totale',
	                                date('Y') => date('Y')." - ".date('Y') + 1,
	                                date('Y') - 1 => (date('Y') - 1)." - ".date('Y'),
	                                date('Y') - 2 => (date('Y') - 2)." - ".(date('Y') - 1),
	                                date('Y') - 3 => (date('Y') - 3)." - ".(date('Y') - 2),
	                            ], [request('year')], [ 'class'=> 'form-control form-control-sm selectYear'])!!}
                            </div>
                            <div class="col-md-3">
	                            Da <input type="date" name="data_in" value="{{request()->get('data_in')}}" class="form-control" id="data_in">
	                        </div>
	                        <div class="col-md-3">    
	                            A <input type="date" name="data_out" value="{{request()->get('data_out')}}" class="form-control" id="data_out">
	                        </div>
	                        <div class="col-md-3">    
	                            <br><button type="submit" class="btn btn-warning btn-lg" id="submitForm"><i class="fa fa-search"></i></button> 
	                            {!!Form::close()!!}
	                            @if(request()->get('data_in') || request()->get('data_out'))
	                            	<a href="@if(date('m') >= 6) {{url('stats/maestri?year='.date('Y'))}} @else {{url('stats/maestri?year='.(date('Y')-1))}} @endif"><button type="submit" class="btn btn-secondary btn-lg" id="submitForm"><i class="fa fa-redo"></i></button></a>
	                            @endif
	                        </div>
                        </div>
                    </div>

                </div>
                <div class="card-body">
                    <div class="row mt-5">
                        	
                        	@php
                    			switch($maestro->disciplina){
                    				case 1:
                    					$disc = 'Discesa';
                    					break;
                    				case 2:
                    				case 3:
                    					$disc = 'Fondo';
                    					break;
                    				case 4:
                    					$disc = 'Snowboard';
                    					break;
                    			}
                    		@endphp
                        	
                       	<div class="col-md-12 text-center ">
                       		<h3>{{$maestro->contact->fullname}}</h3>
                       		{{$disc}}
                       	</div> 	
                    </div>
                    <div class="row mt-5">
                       	<div class="col-md-12 col-xs-12">
							<table class="table">
								<thead>
	                				<tr>
	                					<th class="text-center">Data</th>
	                					<th class="text-center">Ora in</th>
	                					<th class="text-center">Ora out</th>
	                					<th class="text-center">Ore</th>
	                					<th class="text-center">Cliente</th>
	                					<th class="text-center">Sede</th>
	                				</tr>  
	                			</thead>
	                			<tbody>
                				
	                				@php
	                					$diff = array();
										$id_ctrl_ore = array();
										$tot_ore = array();
										$ore_giorno = array();
										$data_old = "";
	                				@endphp
	                				
	                				@foreach($ore as $ora)
	                				
	                					@php
	                						
	                						if(substr($ora->id_cliente, 0, 1) == "C"){
	                					
											  	$collettivo = Areaseb\Core\Models\Collettivo::find(substr($ora->id_cliente, 2));
											  	$id_cli = $collettivo->id;
											  	$nome_cli = $collettivo->nome;
									  		
									  		} elseif(substr($ora->id_cliente, 0, 1) == "Y"){
									  		
									  			$company = Areaseb\Core\Models\Company::find(substr($ora->id_cliente, 2));
											  	$id_cli = $company->id;
											  	$nome_cli = $company->rag_soc;
											  	
									  		} elseif(substr($ora->id_cliente, 0, 1) == "T"){
									  		
									  			$contact = Areaseb\Core\Models\Contact::find(substr($ora->id_cliente, 2));
											  	$id_cli = $contact->id;
											  	$nome_cli = $contact->fullname;
											  	
									  		}
	                				
		                					list($a, $m, $g) = explode("-", $ora->data);
			  								$data_ok = "$g/$m/$a";
								  		
									  		if(!in_array($ora->id, $id_ctrl_ore)){
									  		
										  		list($h_in, $m_in) = explode(":", $ora->ora_in);
											    list($h_out, $m_out) = explode(":", $ora->ora_out);
											    
											    $dif_ore = $h_out - $h_in;
											    $dif_minuti = $m_out - $m_in;
											    $dif_minuti_ok = ($dif_minuti * 100) / 60;
												if($dif_minuti_ok < 0){
													$dif_ore--;
												}
												$dif_minuti_ok = abs($dif_minuti_ok);	
												$diff[] = "$dif_ore.$dif_minuti_ok";
												$id_ctrl_ore[] = $ora->id;
												$tot_ore[] = "$dif_ore.$dif_minuti_ok";	
													
												if($ora->data != "$data_old" && $data_old != ""){
													
													print"	<tr class=\"intestazione\">
																		<td align=\"left\" colspan=\"3\">
																			TOTALE
																		</td>
																		<td align=\"center\" width=\"15%\">";
																			echo number_format(array_sum($ore_giorno), 2, ",", ".");
													print"		</td>
																		<td width=\"25%\" colspan=\"2\">
																			&nbsp;
																		</td>
																	</tr>
																</table>
																<br>
																<table class=\"table\">";
																
													$ore_giorno = array();			
												}	
												
			                					print"	<tr>
						                					<td class=\"text-center\">".date('d/m/Y', strtotime($ora->data))."</td>
						                					<td class=\"text-center\">$h_in:$m_in</td>
						                					<td class=\"text-center\">$h_out:$m_out</td>
						                					<td class=\"text-center\">$dif_ore,$dif_minuti_ok</td>
						                					<td class=\"text-center\">$nome_cli</td>
						                					<td class=\"text-center\">$ora->id_cc</td>
						                				</tr> ";
						                				
				                				$ore_giorno[] = "$dif_ore.$dif_minuti_ok";							
												$data_old = $ora->data;
												
											}
											
											$ritrovo = "";
											$specialita = "";
											$lista_specialita = array();
											$id_costo = "";
											$pagato = "";
											$paga = "";
										
										@endphp
										
	                				@endforeach
	                				
                					<tr class="intestazione">
										<td align="left" colspan="3">
											TOTALE
										</td>
										<td align="center" width="15%">
											{!! number_format(array_sum($ore_giorno), 2, ",", ".") !!}
										</td>
										<td width="25%" colspan="2">
											&nbsp;
										</td>
									</tr>
                				</tbody>
                				<tfoot>
                					<tr class="intestazione">
										<td align="left" colspan="3">
											TOTALE ORE
										</td>
										<td align="center" width="15%">
											{!! number_format(array_sum($tot_ore), 2, ",", ".") !!}
										</td>
										<td width="25%" colspan="2">
											&nbsp;
										</td>
									</tr>
                				</tfoot>
                				
                			</table>
                		</div>                      	
                        	                        	
                    </div>
                </div>
            </div>
        </div>

    </div>
@stop


@section('scripts')
    <script>

    


    $('select.selectYear').select2({placeholder:'Cambia Anno', allowClear: true});
    $('select.selectYear').on('change', function(){
        let val = $(this).find('option:selected').val();
        if(val != 'tutti')
        {
            window.location.href = baseURL+'stats/maestro/{{$maestro->id}}?year='+val;
        }
        else
        {
            window.location.href = baseURL+'stats/maestro/{{$maestro->id}}';
        }
    });


    $('a#menu-stats-maestri').addClass('active');
    $('a#menu-stats-aziende').parent('li').parent('ul.nav-treeview').css('display', 'block');
    $('a#menu-stats-aziende').parent('li').parent('ul').parent('li.has-treeview ').addClass('menu-open');


    </script>
@stop
