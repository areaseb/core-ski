@section('css')
<style>

	/* Full calendar colors */
	.fc-toolbar {
		display: none !important;
	}

    .no-w-limit {
        max-width: 1500px !important;
    }

	.fc-datagrid-cell-frame, .fc-datagrid-cell-cushion, .fc-timeline-slot-frame {
		background-color: #0082DA;
		color: #fff;
	}

    .fc-datagrid-cell-frame {
        min-width: 50px;
    }

	.fc-timeline-event .fc-event-main {
		height: 60px;
		max-height: 60px;
	}

	.fc-event-icon {
		height: 18px;
	}

	.fc .fc-event-main a {
		color: #000066;
	}

	.fc-h-event .fc-event-main {
		color: #000066;
	}

	.hourColumn .fc-datagrid-cell-main {
		text-align: center !important;		
		justify-content: center;
	}

    @media(min-width: 1000px) {
        .hourColumn .fc-datagrid-cell-main {
            display: flex;
        }
    }


</style>
@stop

@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => $day->format("d/m/Y").' calendario ore '])

@section('content')

	@php
		$yesterday = $day->copy()->subDays(1);
		$tomorrow = $day->copy()->addDays(1);
	@endphp

	<!-- Day filter -->
	<form>
	    <div class="row form-row align-items-end mb-5">
	        <div class="col-10 col-lg-2">
				<label>Giorno:</label>
				<div class="input-group" id="day" data-target-input="nearest">
					<input type="text" value="{{$day->format('d-m-Y')}}" name="day" class="form-control" data-target="#day" data-toggle="datetimepicker" />
					<div class="input-group-append" data-target="#day" data-toggle="datetimepicker">
						<div class="input-group-text"><i class="fa fa-calendar"></i></div>
					</div>					
				</div>
	        </div>
	        <div class="col-2 col-lg-2">
				<input type="submit" value="Filtra" class="btn btn-primary">
	        </div>

			<!-- Current day and navigation -->
			<div class="col-12 col-md-5 mt-2 text-center">
				<a href="{{ route('calendars.planning') }}?day={{$yesterday->format('d-m-Y')}}" class="btn btn-primary mr-2">
					<i class="fa fa-chevron-left"></i>
				</a>
				<label style="font-size: 120%">
			        {{$day->dayName}}
					{{$day->format('d')}}
					{{$day->monthName}}
					{{$day->format('Y')}}
				</label>
				<a href="{{ route('calendars.planning') }}?day={{$tomorrow->format('d-m-Y')}}" class="btn btn-primary ml-2">
					<i class="fa fa-chevron-right"></i>
				</a>
		    </div>

			<div class="col-12 col-md-3 text-right">
	            <a href="{{ route('calendars.planning') }}?day={{\Carbon\Carbon::today()->format('d-m-Y')}}" class="btn btn-primary @if($day->isSameDay(\Carbon\Carbon::today())) disabled @endif">Oggi</a>
	        </div>
	    </div>
	</form>

	<div id="calendarContainer"></div>
	
    
	



<!-- MODAL AGGIUNGI -->
<div class="modal fade" id="modalAggiungi" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
    aria-hidden="true">
    <div class="modal-dialog modal-xl no-w-limit" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Inserisci nuova ora</h5>
                
            </div>
            <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <table width="100%" cellpadding="3" cellspacing="3">
                                <tbody>
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Collettivo</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <select name="collettivo" class="form-control"  id="collettivo_id_modal_add">
                                                <option value="">- Scegli -</option>
                                                @foreach ($collettivi as $item)
                                                    <option value="{{ $item->id }}">{{ $item->nome }}</option>
                                                @endforeach 
                                            </select>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Azienda</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <div class="row" style="margin:0 !important">
                                            
{{--                                                <select name="cliente"  class="form-control col-md-5"  id="cliente_id_modal_add">
                                                    <option value="">- Scegli Azienda -</option>
                                                    @foreach ($clienti as $item)
                                                        <option value="{{ $item->id }}">{{ $item->rag_soc }}</option>
                                                    @endforeach 
                                                </select>
                                                &nbsp;
                                                <select name="partecipante"  class="form-control col-md-5"  id="partecipante_id_modal_add">
                                                    <option value="">- Scegli Privato -</option>
                                                    @foreach ($contacts as $item)
                                                        <option value="{{ $item->id }}">{{ $item->nome.' '.$item->cognome }}</option>
                                                    @endforeach 
                                                </select> --}}
                                                
                                                <div class="col-md-4 p-0">
						                            <div class="form-group ta">
						                                <input class="form-control" name="cliente" id="cliente_id_modal_add" type="text" tabindex="1">@if(request()->has('id'))<a title="reset" href="{{url('companies')}}" class="btn btn-danger reset"><i class="fas fa-times"></i></a>@endif
						                                <input id="cliente_id_modal_add_id" type="hidden">
						                            </div>
						                        	<div class="selection"></div>
						                        </div>
						                        <div class="col-md-4">
						                            <div class="form-group ta">
						                                <input class="form-control" name="partecipante" id="partecipante_id_modal_add" type="text" tabindex="1">@if(request()->has('id'))<a title="reset" href="{{url('companies')}}" class="btn btn-danger reset"><i class="fas fa-times"></i></a>@endif
														<input id="partecipante_id_modal_add_id" type="hidden">
						                            </div>
						                        	<div class="selection"></div>
						                        </div>
                                                
                                                &nbsp;
                                                <a href="#" onclick="gotoCreate()" style="max-height: 35px;" class="btn btn-sm btn-primary btn-block col-md-2"><b> <i class="fa fa-plus"></i></b></a>

                                            </div>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Segnaposto</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <div class="row" style="margin:0 !important">
                                            
                                                <select name="label"  class="form-control col-md-6"  id="label_id_modal_add">
                                                    <option value="">- Scegli -</option>
                                                    @foreach ($labels as $item)
                                                        <option value="{{ $item->id }}">{{ $item->nome }}</option>
                                                    @endforeach 
                                                </select>

                                            </div>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr>
                                        <td colspan="3"><br></td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Alloggio</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <select name="alloggio"  class="form-control" id="alloggio_modal_add">
                                                <option value="">- Scegli -</option>
                                                @foreach ($alloggi as $item)
                                                    <option value="{{ $item->id }}">{{ $item->luogo }} - {{ $item->hotel }}</option>
                                                @endforeach 
                                            </select>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b class="pax_modal_add_enabled">Pax</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <input type="text" name="pax" size="2"  id="pax_modal_add" class="form-item pax_modal_add_enabled" maxlength="2" value="1"> &nbsp;&nbsp;&nbsp; <!--<b>Sci club</b> <input type="checkbox" name="sciclub" id="sciclub_modal_add">-->
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Data</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <input type="date" name="data_in"  class="form-item" id="data_in_modal_add"> 
                                            <div style="display: inline;">
                                                <b>Al</b> 
                                                <input type="date" name="data_out"  onchange="document.getElementById('frequenza').style.display='table-row';" class="form-item" id="data_out_modal_add">
                                            </div>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>
                                    <tr id="frequenza" style="display: none;">
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Frequenza</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <input type="checkbox" name="freq_1" id="freq_1" value="S"> Lunedì &nbsp;&nbsp; 
                                            <input type="checkbox" name="freq_2" id="freq_2" value="S"> Martedì &nbsp;&nbsp; 
                                            <input type="checkbox" name="freq_3" id="freq_3" value="S"> Mercoledì &nbsp;&nbsp; <br>
                                            <input type="checkbox" name="freq_4" id="freq_4" value="S"> Giovedì &nbsp;&nbsp; 
                                            <input type="checkbox" name="freq_5" id="freq_5" value="S"> Venerdì &nbsp;&nbsp; 
                                            <input type="checkbox" name="freq_6" id="freq_6" value="S"> Sabato &nbsp;&nbsp; <br>
                                            <input type="checkbox" name="freq_0" id="freq_0" value="S"> Domenica 
                                            <br><br>oppure<br><br>
                                            <input type="checkbox" name="freq_C" id="freq_C" value="S"> Continuativo
                                        </td>
                                        <td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Dalle ore</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <select name="ora_in"  class="form-item" id="ora_in_modal_add">
                                                <option value="">- Scegli -</option>
                                                <option value=""></option>
                                                <option value="08:00">08:00</option>
                                                <option value="08:30">08:30</option>
                                                <option value="09:00">09:00</option>
                                                <option value="09:30">09:30</option>
                                                <option value="10:00">10:00</option>
                                                <option value="10:30">10:30</option>
                                                <option value="11:00">11:00</option>
                                                <option value="11:30">11:30</option>
                                                <option value="12:00">12:00</option>
                                                <option value="12:30">12:30</option>
                                                <option value="13:00">13:00</option>
                                                <option value="13:30">13:30</option>
                                                <option value="14:00">14:00</option>
                                                <option value="14:30">14:30</option>
                                                <option value="15:00">15:00</option>
                                                <option value="15:30">15:30</option>
                                                <option value="16:00">16:00</option>
                                                <option value="16:30">16:30</option>
                                                <option value="17:00">17:00</option>
                                                <option value="17:30">17:30</option>
                                                <option value="18:00">18:00</option>
                                            </select>
                                            &nbsp;&nbsp;&nbsp;
                                            <b>Alle ore</b>
                                            &nbsp;
                                            <select name="ora_out"  class="form-item" id="ora_out_modal_add">
                                                <option value="">- Scegli -</option>
                                                <option value=""></option>
                                                <option value="08:00">08:00</option>
                                                <option value="08:30">08:30</option>
                                                <option value="09:00">09:00</option>
                                                <option value="09:30">09:30</option>
                                                <option value="10:00">10:00</option>
                                                <option value="10:30">10:30</option>
                                                <option value="11:00">11:00</option>
                                                <option value="11:30">11:30</option>
                                                <option value="12:00">12:00</option>
                                                <option value="12:30">12:30</option>
                                                <option value="13:00">13:00</option>
                                                <option value="13:30">13:30</option>
                                                <option value="14:00">14:00</option>
                                                <option value="14:30">14:30</option>
                                                <option value="15:00">15:00</option>
                                                <option value="15:30">15:30</option>
                                                <option value="16:00">16:00</option>
                                                <option value="16:30">16:30</option>
                                                <option value="17:00">17:00</option>
                                                <option value="17:30">17:30</option>
                                                <option value="18:00">18:00</option>
                                            </select>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Ritrovo</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <select name="ritrovo"  class="form-control" id="ritrovo_modal_add">
                                                <option value="">- Scegli -</option> 
                                                @foreach ($ritrovi as $item)
                                                    <option value="{{ $item->id }}">{{ $item->luogo }} - {{ $item->posto }}</option>
                                                @endforeach   
                                            </select>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Disciplina</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <select name="disciplina" class="form-control" id="disciplina_modal_add">
                                                <option value="">- Scegli -</option>
                                                <option value="1" selected>Discesa</option>
                                                <option value="2">Fondo</option>
                                                <option value="4">Snowboard</option>					
                                            </select>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr id="tr_livello">
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Livello</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <select name="livello" id="livello_modal_add"  class="form-control">
                                                <option value="PRA">Primo Approccio</option>
                                                <option value="ELE">Elementare</option>
                                                <option value="BAS">Base</option>
                                                <option value="INT">Intermedio</option>
                                                <option value="AVA">Avanzato</option>
                                            </select>
                                            </td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Venditore</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <select name="venditore"  id="venditore_modal_add" class="form-control">
                                                <option value="">- Scegli -</option>
                                                <option value=""></option>
                                                <option value="S" selected="">Segreteria</option>
                                                <option value="N">Noleggio</option>
                                                <option value="M">Maestro</option>
                                                <option value="P">Prevendita</option>
                                                <option value="H">Altro</option>
                                            </select>
                                            <div id="divmaestro" style="display: none">
                                                <br>
                                                <select name="maestro_v" id="maestro_v_modal_add" class="form-control">
                                                    <option value="">- Scegli maestro -</option>  
                                                    @foreach ($maestri_list as $item)
                                                        <option value="{{ $item->id }}">{{ $item->cognome.' '.$item->nome }}</option>
                                                    @endforeach    
                                                </select>
                                            </div>
                                        </td>
                                    </tr>							    			
                                    <tr @if(!auth()->user()->hasRole('super')) style="display: none" @endif>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Sede</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                        {!! Form::select('branch_id_modal_add',$branches, null, ['class' => 'form-control', 'data-placeholder' => 'Associa una sede', 'data-fouc', 'style' => 'width:100%',' required' => 'required']) !!}

                                        </td>
                                    </tr> 	
                                </tbody>
                            </table>
                        </div>
                        <div class="col-12 col-md-6">
                            <table width="100%" cellpadding="3" cellspacing="3">
                                <tbody>
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            &nbsp;
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <div align="center"><b>Persona</b></div>
                                                    <table width="100%" cellpadding="0" cellspacing="0" class="tabelle">
                                                            <tbody><tr><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="2" value="S" class="specialita_modal_add"> Adulto
                                                            </td><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="1" value="S" class="specialita_modal_add"> Bambino (3-6)
                                                            </td></tr>
                                                                <tr><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="3" value="S" class="specialita_modal_add"> Disabile
                                                            </td><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="15" value="S" class="specialita_modal_add"> Ragazzo (7-14)
                                                            </td></tr>
                                                                <tr>	</tr>
                                                        </tbody>
                                                    </table>
                                                    <br>
                                                    <div align="center"><b>Lingua</b></div>
                                                    <table width="100%" cellpadding="0" cellspacing="0" class="tabelle">
                                                            <tbody><tr><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="9" value="S" class="specialita_modal_add"> Francese
                                                            </td><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="8" value="S" class="specialita_modal_add"> Inglese
                                                            </td></tr>
                                                                <tr><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="14" value="S" class="specialita_modal_add"> Polacco
                                                            </td><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="13" value="S" class="specialita_modal_add"> Tedesco
                                                            </td></tr>
                                                                <tr>	</tr>
                                                        </tbody>
                                                    </table>
                                                    <br>
                                                    
                                                    <div align="center"><b>Segreteria</b></div>
                                                    <table width="100%" cellpadding="0" cellspacing="0" class="tabelle">
                                                            <tbody><tr><td align="left" width="35%" class="td-modal">
                                                                <input type="checkbox" id="16" value="S" class="specialita_modal_add"> Pagamento in pista
                                                            </td><td align="left" width="40%" class="td-modal">
                                                                <input type="checkbox" id="17" value="S" class="specialita_modal_add"> Prenotazione telefonica
                                                            </td><td align="left" width="25%" class="td-modal">
                                                                <input type="checkbox" id="18" value="S" class="specialita_modal_add"> Ora aperta
                                                            </td></tr>
                                                                <tr>	</tr>
                                                        </tbody>
                                                    </table>
                                                    <br>				
                                        </td>
                                        <td>
                                        </td>
                                    </tr>		
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Note lezione</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <textarea name="note" cols="34" class="form-control" id="note_lez_modal_add" rows="3"></textarea>
                                        </td>
                                        <td>
                                        </td>
                                    </tr> 	
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <h5 id="maestro_id_modal_add" name="maestro" hidden></h5>

            </div>

            <div class="alert alert-info" style="display:none">
                <strong>Attenzione! Operazione fallita!</strong> 
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closemodalAggiungi">Annulla</button>
                <button type="button" class="btn btn-primary" id="btnsave">Salva</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL MODIFICA ORA -->

<div class="modal fade" id="modalModifica" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
    aria-hidden="true">
    <div class="modal-dialog modal-xl no-w-limit" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Modifica ora</h5>
                
            </div>
            <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <table width="100%" cellpadding="3" cellspacing="3">
                                <tbody>
                                	<tr id="tr_modal_upd">
                                		
                                	</tr>
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Azienda</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <div class="row" style="margin:0 !important">
                                            
{{--                                                <select name="cliente"  class="form-control col-md-5"  id="cliente_id_modal_upd">
                                                    <option value="">- Scegli Azienda -</option>
                                                    @foreach ($clienti as $item)
                                                        <option value="{{ $item->id }}">{{ $item->rag_soc }}</option>
                                                    @endforeach 
                                                </select>
                                                &nbsp;
                                                <select name="partecipante"  class="form-control col-md-5"  id="partecipante_id_modal_upd">
                                                    <option value="">- Scegli Privato -</option>
                                                    @foreach ($contacts as $item)
                                                        <option value="{{ $item->id }}">{{ $item->nome.' '.$item->cognome }}</option>
                                                    @endforeach 
                                                </select> --}}
                                                
                                                
                                                <div class="col-md-4 p-0">
						                            <div class="form-group ta">
						                                <input class="form-control" name="cliente" id="cliente_id_modal_upd" type="text" tabindex="1">@if(request()->has('id'))<a title="reset" href="{{url('companies')}}" class="btn btn-danger reset"><i class="fas fa-times"></i></a>@endif
						                                <input id="cliente_id_modal_upd_id" type="hidden">
						                            </div>
						                        	<div class="selection"></div>
						                        </div>
						                        <div class="col-md-4">
						                            <div class="form-group ta">
						                                <input class="form-control" name="partecipante" id="partecipante_id_modal_upd" type="text" tabindex="1">@if(request()->has('id'))<a title="reset" href="{{url('companies')}}" class="btn btn-danger reset"><i class="fas fa-times"></i></a>@endif
														<input id="partecipante_id_modal_upd_id" type="hidden">
						                            </div>
						                        	<div class="selection"></div>
						                        </div>
                                                
                                                &nbsp;
                                                <a href="#" onclick="gotoCreate()" class="btn btn-sm btn-primary btn-block col-md-2"><b> <i class="fa fa-plus"></i></b></a>

                                            </div>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Segnaposto</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <div class="row" style="margin:0 !important">
                                            
                                                <select name="label"  class="form-control col-md-6"  id="label_id_modal_upd">
                                                    <option value="">- Scegli -</option>
                                                    @foreach ($labels as $item)
                                                        <option value="{{ $item->id }}">{{ $item->nome }}</option>
                                                    @endforeach 
                                                </select>

                                            </div>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr>                                    	
                                        <td style="height: 20px;">
                                        </td>
                                        <td>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Maestro</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                        <select name="maestro_v" id="maestro_modal_upd" class="form-control">
                                                    <option value="">- Scegli maestro -</option>  
                                                    @foreach ($maestri_list as $item)
                                                        <option value="{{ $item->id }}">{{ $item->cognome.' '.$item->nome }}</option>
                                                    @endforeach    
                                                </select>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	


                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Alloggio</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <select name="alloggio"  class="form-control" id="alloggio_modal_upd">
                                                <option value="">- Scegli -</option>
                                                @foreach ($alloggi as $item)
                                                    <option value="{{ $item->id }}">{{ $item->luogo }} - {{ $item->hotel }}</option>
                                                @endforeach 
                                            </select>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b class="pax_modal_upd_enabled">Pax</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <input type="text" name="pax" size="2"  id="pax_modal_upd" class="form-item pax_modal_upd_enabled" maxlength="2" value="1"> &nbsp;&nbsp;&nbsp; <!--<b>Sci club</b> <input type="checkbox" name="sciclub" id="sciclub_modal_upd">-->
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Data</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <input type="date" name="data_in"  class="form-item" id="data_in_modal_upd"> 
                                            <div style="display: none;">
                                                <b>Al</b> 
                                                <input type="date" name="data_out"  class="form-item" id="data_out_modal_upd">
                                            </div>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>
                                    <tr id="frequenza" style="display: none;">
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Frequenza</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                        <input type="checkbox" name="freq_1" id="freq_1_upd" value="S"> Lunedì &nbsp;&nbsp; 
                                            <input type="checkbox" name="freq_2" id="freq_2_upd" value="S"> Martedì &nbsp;&nbsp; 
                                            <input type="checkbox" name="freq_3" id="freq_3_upd" value="S"> Mercoledì &nbsp;&nbsp; <br>
                                            <input type="checkbox" name="freq_4" id="freq_4_upd" value="S"> Giovedì &nbsp;&nbsp; 
                                            <input type="checkbox" name="freq_5" id="freq_5_upd" value="S"> Venerdì &nbsp;&nbsp; 
                                            <input type="checkbox" name="freq_6" id="freq_6_upd" value="S"> Sabato &nbsp;&nbsp; <br>
                                            <input type="checkbox" name="freq_0" id="freq_0_upd" value="S"> Domenica 
                                            <br><br>oppure<br><br>
                                            <input type="checkbox" name="freq_C" id="freq_C_upd" value="S"> Continuativo
                                        </td>
                                        <td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Dalle ore</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <select name="ora_in"  class="form-item" id="ora_in_modal_upd">
                                                <option value="">- Scegli -</option>
                                                <option value=""></option>
                                                <option value="08:00">08:00</option>
                                                <option value="08:30">08:30</option>
                                                <option value="09:00">09:00</option>
                                                <option value="09:30">09:30</option>
                                                <option value="10:00">10:00</option>
                                                <option value="10:30">10:30</option>
                                                <option value="11:00">11:00</option>
                                                <option value="11:30">11:30</option>
                                                <option value="12:00">12:00</option>
                                                <option value="12:30">12:30</option>
                                                <option value="13:00">13:00</option>
                                                <option value="13:30">13:30</option>
                                                <option value="14:00">14:00</option>
                                                <option value="14:30">14:30</option>
                                                <option value="15:00">15:00</option>
                                                <option value="15:30">15:30</option>
                                                <option value="16:00">16:00</option>
                                                <option value="16:30">16:30</option>
                                                <option value="17:00">17:00</option>
                                                <option value="17:30">17:30</option>
                                                <option value="18:00">18:00</option>
                                            </select>
                                            &nbsp;&nbsp;&nbsp;
                                            <b>Alle ore</b>
                                            &nbsp;
                                            <select name="ora_out"  class="form-item" id="ora_out_modal_upd">
                                                <option value="">- Scegli -</option>
                                                <option value=""></option>
                                                <option value="08:00">08:00</option>
                                                <option value="08:30">08:30</option>
                                                <option value="09:00">09:00</option>
                                                <option value="09:30">09:30</option>
                                                <option value="10:00">10:00</option>
                                                <option value="10:30">10:30</option>
                                                <option value="11:00">11:00</option>
                                                <option value="11:30">11:30</option>
                                                <option value="12:00">12:00</option>
                                                <option value="12:30">12:30</option>
                                                <option value="13:00">13:00</option>
                                                <option value="13:30">13:30</option>
                                                <option value="14:00">14:00</option>
                                                <option value="14:30">14:30</option>
                                                <option value="15:00">15:00</option>
                                                <option value="15:30">15:30</option>
                                                <option value="16:00">16:00</option>
                                                <option value="16:30">16:30</option>
                                                <option value="17:00">17:00</option>
                                                <option value="17:30">17:30</option>
                                                <option value="18:00">18:00</option>
                                            </select>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Ritrovo</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <select name="ritrovo"  class="form-control" id="ritrovo_modal_upd">
                                                <option value="">- Scegli -</option> 
                                                @foreach ($ritrovi as $item)
                                                    <option value="{{ $item->id }}">{{ $item->luogo }} - {{ $item->posto }}</option>
                                                @endforeach   
                                            </select>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Disciplina</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <select name="disciplina" class="form-control" id="disciplina_modal_upd">
                                            <option value="">- Scegli -</option>
                                                <option value="1" >Discesa</option>
                                                <option value="2">Fondo</option>
                                                <option value="4">Snowboard</option>					
                                            </select>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>	
                                    <tr id="tr_livello_upd">
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Livello</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <select name="livello" id="livello_modal_upd"  class="form-control">
                                                <option value="PRA">Primo Approccio</option>
                                                <option value="ELE">Elementare</option>
                                                <option value="BAS">Base</option>
                                                <option value="INT">Intermedio</option>
                                                <option value="AVA">Avanzato</option>
                                            </select>
                                            </td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Venditore</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <select name="venditore"  id="venditore_modal_upd" class="form-control">
                                                <option value="">- Scegli -</option>
                                                <option value=""></option>
                                                <option value="S" selected="">Segreteria</option>
                                                <option value="N">Noleggio</option>
                                                <option value="M">Maestro</option>
                                                <option value="P">Prevendita</option>
                                                <option value="H">Altro</option>
                                            </select>
                                            <div id="divmaestro_upd" style="display: none">
                                                <br>
                                                <select name="maestro_v" id="maestro_v_modal_upd" class="form-control">
                                                    <option value="">- Scegli maestro -</option>  
                                                    @foreach ($maestri_list as $item)
                                                        <option value="{{ $item->id }}">{{ $item->cognome.' '.$item->nome }}</option>
                                                    @endforeach    
                                                </select>
                                            </div>
                                        </td>
                                    </tr> 	 							    			
                                    <tr @if(!auth()->user()->hasRole('super')) style="display: none" @endif>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Sede</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                        {!! Form::select('branch_id_modal_upd[]',$branches, null, ['class' => 'select2 ucc', 'data-placeholder' => 'Associa uno o più sedi', 'data-fouc', 'style' => 'width:100%']) !!}

                                        </td>
                                    </tr> 	
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Maestro richiesto</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                        	<input type="checkbox" name="richiesto" id="richiesto_modal_upd"> 
                                        </td>
                                    </tr> 	
                                </tbody>
                            </table>
                        </div>
                        <div class="col-12 col-md-6">
                            <table width="100%" cellpadding="3" cellspacing="3">
                                <tbody>
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            &nbsp;
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <div align="center"><b>Persona</b></div>
                                                    <table width="100%" cellpadding="0" cellspacing="0" class="tabelle">
                                                            <tbody><tr><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="2_upd" value="S" class="specialita_modal_upd"> Adulto
                                                            </td><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="1_upd" value="S" class="specialita_modal_upd"> Bambino (3-6)
                                                            </td></tr>
                                                                <tr><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="3_upd" value="S" class="specialita_modal_upd"> Disabile
                                                            </td><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="15_upd" value="S" class="specialita_modal_upd"> Ragazzo (7-14)
                                                            </td></tr>
                                                                <tr>	</tr>
                                                        </tbody>
                                                    </table>
                                                    <br>
                                                    <div align="center"><b>Lingua</b></div>
                                                    <table width="100%" cellpadding="0" cellspacing="0" class="tabelle">
                                                            <tbody><tr><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="9_upd" value="S" class="specialita_modal_upd"> Francese
                                                            </td><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="8_upd" value="S" class="specialita_modal_upd"> Inglese
                                                            </td></tr>
                                                                <tr><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="14_upd" value="S" class="specialita_modal_upd"> Polacco
                                                            </td><td align="left" width="50%" class="td-modal">
                                                                <input type="checkbox" id="13_upd" value="S" class="specialita_modal_upd"> Tedesco
                                                            </td></tr>
                                                                <tr>	</tr>
                                                        </tbody>
                                                    </table>
                                                    
                                                    
                                                    <br>
                                                    <div align="center"><b>Segreteria</b></div>
                                                    <table width="100%" cellpadding="0" cellspacing="0" class="tabelle">
                                                            <tbody>
                                                                <tr>
                                                                    <td align="left" width="35%" class="td-modal">
                                                                        <input type="checkbox" id="16_upd" value="S" class="specialita_modal_upd"> Pagamento in pista
                                                                    </td>
                                                                    <td align="left" width="40%" class="td-modal">
                                                                        <input type="checkbox" id="17_upd" value="S" class="specialita_modal_upd"> Prenotazione telefonica
                                                                    </td>
                                                                    <td align="left" width="25%" class="td-modal">
                                                                        <input type="checkbox" id="18_upd" value="S" class="specialita_modal_upd"> Ora aperta
                                                                    </td>
                                                                </tr>
                                                                <tr>	</tr>
                                                        </tbody>
                                                    </table>
                                                    <br>				
                                        </td>
                                        <td>
                                        </td>
                                    </tr>		
                                    <tr>
                                        <td width="20%" class="testo" align="right" valign="top">
                                            <b>Note lezione</b>
                                        </td>
                                        <td width="80%" class="testo" align="left" valign="top">
                                            <textarea name="note" cols="34" class="form-control" id="note_lez_modal_upd" rows="3"></textarea>
                                        </td>
                                        <td>
                                        </td>
                                    </tr> 	
                                </tbody>
                            </table>
                        </div>
                    </div>
      
                    <h5 id="record_id_modal_upd" name="record" hidden></h5>
                    <h5 id="invoice_id_modal_upd" hidden></h5>

            </div>

            <div class="alert alert-info" style="display:none">
                <strong>Attenzione!</strong> Operazione fallita!
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closemodalModifica">Annulla</button>
                <button type="button" class="btn btn-primary" id="btnupdate">Modifica</button>
                <button type="button" class="btn btn-warning" id="btnFattura">Fattura</button>
                <button type="button" class="btn btn-warning" id="btnopenModalDoc">Aggiungi a Documento</button>
                <button type="button" class="btn btn-danger" id="btndelete">Elimina</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="modalAddByCliente" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
    aria-hidden="true">
    <div class="modal-dialog modal-xl no-w-limit modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Completa Inserimento nuova ora</h5>
                <h5 id="oraIDByCliente" hidden></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="maestro_id_modal_addCliente"></div>
                    </div> 
                    <div class="col-md-12 mt-3">
                        <b>Maestro richiesto</b> <input type="checkbox" name="sciclub" id="richiesto_modal_add">
                    </div> 
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closemodalAggiungiCliente">Annulla</button>
                <button type="button" class="btn btn-warning" id="btnsaveCliente">Lascia ora aperta</button>
                <button type="button" class="btn btn-success" id="btnclosedCliente">Termina</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalAddByCollettivo" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Completa Inserimento nuova ora</h5>
                <h5 id="oraIDByCollettivo" hidden></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                    <b>Livello dell'alievo</b>
	  					<select name=\"livello\" id="livelloCollettivo">
    						<option value=\"\">- Scegli -</option>
    						<optgroup label=\"Bronzo\">
	    						<option value=\"B_B\">Base</option>
	    						<option value=\"B_I\">Intermedio</option>
	    						<option value=\"B_A\">Avanzato</option>
	    					</optgroup>
	    					<optgroup label=\"Argento\">
	    						<option value=\"A_B\">Base</option>
	    						<option value=\"A_I\">Intermedio</option>
	    						<option value=\"A_A\">Avanzato</option>
	    					</optgroup>
	    					<optgroup label=\"Oro\">
	    						<option value=\"O_B\">Base</option>
	    						<option value=\"O_I\">Intermedio</option>
	    						<option value=\"O_A\">Avanzato</option>
	    					</optgroup>
    					</select>
                    </div> 
                    <div class="col-md-6">
                        <b>Età</b> <input type="text" id="eta_modal_add" > 
                    </div> 
                </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="closemodalAggiungiCollettivo">Annulla</button>
                <button type="button" class="btn btn-warning" id="btnsaveCollettivo">Lascia ora aperta</button>
                <button type="button" class="btn btn-success" id="btnclosedCollettivo">Termina</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal Delete -->
<div class="modal fade" id="modalEliminazioneOra" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Eliminazione Ora</h5>
                <h5 id="oraIDRemove" hidden></h5>
                <h5 id="invoiceIDRemove" hidden></h5>
            </div>
            <div class="modal-body">
                <h4>Sei sicuro di voler eliminare la seguente ora?</h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closemodalEliminazioneOra">Annulla</button>
                <button type="button" class="btn btn-danger" id="btnRemoveOra"><i
                        class="fa fa-trash mr-1"></i>Conferma</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalAddDoc" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Inserisci i dati del documento in cui aggiungere l'ora</h5>
                <h5 id="ccID" hidden></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <b>Sede: </b> <label id="lbl_cc"></label>
                    </div> 
                </div>

                <div class="row">
                    <div class="col-md-12">
                    <b>Tipo di documento: </b>
                        <select class="form-control"  id="tipo_doc">
                            <option value="R">Ricevuta</option>
                            <option value="F">Fattura</option>
                            <option value="D">Documento</option>
                            <option value="A">Nota di accredito</option>
                        </select>
                    </div> 
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <b>N° documento: </b><input type="text" id="n_doc" class="form-control">
                    </div> 
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <b>Data documento: </b><input type="date" id="data_doc" class="form-control">
                    </div> 
                </div>

                <div class="row">
                    <div class="col-md-12">
                    <b>Prodotto:</b>
                    {!! Form::select('prodotto_doc',$products, null, ['class' => 'select2 ucc', 'data-fouc', 'style' => 'width:100%']) !!}

                    </div> 
                </div>

                <br>
                <div class="alert alert-info alert-info-add-doc" style="display:none">
                    <strong>Attenzione!</strong> Documento non trovato!
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closemodalAddDoc">Annulla</button>
                <button type="button" class="btn btn-success" id="btnAddDoc">Inserisci</button>
            </div>
        </div>
    </div>
</div>


</div>









@stop

@section('scripts')
<script src="{{asset('calendar_sch/index.global.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/js/autoComplete.min.js"></script>
<script>

document.addEventListener('DOMContentLoaded', function() {
    $('[data-widget="pushmenu"]').PushMenu('collapse')

    // Client save function
    let hourInfo = null;

	// Set datetimepicker to date only
	$("#day").datetimepicker({
		minView: 2,
		format: 'DD-MM-YYYY'
	});

    let calendarEl = document.getElementById('calendarContainer');

	let calendar = new FullCalendar.Calendar(calendarEl, {		
        contentHeight:"auto",

		schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',

		initialView: 'resourceTimelineDay',		
		locale: 'it',
	    eventTimeFormat: {
	        hour: '2-digit',
	        minute: '2-digit',
	        hour12: false
	    },

		slotMinTime: '08:00',
		slotMaxTime: '18:00',
		initialDate: '{{$day->format("Y-m-d")}}',

        themeSystem: 'bootstrap',
        editable: true,
        selectable: true,

		resourceAreaColumns: [
			{
				field: 'title',
				headerContent: 'Maestri',
				width: "75%",
			},
			{
				field: 'hours',
				headerContent: 'Ore',
				width: "25%",
				headerClassNames: 'hourColumn',
				cellClassNames: 'hourColumn',
			}
		],

		resourceOrder: 'order',
		resourceAreaWidth: '15%',

		resources: [
			@foreach($teachers as $teacher)
				{ id: {{$teacher->id}}, order: '{{$teacher->contact->cognome}}', title: '<a target="_blank" id="{{$teacher->id}}" class="text-white" href="/contacts-master/{{$teacher->contact_id}}"><b>{{$teacher->contact->cognome}} {{$teacher->contact->nome}}</b></a><br /><a style="color: #fff" href="tel:{{$teacher->contact->cellulare}}">{{$teacher->contact->cellulare}}</a>', hours: '{{$teacher->total_hours}}', },
			@endforeach		
		],

		events: [
			@foreach($hours as $hour)
				@php
					$background_color = $hour->teacher->color;

					if(isset($hour->special_color))
						$background_color = $hour->special_color;
				@endphp

				{
					id: {{$hour->id}},
					title: '{!! $hour->event_title !!}',
					start: '{{$hour->data}} {{$hour->ora_in}}',
					end: '{{$hour->data}} {{$hour->ora_out}}',
					resourceId: {{$hour->id_maestro}},
					backgroundColor: '{{$background_color}}',
					overlap: true,
				},
			@endforeach
		],

		// Display as HTML
		resourceLabelContent: function (info) {
		 	return {html: info.resource.title};
      	},

		eventContent: function( info ) {
        	return {html: info.event.title};
      	},

		// Drag & drop events
		eventDrop: function(info) {
			if (!confirm("Sei sicuro di voler spostare questa lezione?")) {
				info.revert();
				return;
			}
				
			// Get sender hour ID and receiver teacher / hour
			let hourId = info.event.id;
			let newStartHour = moment(info.event.start).format("HH:mm");
			let newEndHour = moment(info.event.end).format("HH:mm");
			let newTeacherId = null;

			// If event changed resource (teacher), object will have newResource
			if(info.newResource)
				newTeacherId = info.newResource.id;

			jQuery.ajax("{{ route('api.planning.updateHour') }}", {
                method: 'POST',
                data: {
                    '_token': '{{ csrf_token() }}', hourId, newStartHour, newEndHour, newTeacherId
                },
                complete: function (resp) {
					//location.reload();
                },
            });
		},

		eventResize: function(info) {
			if (!confirm("Sei sicuro di voler cambiare la durata della lezione?")) {
				info.revert();
				return;
			}
				
			// Get sender hour ID and receiver teacher / hour
			let hourId = info.event.id;
			let newStartHour = moment(info.event.start).format("HH:mm");
			let newEndHour = moment(info.event.end).format("HH:mm");
			let newTeacherId = null;

			// If event changed resource (teacher), object will have newResource
			if(info.newResource)
				newTeacherId = info.newResource.id;

			jQuery.ajax("{{ route('api.planning.updateHour') }}", {
                method: 'POST',
                data: {
                    '_token': '{{ csrf_token() }}', hourId, newStartHour, newEndHour, newTeacherId
                },
                complete: function (resp) {
					//location.reload();
                },
            });
		},

        dateClick: function(info) {
            dayClickCallback(info);
        },

        eventClick: function(info) {
            //showModal(info.event.id)
        }
	});

    calendar.render();
});

    var dblclick = false;
    function clear_click(){
        dblclick = false;
    }

    function dayClickCallback(info){
        if(dblclick){
            let hour = moment(info.date).format('H');
			let minutes = moment(info.date).format('m');
			let teacherId = info.resource.id;

			// CHECK FOR SUPER
			@if(Auth::user()->hasRole('super'))
				let branchId = null;
			@else
				let branchId = '{{ (auth()->user()->contact->branchContact()->branch_id) ? auth()->user()->contact->branchContact()->branch_id : null }}';
			@endif

			openAddOraPreimpostata(hour, minutes, teacherId, branchId);
        }else{
            dblclick = true;
            const myTimeout = setTimeout(clear_click, 300);
        }
    }

    var giorno_output = '<?php echo $giorno_output; ?>';
    var branches = <?php echo json_encode($branches); ?>;
    sessionStorage.clear();



    var collettivi = <?php echo json_encode($collettivi); ?>;
//   var clienti = <?php //echo json_encode($clienti); ?>;
//    var contacts = <?php //echo json_encode($contacts); ?>;


    function preimpostaCollection(id_sede){
        const filteredCollettivi = collettivi.filter(val => val.centro_costo == id_sede);
        console.log(collettivi, filteredCollettivi);
        $("#collettivo_id_modal_add").select2('destroy').empty();
        $('#collettivo_id_modal_add').append('<option value="">- Scegli Collettivo -</option>');    
        filteredCollettivi.forEach(element => {
            $('#collettivo_id_modal_add').append('<option value="'+element.id+'">'+element.nome+'</option>');    
        });
        $('#collettivo_id_modal_add').select2({width: '100%'});

/*        const filteredClienti = clienti.filter(val => val.sedi.includes(id_sede));
        console.log(clienti, filteredClienti);
        $("#cliente_id_modal_add").select2('destroy').empty();
        $('#cliente_id_modal_add').append('<option value="">- Scegli Azienda -</option>');    
        filteredClienti.forEach(element => {
            $('#cliente_id_modal_add').append('<option value="'+element.id+'">'+element.rag_soc+'</option>');    
        });
        $('#cliente_id_modal_add').select2();*/


/*        const filteredContacts = contacts.filter(val => val.sedi.includes(id_sede));
        console.log(contacts, filteredContacts);
        $("#partecipante_id_modal_add").select2('destroy').empty();
        $('#partecipante_id_modal_add').append('<option value="">- Scegli Privato -</option>');    
        filteredContacts.forEach(element => {
            $('#partecipante_id_modal_add').append('<option value="'+element.id+'">'+element.cognome+' ' + element.nome +'</option>');    
        });
        $('#partecipante_id_modal_add').select2();*/


    }

    
    function clearFields(){
        $('#collettivo_id_modal_add').val('').trigger('change')
        $('#cliente_id_modal_add').val('')
        $('#partecipante_id_modal_add').val('')
        $('#cliente_id_modal_add_id').val('')
        $('#partecipante_id_modal_add_id').val('')
        $('#label_id_modal_add').val('')
        $('#alloggio_modal_add').val('')
        $('#sciclub_modal_add').prop('checked',false)
        $('.specialita_modal_add').prop('checked',false)
        $('#ritrovo_modal_add').val('')
        $('#note_lez_modal_add').val('')
        $('#note_lez_modal_add').text('')
        $('#pax_modal_add').val('1')
        $('select[name="branch_id_modal_add"]').val('')

        $('#ora_in_modal_add').val('')
        $('#ora_out_modal_add').val('')
        $('#ritrovo_modal_add').val('')
        $('#disciplina_modal_add').val('')
        $('#frequenza').hide();

    }


    function clearFieldsUpdate(){
        $('#maestro_v_modal_upd').val('')
        $('#alloggio_modal_upd').val('')
        $('#pax_modal_upd').val('1')
        $('#sciclub_modal_upd').prop('checked',false)
        $('#data_in_modal_upd').val('')
        $('#ora_in_modal_upd').val('')
        $('#ora_out_modal_upd').val('')
        $('#ritrovo_modal_upd').val('')
        $('#livello_modal_upd').val('')
        $('#venditore_modal_upd').val('')
        $('.specialita_modal_upd').prop('checked',false)
        $('#note_lez_modal_upd').val('')
        $('#note_lez_modal_upd').text('')
        $('#tr_modal_upd').html('')
        $('#richiesto_modal_upd').prop('checked',false)
        $('#cliente_id_modal_upd').val('');
        $('#partecipante_id_modal_upd').val('');
        $('#cliente_id_modal_upd_id').val('');
        $('#partecipante_id_modal_upd_id').val('');
        $('#label_id_modal_upd').val('').change();

    }
    
        //$('select[name="branch_id_modal_add"]').select2({width: '100%'});
        $('#maestro_id_modal_addCollettivo').select2({width: '100%'});
        $('#collettivo_id_modal_add').select2({width: '100%'});
        $('#collettivo_id_modal_upd').select2({width: '100%'});

        $('select[name="prodotto_doc"]').select2({width: '100%'});

//        $('#cliente_id_modal_add').select2({width: '40%'});
//        $('#partecipante_id_modal_add').select2({width: '40%'});
//        $('#cliente_id_modal_upd').select2({width: '40%'});
//        $('#partecipante_id_modal_upd').select2({width: '40%'});

        $('.pax_modal_add_enabled').hide();
		
		@if(auth()->user()->roles()->count() == 1 && auth()->user()->hasRole('Maestro'))
	        $('select#venditore_modal_add').val('M').change();
	        $('#divmaestro').show();
	        $('select#maestro_v_modal_add').val({{ auth()->user()->contact->master->id }}).change();
	            
	    @endif
	    
    	$('select#venditore_modal_add').on('change', function(){
            $('#divmaestro').hide()
            if($(this).val() == 'M')
                $('#divmaestro').show()
        });

        $('select#company_id').on('change', function(){
            console.log($(this).val())
            reloadDropDown();
        });

        
        $('#partecipante_id_modal_add').on('input', function(){
            console.log($(this).val(), 'pippo')
            if($(this).val() != ''){
                $('.pax_modal_add_enabled').show();
                $('#tr_livello').show();
                getLevel($(this).val());
                getDisabled($(this).val());
            }
            else{
                $('#tr_livello').hide()
                $('.pax_modal_add_enabled').hide();
            }
                
        });
        
        $('select#collettivo_id_modal_add').on('change', function(){
            console.log($(this).val())
            if($(this).val() != ''){
                $('#cliente_id_modal_add').prop('disabled',true);
                $('#partecipante_id_modal_add').prop('disabled',true);
                $('select#label_id_modal_add').prop('disabled',true);
            }
            else{
                $('#cliente_id_modal_add').prop('disabled',false);
                $('#partecipante_id_modal_add').prop('disabled',false);
                $('select#label_id_modal_add').prop('disabled',false);
            }                
        });
        
        $('#cliente_id_modal_add').on('input', function(){
            console.log($(this).val())
            if($(this).val() != ''){
                $('select#collettivo_id_modal_add').prop('disabled',true);
                $('select#label_id_modal_add').prop('disabled',true);
            }
            else{
                $('select#collettivo_id_modal_add').prop('disabled',false);
                $('select#label_id_modal_add').prop('disabled',false);
            }                
        });
        
        $('#partecipante_id_modal_add').on('input', function(){
            console.log($(this).val())
            if($(this).val() != ''){
                $('select#collettivo_id_modal_add').prop('disabled',true);
                $('select#label_id_modal_add').prop('disabled',true);
            }
            else{
                $('select#collettivo_id_modal_add').prop('disabled',false);
                $('select#label_id_modal_add').prop('disabled',false);
            }                
        });
        
        $('select#label_id_modal_add').on('change', function(){
            console.log($(this).val())
            if($(this).val() != ''){
                $('#cliente_id_modal_add').prop('disabled',true);
                $('#partecipante_id_modal_add').prop('disabled',true);
                $('select#collettivo_id_modal_add').prop('disabled',true);
            }
            else{
                $('#cliente_id_modal_add').prop('disabled',false);
                $('#partecipante_id_modal_add').prop('disabled',false);
                $('select#collettivo_id_modal_add').prop('disabled',false);
            }                
        });

        $('select#contact_id').on('change', function(){
            console.log($(this).val())
            reloadDataStudent();
        });

		
		
		$('#partecipante_id_modal_upd').on('input', function(){
            if($(this).val() != ''){
                $('.pax_modal_upd_enabled').show();
                $('#tr_livello').show();
                getLevel($(this).val());
                getDisabled($(this).val());
            }
            else{
                $('#tr_livello').hide()
                $('.pax_modal_upd_enabled').hide();
            }
                
        });
        
        $('#cliente_id_modal_upd').on('input', function(){
            if($(this).val() != ''){
                $('.pax_modal_upd_enabled').show();
                $('#tr_livello').show();
            }
            else{
                $('#tr_livello').hide()
                $('.pax_modal_upd_enabled').hide();
            }
                
        });
        
        $('#cliente_id_modal_upd').on('input', function(){
            if($(this).val() != ''){
                $('select#label_id_modal_upd').prop('disabled',true);
            }
            else{
                $('select#label_id_modal_upd').prop('disabled',false);
            }                
        });
        
        $('#partecipante_id_modal_upd').on('input', function(){
            if($(this).val() != ''){
                $('select#label_id_modal_upd').prop('disabled',true);
            }
            else{
                $('select#label_id_modal_upd').prop('disabled',false);
            }                
        });
        
        

        function openAddOra(id_maestro, id_sede)
        {
        	
	            console.log('openAddOra: ', id_sede)
	            clearFields();
	            $('select[name="branch_id_modal_add"]').val(id_sede)
	            //$('select[name="branch_id_modal_add"]').prop('disabled',true)
	            console.log('giorno_output: ', giorno_output)
	            $('#data_in_modal_add').val(giorno_output)
	            $('#data_out_modal_add').val(giorno_output)
	            $('#maestro_id_modal_add').val(id_maestro)
	           
	            preimpostaCollection(id_sede);
	            $('#tr_livello').hide()
	            $('#modalAggiungi').modal('toggle');
        };


        function openAddOraPreimpostata(ora,min,id_maestro, id_sede){
        	
        	@if(auth()->user()->hasRole('Maestro') && auth()->user()->roles()->count() == 1 && $giorno_output < date('Y-m-d'))
        		return true;
        	@else
				ora = parseInt(ora);
				min = parseInt(min);

	            console.log('openAddOraPreimpostata: ', id_sede)
	            clearFields();
	            $('select[name="branch_id_modal_add"]').val(id_sede)
	            //$('select[name="branch_id_modal_add"]').prop('disabled',true)
	            let oraFine = (ora + 1) < 10 ? '0'+(ora + 1) : (ora + 1);
				ora = ora < 10 ? '0'+ora : ora;   
	            min = min == 30 ? ':30' : ':00';        
	            let time = ora + min;
	            let timeFine = oraFine + min;
	            console.log('time:',time)
	            console.log('timeFine:',timeFine)
	            console.log('giorno_output: ', giorno_output)
	            $('#ora_in_modal_add').val(time)
	            $('#ora_out_modal_add').val(timeFine)
	            $('#data_in_modal_add').val(giorno_output)
	            $('#data_out_modal_add').val(giorno_output)
	            $('#maestro_id_modal_add').val(id_maestro)

	            //preimpostaCollection(id_sede);
	            $('#tr_livello').hide()
	            $('#modalAggiungi').modal('toggle');

	            $('#disciplina_modal_add').val('1');
	        @endif
        }

        //modale modifica

        $('select[name="branch_id_modal_upd[]"]').select2({width: '100%'});

        $("#closemodalModifica").click(function() {
            $('#modalModifica').modal('toggle');
        });

        $("#closemodalEliminazioneOra").click(function() {
            $('#modalEliminazioneOra').modal('toggle');
        });

        $("#closemodalAddDoc").click(function() {
            $('#modalAddDoc').modal('toggle');
        });

        
        function gotoCreate(){
            window.location.href = "/planning/create-company/" + giorno_output;
        }
        
        
		function getLevel(id_contatto){
        
            jQuery.ajax('/planning/get-level',
            {
                method: 'GET',
                data: {
                    "id": id_contatto

                },

                complete: function (resp) {
                	console.log(resp.responseText);
                	$('select#livello_modal_add').val(resp.responseText);
                }
            });
        }
        
        function getDisabled(id_contatto){
        	
            jQuery.ajax('/planning/get-disabled',
            {
                method: 'GET',
                data: {
                    "id": id_contatto

                },

                complete: function (resp) {
                	if(resp.responseText == 'true'){
                		$('#3.specialita_modal_add').prop('checked', true);
                	} else {
                		$('#3.specialita_modal_add').prop('checked', false);
                	}
                }
            });
        }
               	
        function openModOra(id_ora){
        	
        	@if(auth()->user()->hasRole('Maestro') && auth()->user()->roles()->count() == 1 && $giorno_output < date('Y-m-d'))
        		return true;
        	@else
	            clearFieldsUpdate();
	            jQuery.ajax('/planning/get-ora',
	            {
	                method: 'POST',
	                data: {
	                    "_token": '{{ csrf_token() }}',
	                    "id_ora": id_ora

	                },

	                complete: function (resp) {
	                    $('.alert').hide();
	                    var result = JSON.parse(resp.responseText);
	                    console.log('id_cliente: ' + result.data.id_cliente)
	                    var data = JSON.stringify(result.data)
	                    console.log('data: ' + data)
	                    if(result.code){
	                        $('.pax_modal_upd_enabled').hide();
	                        $('#data_in_modal_upd').val(giorno_output)
	                        $('#data_out_modal_upd').val(giorno_output)
	                        $('#maestro_v_modal_upd').val(result.data.nome_venditore)
	                        $('#maestro_modal_upd').val(result.data.id_maestro)
	                        
	                        if(result.data.venditore == 'M'){
	                			$('#divmaestro_upd').show();
	                		} else {
	                			$('#divmaestro_upd').hide();
	                		}
	                		
	                        var arr = result.data.id_cliente.split('_')
	                        $('#tr_livello_upd').hide()
	                        //partecipante
	                        if(arr[0] == 'T'){
	                        	$('#partecipante_id_modal_upd').val(result.data.nome_cliente);
	                        	$('#partecipante_id_modal_upd_id').val(arr[1]);
	                            $('#tr_modal_upd').append(`<td width="20%" class="testo" align="right" valign="top">
	                                                                        <b>Partecipante</b>
	                                                                    </td>
	                                                                    <td width="80%" class="testo" align="left" valign="top">`
	                                                                        + result.data.item_label+
	                                                                        `</td>
	                                                                    <td>
	                                                                    </td>`);

	                            $('.pax_modal_upd_enabled').show();
	                            $('#tr_livello_upd').show()
	                            $('#label_id_modal_upd').prop('disabled',true);
	                        }
	                        //label
	                        if(arr[0] == 'L'){
	                        	$('#label_id_modal_upd').val(arr[1]).change();
	                            $('#tr_modal_upd').append(`<td width="20%" class="testo" align="right" valign="top">
	                                                                        <b>Segnaposto</b>
	                                                                    </td>
	                                                                    <td width="80%" class="testo" align="left" valign="top">`
	                                                                        +result.data.item_label+
	                                                                        `</td>
	                                                                    <td>
	                                                                    </td>`);
	                        }
	                        if(arr[0] == 'Y'){
	                        	$('#cliente_id_modal_upd').val(result.data.nome_cliente);
	                        	$('#cliente_id_modal_upd_id').val(arr[1]);
	                            $('#tr_modal_upd').append(`<td width="20%" class="testo" align="right" valign="top">
	                                                                        <b>Cliente</b>
	                                                                    </td>
	                                                                    <td width="80%" class="testo" align="left" valign="top">`
	                                                                        + result.data.item_label+
	                                                                        `</td>
	                                                                    <td>
	                                                                    </td>`);
	                            $('#label_id_modal_upd').prop('disabled',true);
	                        }

	                        if(arr[0] == 'C'){
	                            $('#tr_modal_upd').append(`<td width="20%" class="testo" align="right" valign="top">
	                                                                        <b>Collettivo</b>
	                                                                    </td>
	                                                                    <td width="80%" class="testo" align="left" valign="top">`
	                                                                        +result.data.item_label+
	                                                                        `</td>
	                                                                    <td>
	                                                                    </td>`);
	                        }



	                        $('#alloggio_modal_upd').val(result.data.id_alloggio)
	                        $('#pax_modal_upd').val(result.data.pax)
	                        $('#ora_in_modal_upd').val(result.data.ora_in.substring(0, 5))
	                        $('#ora_out_modal_upd').val(result.data.ora_out.substring(0, 5))
	                        $('#ritrovo_modal_upd').val(result.data.ritrovo_id)
	                        $('#disciplina_modal_upd').val(result.data.disciplina)
	                        $('#livello_modal_upd').val(result.data.livello)
	                        $('#venditore_modal_upd').val(result.data.venditore)
	                        $('select[name="branch_id_modal_upd[]"]').val(result.data.id_cc).trigger('change')
	                        //$('select[name="branch_id_modal_upd[]"]').prop('disabled',true)
	                        $('#note_lez_modal_upd').val(result.data.note)
	                        $('#record_id_modal_upd').val(result.data.id)
	                        $('#invoice_id_modal_upd').val(result.data.invoice_id);

	                        var arr = result.data.specialita != null ? result.data.specialita.split(',') : [];
	                        var specs = [];
	                        for (var i = 0; i < arr.length; i++){
	                            specs.push(parseInt(arr[i]));
	                        }

	                        console.log(specs.includes(10));
	                        if(specs.includes(10))
	                            $('#10_upd').prop('checked',true)
	                        if(specs.includes(11))
	                            $('#11_upd').prop('checked',true)
	                        if(specs.includes(9))
	                            $('#9_upd').prop('checked',true)
	                        if(specs.includes(8))
	                            $('#8_upd').prop('checked',true)
	                        if(specs.includes(13))
	                            $('#13_upd').prop('checked',true)
	                        if(specs.includes(14))
	                            $('#14_upd').prop('checked',true)
	                        if(specs.includes(2))
	                            $('#2_upd').prop('checked',true)
	                        if(specs.includes(1))
	                            $('#1_upd').prop('checked',true)
	                        if(specs.includes(3))
	                            $('#3_upd').prop('checked',true)
	                        if(specs.includes(15))
	                            $('#15_upd').prop('checked',true)
	                        if(specs.includes(16))
	                            $('#16_upd').prop('checked',true)
	                        if(specs.includes(17))
	                            $('#17_upd').prop('checked',true)
	                        if(specs.includes(18))
	                            $('#18_upd').prop('checked',true)
							
							if(result.data.richiesto == 'S'){
								$('#richiesto_modal_upd').prop('checked',true)
							} else {
								$('#richiesto_modal_upd').prop('checked',false)
							}
							
							
	                        $('#btnupdate').show()
	                        if(result.data.saldato == 1){
	                            $('#btnFattura').hide();
	                            $('#btnopenModalDoc').hide()
							}
	                        $('#modalModifica').modal('toggle');
	                    }  else {
	                    	alert(result.message);
	                    }  
	                }
	            });
	        @endif
        }

    
        $( "#btnopenModalDoc" ).click(function() {
            //$('#modalModifica').modal('toggle');
            $('#modalAddDoc').modal('toggle');
            var branch_id = $('select[name="branch_id_modal_upd[]"]').val();
            //console.log('btnopenModalDoc: ', $('select[name="branch_id_modal_upd[]"]').val())
            $('#ccID').val($('select[name="branch_id_modal_upd[]"]').val())
            //console.log(branches[branch_id])
            $('#lbl_cc').text(branches[branch_id])
        })


        $( "#btnAddDoc" ).click(function() {
            var ora_id = $('#record_id_modal_upd').val()
            var invoice_id = $('#invoice_id_modal_upd').val()
            jQuery.ajax('/planning/add-document-ora',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "tipo_doc": $('#tipo_doc').val(),
                    "n_doc": $('#n_doc').val(),
                    "data_doc": $('#data_doc').val(),
                    "prodotto_doc":$('select[name="prodotto_doc"]').val(),
                    "ora_id":ora_id,
                    "invoice_id":invoice_id
                },
                
                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        if(result.data != null) {

                            console.log(result.data)
                            window.location.href = "/invoices/" + result.data + '/edit';
                        } else {
                            $('.alert-info-add-doc').show();
                        }
                    } else {
                    	alert(result.message);
                    }    
                }
            });
        })


        $( "#btnFattura" ).click(function() {
            var invoice_id = $('#invoice_id_modal_upd').val()
			let hourId = $('#record_id_modal_upd').val()
			
            jQuery.ajax('/planning/update-fattura-ora',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "invoice_id": invoice_id,
					'hour_id': hourId,
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        window.location.href = "/invoices/" + result.data + '/edit';
                    } else {
	                	alert(result.message);
	                }  
                }
            });
        })


        
        $( "#btnupdate" ).click(function() {

            if($('#ora_in_modal_upd').val() == '' || $('#ora_out_modal_upd').val() == ''){
                alert('Inserisci sia l\'ora iniziale che quella finale!')
                return;
            }

            if($('#ora_in_modal_upd').val() > $('#ora_out_modal_upd').val() ){
                alert('L\'ora finale non può essere maggiore di quella iniziale!')
                return;
            }

            if($('#disciplina_modal_upd').val() == ''){
                alert('Per proseguire devi selezionare la disciplina!')
                return;
            }


            if($('#venditore_modal_upd').val() == 'M' && $('#maestro_v_modal_upd').val() == ''){
                alert('Per proseguire devi selezionare il Maestro!')
                return;
            }

			if($('#label_id_modal_upd').val() != ''){
				var client = 'L_' + $('#label_id_modal_upd').val();
			}
			if($('#cliente_id_modal_upd').val() != ''){
				var client = 'Y_' + $('#cliente_id_modal_upd_id').val();
			}
			if($('#partecipante_id_modal_upd').val() != ''){
				var client = 'T_' + $('#partecipante_id_modal_upd_id').val();
			}

            var invoice_id = $('#invoice_id_modal_upd').val()
            var ora_id = $('#record_id_modal_upd').val()
            var lista_spec = "";
            $(".specialita_modal_upd").each(function() {
                if ($(this).is(':checked')) {
                    var id_ele = $(this).attr("id").split("_")[0];
                    lista_spec = lista_spec != '' ? lista_spec + ',' + id_ele : id_ele; 
                }
                    
            });

            jQuery.ajax('/planning/update-ora',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "client": client,
                    "ora_id":ora_id,
                    "invoice_id": invoice_id,
                    "maestro": $('#maestro_modal_upd').val(),
                    "alloggio": $('#alloggio_modal_upd').val(),
                    "pax": $('#pax_modal_upd').val(),
                    "sciclub": $('#sciclub_modal_upd').is(":checked") ? 1 : 0,
                    "data_in": $('#data_in_modal_upd').val(),
                    "data_out": $('#data_out_modal_upd').val(),
                    "ora_in": $('#ora_in_modal_upd').val(),
                    "ora_out": $('#ora_out_modal_upd').val(),
                    "lista_spec" : lista_spec,
                    "ritrovo": $('#ritrovo_modal_upd').val(),
                    "disciplina": $('#disciplina_modal_upd').val(),
                    "livello": $('#livello_modal_upd').val(),
                    "venditore": $('#venditore_modal_upd').val(),
                    "branches": $('select[name="branch_id_modal_upd[]"]').val(),
                    "note": $('#note_lez_modal_upd').val(),
                    "richiesto": $('#richiesto_modal_upd').is(':checked') ? 'S' : 'N'
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        $('#modalModifica').modal('toggle');                        
                        //location.reload();
                    } 
                    else{
                        alert(result.message);
                    }
                }
            });
        })

        $( "#btndelete" ).click(function() {
            var ora_id = $('#record_id_modal_upd').val()
            var invoice_id = $('#invoice_id_modal_upd').val()
            $('#modalEliminazioneOra').modal('toggle');
            $('#oraIDRemove').val(ora_id);
            $('#invoiceIDRemove').val(invoice_id);
        })

        $("#btnRemoveOra").click(function() {
                jQuery.ajax('/planning/delete-ora', {
                    method: 'POST',
                    data: {
                        "_token": '{{ csrf_token() }}',
                        "id_ora": $('#oraIDRemove').val(),
                        "id_invoice": $('#invoiceIDRemove').val()

                    },

                    complete: function(resp) {
                        var result = JSON.parse(resp.responseText);

                        console.log(JSON.stringify(result));

                        if (result.code) {
                            $('#modalEliminazioneOra').modal('toggle');

                            location.reload();
                        } else {

                            $('#modalEliminazioneOra').modal('toggle');
                        }

                    }
                })
            });

        //fine modale modifica

        $("#closemodalAggiungi").click(function() {
            $('#modalAggiungi').modal('toggle');
        });



        $( "#btnsaveCliente" ).click(function() {
            let teachers = $('input[name="teachers[]"]:checked').map(function(){ 
                return this.value; 
            }).get();

            if(teachers.length == 0){
                alert('Per proseguire devi selezionare un Maestro!')
                return;
            }

            jQuery.ajax('/planning/insert-ora-cliente',
            {
                method: 'POST',
                data: {
                "_token": '{{ csrf_token() }}',
                "richiesto": $('#richiesto_modal_add').is(':checked') ? 'S' : 'N',
                // "maestri": $('#maestro_id_modal_addCliente').val(),
                'teachers': teachers,
                'hour_info': hourInfo,
                "aperta" : 1
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        $('#modalAddByCliente').modal('toggle');
                        location.reload()
                    }
                    else{
                        $('#modalAddByCliente').modal('toggle');
                    }         
                }
            });

        })


        $( "#btnclosedCliente" ).click(function() {
            let teachers = $('input[name="teachers[]"]:checked').map(function(){ 
                return this.value; 
            }).get();

            if(teachers.length == 0){
                alert('Per proseguire devi selezionare un Maestro!')
                return;
            }

            jQuery.ajax('/planning/insert-ora-cliente',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "richiesto": $('#richiesto_modal_add').is(':checked') ? 'S' : 'N',
                    'teachers': teachers,
                    'hour_info': hourInfo,
                    "aperta" : 0
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        $('#modalAddByCliente').modal('toggle');
                        window.location.href = "/invoices/" + result.data + '/edit';
                    }
                    else{
                        $('#modalAddByCliente').modal('toggle');
                    }         
                }
            });

        })




        $( "#btnsaveCollettivo" ).click(function() {
            jQuery.ajax('/planning/insert-ora-collettivo',
            {
                method: 'POST',
                data: {
                "_token": '{{ csrf_token() }}',
                "livello": $('#livelloCollettivo').val(),
                "eta": $('#eta_modal_add').val(),
                "colletivo_allievi_id": $('#oraIDByCollettivo').val(),
                "aperta" : 1
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        $('#modalAddByCollettivo').modal('toggle');
                        location.reload();
                    }
                    else{
                        $('#modalAddByCollettivo').modal('toggle');
                    }         
                }
            });

        })


        $( "#btnclosedCollettivo" ).click(function() {
            jQuery.ajax('/planning/insert-ora-collettivo',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "livello": $('#livelloCollettivo').val(),
                    "eta": $('#eta_modal_add').val(),
                    "colletivo_allievi_id": $('#oraIDByCollettivo').val(),
                    "aperta" : 0

                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        $('#modalAddByCollettivo').modal('toggle');
                        window.location.href = "/invoices/" + result.data + '/edit';
                    }
                    else{
                        $('#modalAddByCollettivo').modal('toggle');
                    }         
                }
            });

        })


        $( "#btnsave" ).click(function() {
            var lista_spec = "";
            $(".specialita_modal_add").each(function() {
                if ($(this).is(':checked')) 
                    lista_spec = lista_spec != '' ? lista_spec + ',' + $(this).attr("id") : $(this).attr("id"); 
            });
            $('.alert').hide();
            console.log('lista_spec: ', lista_spec)

            if($('#ora_in_modal_add').val() == '' || $('#ora_out_modal_add').val() == ''){
                alert('Inserisci sia l\'ora iniziale che quella finale!')
                return;
            }

            if($('#ora_in_modal_add').val() > $('#ora_out_modal_add').val() ){
                alert('L\'ora finale non può essere maggiore di quella iniziale!')
                return;
            }

            if($('#disciplina_modal_add').val() == ''){
                alert('Per proseguire devi selezionare la disciplina!')
                return;
            }


            if($('#collettivo_id_modal_add').val() == '' && $('#cliente_id_modal_add_id').val() == '' && $('#partecipante_id_modal_add_id').val() == '' && $('#label_id_modal_add').val() == ''){
                alert('Per proseguire devi selezionare uno tra collettivo,azienda,contatto e segnaposto!')
                return;
            }


            if($('#collettivo_id_modal_add').val() != '' && $('#partecipante_id_modal_add').val() == ''){
                alert('Per proseguire devi selezionare un partecipante!')
                return;
            }

            @if(auth()->user()->hasRole('super'))
                if($('select[name="branch_id_modal_add"]').val() == ''){
                    alert('Per proseguire devi selezionare una sede')
                    return;
                }
            @endif

            jQuery.ajax('/planning/insert-ora',
            {
                method: 'POST',
                data: {
                "_token": '{{ csrf_token() }}',
                "colletivo": $('#collettivo_id_modal_add').val(),
                "cliente": $('#cliente_id_modal_add_id').val(),
                "partecipante": $('#partecipante_id_modal_add_id').val(),
                "alloggio": $('#alloggio_modal_add').val(),
                "pax": $('#pax_modal_add').val(),
                "sciclub": $('#sciclub_modal_add').is(":checked") ? 1 : 0,
                "data_in": $('#data_in_modal_add').val(),
                "data_out": $('#data_out_modal_add').val(),
                "ora_in": $('#ora_in_modal_add').val(),
                "ora_out": $('#ora_out_modal_add').val(),
                "lista_spec" : lista_spec,
                "ritrovo": $('#ritrovo_modal_add').val(),
                "disciplina": $('#disciplina_modal_add').val(),
                "livello": $('#livello_modal_add').val(),
                "venditore": $('#venditore_modal_add').val(),
                "branches": $('select[name="branch_id_modal_add"]').val(),
                "note": $('#note_lez_modal_add').val(),
                "maestro": $('#maestro_id_modal_add').val(),
                "maestro_v": $('#maestro_v_modal_add').val(),
                "label": $('#label_id_modal_add').val(),
                "freq_C": $('#freq_C').is(":checked") ? 1 : 0,
                "freq_0": $('#freq_0').is(":checked") ? 1 : 0,
                "freq_1": $('#freq_1').is(":checked") ? 1 : 0,
                "freq_2": $('#freq_2').is(":checked") ? 1 : 0,
                "freq_3": $('#freq_3').is(":checked") ? 1 : 0,
                "freq_4": $('#freq_4').is(":checked") ? 1 : 0,
                "freq_5": $('#freq_5').is(":checked") ? 1 : 0,
                "freq_6": $('#freq_6').is(":checked") ? 1 : 0,
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        if($('#collettivo_id_modal_add').val() != ''){
                            $('#oraIDByCollettivo').val(result.data.collettivo_allievi_id)
                            $('#modalAggiungi').modal('toggle');
                            $('#modalAddByCollettivo').modal({backdrop: 'static', keyboard: false});
                        } else {
                            $('#oraIDByCliente').val(result.data.ora_id)
                            $('#modalAggiungi').modal('toggle');

                            hourInfo = result.hour_info;

                            $('#btnsaveCliente').show()
                            $('#btnclosedCliente').show()

                            console.log('teacher ID selected: ' + $('#maestro_id_modal_add').val());

                            // ADD CHECKBOXES
                            let html = '';
                            let teacher_id = $('#maestro_id_modal_add').val();

                            for (const [day, teachers] of Object.entries(result.teachers)) {
                                html += '<div class="col-md-4"><div class="mt-3 mb-2 font-weight-bold">' + day + '</div>';

                                teachers.forEach(teacher => {
                                    let checked = '';

                                    if (teacher_id == teacher.master_id)
                                        checked = 'checked';

                                    html += `
                                        <div class="form-check">
                                            <input class="form-check-input" name="teachers[]" type="checkbox" value="`+day+','+teacher.id+`" id="`+day+','+teacher.id+`" `+checked+`>
                                            <label class="form-check-label" for="`+day+','+teacher.id+`">
                                                ` + teacher.cognome + ' ' + teacher.nome + `
                                            </label>
                                        </div>`;
                                });                              
                                
                                html += '</div>';                                
                            }

                            $('#maestro_id_modal_addCliente').html('<div class="row">' + html + '</div>');

                            //$('#maestro_id_modal_addCliente').append('<input value="' + element.id +'" ' + selected + '>' + element.nome + ' ' + element.cognome + '</option>');
                            
                            /*
                                $('#btnsaveCliente').hide()
                                $('#btnclosedCliente').hide()
                                alert('Non ci sono maestri disponibili!')
                            */
                            
                            $('#modalAddByCliente').modal({backdrop: 'static', keyboard: false});
                        }
                        
                    }
                    else{
                    	console.log(result);
                    	alert(result.message);
                        $('.alert strong').text(result.message);
                        $('.alert').show();
                        $('.alert').hide();
                    }         
                }
            });

        })


        //step 2

        $("#closemodalAggiungiCollettivo").click(function() {
            $('#modalAddByCollettivo').modal('toggle');
        });

        $("#closemodalAggiungiCliente").click(function() {
            $('#modalAddByCliente').modal('toggle');
        });
        



        /*$('#cliente_id_modal_add').on('change', function(){
            console.log($(this).val())
            if($(this).val() != '')
                reloadDropDown();
        });*/


        function reloadDropDown(){
            $('#partecipante_id_modal_add').prop('disabled',false)
            jQuery.ajax('/api/list-students',
            {
                method: 'POST',
                data: {
                "_token": "{{csrf_token()}}",
                "company_id": $('#cliente_id_modal_add').val()
                },
                complete: function (resp) {
                    $("#partecipante_id_modal_add").empty();
                    if(resp !== null)
                    {
                        var result = JSON.parse(resp.responseText);
                        console.log(result)
                        if(result.length > 0){
                            $('#partecipante_id_modal_add').append( '<option value="">Partecipante</option>')
                            result.forEach(element => {
                                console.log(element)
                                $('#partecipante_id_modal_add').append( '<option value="'+element.id+'">'+element.nome + ' ' + element.cognome + '</option>' );
                            });
                        }
                        else
                            $('#partecipante_id_modal_add').prop('disabled',true)
                        
                    }
                }
            });
        }


const autoCompletejs = new autoComplete({
	data: {
		src: async () => {
			document.querySelector("#cliente_id_modal_add").setAttribute("placeholder", "Loading...");
			const source = await fetch(
				"{{url('api/ta/companies')}}"
			);
			const data = await source.json();
            console.log('RESULTS: ',data)
			document.querySelector("#cliente_id_modal_add").setAttribute("placeholder", "Cerca Aziende");
			return data;
		},
		key: ["name"],
		cache: false
	},
	sort: (a, b) => {
		if (a.match < b.match) return -1;
		if (a.match > b.match) return 1;
		return 0;
	},
	placeHolder: "Cerca Aziende",
	selector: "#cliente_id_modal_add",
	threshold: 3,
	debounce: 0,
	searchEngine: "strict",
	highlight: true,
	maxResults: 5,
	resultsList: {
		render: true,
		container: (source) => {
			source.setAttribute("id", "cliente_id_modal_add_list");
			source.setAttribute("style", "z-index: 10000; position: absolute;");
		},
		destination: document.querySelector("#cliente_id_modal_add"),
		position: "afterend",
		element: "ul"
	},
	resultItem: {
		content: (data, source) => {
			source.innerHTML = data.match;
		},
		element: "li"
	},
	noResults: () => {
		const result = document.createElement("li");
		result.setAttribute("class", "no_result");
		result.setAttribute("tabindex", "1");
		result.innerHTML = "No Results";
		document.querySelector("#cliente_id_modal_add_list").appendChild(result);
	},
	onSelection: (feedback) => {
		const selection = feedback.selection.value.name;
		document.querySelector("#cliente_id_modal_add").value = "";
		document.querySelector("#cliente_id_modal_add").value = feedback.selection.value.name; 	
		document.querySelector("#cliente_id_modal_add_id").value = feedback.selection.value.id; 	//setAttribute("placeholder", selection);
		console.log(feedback);
        //window.location.href = "{{url('companies?id=')}}"+feedback.selection.value.id;
	}
});



const autoCompleteContactsjs = new autoComplete({
	data: {
		src: async () => {
			document.querySelector("#partecipante_id_modal_add").setAttribute("placeholder", "Loading...");
			const source = await fetch(
				"{{url('api/ta/contacts')}}"
			);
			const data = await source.json();
            console.log('RESULTS: ',data)
			document.querySelector("#partecipante_id_modal_add").setAttribute("placeholder", "Cerca Privato");
			return data;
		},
		key: ["name"],
		cache: false
	},
	sort: (a, b) => {
		if (a.match < b.match) return -1;
		if (a.match > b.match) return 1;
		return 0;
	},
	placeHolder: "Cerca Privato",
	selector: "#partecipante_id_modal_add",
	threshold: 3,
	debounce: 0,
	searchEngine: "strict",
	highlight: true,
	maxResults: 5,
	resultsList: {
		render: true,
		container: (source) => {
			source.setAttribute("id", "partecipante_id_modal_add_list");
			source.setAttribute("style", "z-index: 10000; position: absolute;");
		},
		destination: document.querySelector("#partecipante_id_modal_add"),
		position: "afterend",
		element: "ul"
	},
	resultItem: {
		content: (data, source) => {
			source.innerHTML = data.match;
		},
		element: "li"
	},
	noResults: () => {
		const result = document.createElement("li");
		result.setAttribute("class", "no_result");
		result.setAttribute("tabindex", "1");
		result.innerHTML = "No Results";
		document.querySelector("#partecipante_id_modal_add_list").appendChild(result);
	},
	onSelection: (feedback) => {
		const selection = feedback.selection.value.id;
		document.querySelector("#partecipante_id_modal_add").value = "";
		document.querySelector("#partecipante_id_modal_add").value = feedback.selection.value.name; 	//setAttribute("placeholder", selection);
		document.querySelector("#partecipante_id_modal_add_id").value = feedback.selection.value.id;
		console.log(feedback);
        //window.location.href = "{{url('contacts')}}/"+feedback.selection.value.id;
	}
});


// update

const autoCompleteUpdjs = new autoComplete({
	data: {
		src: async () => {
			document.querySelector("#cliente_id_modal_upd").setAttribute("placeholder", "Loading...");
			const source = await fetch(
				"{{url('api/ta/companies')}}"
			);
			const data = await source.json();
            console.log('RESULTS: ',data)
			document.querySelector("#cliente_id_modal_upd").setAttribute("placeholder", "Cerca Aziende");
			return data;
		},
		key: ["name"],
		cache: false
	},
	sort: (a, b) => {
		if (a.match < b.match) return -1;
		if (a.match > b.match) return 1;
		return 0;
	},
	placeHolder: "Cerca Aziende",
	selector: "#cliente_id_modal_upd",
	threshold: 3,
	debounce: 0,
	searchEngine: "strict",
	highlight: true,
	maxResults: 5,
	resultsList: {
		render: true,
		container: (source) => {
			source.setAttribute("id", "cliente_id_modal_upd_list");
			source.setAttribute("style", "z-index: 10000; position: absolute;");
		},
		destination: document.querySelector("#cliente_id_modal_upd"),
		position: "afterend",
		element: "ul"
	},
	resultItem: {
		content: (data, source) => {
			source.innerHTML = data.match;
		},
		element: "li"
	},
	noResults: () => {
		const result = document.createElement("li");
		result.setAttribute("class", "no_result");
		result.setAttribute("tabindex", "1");
		result.innerHTML = "No Results";
		document.querySelector("#cliente_id_modal_upd_list").appendChild(result);
	},
	onSelection: (feedback) => {
		const selection = feedback.selection.value.name;
		document.querySelector("#cliente_id_modal_upd").value = "";
		document.querySelector("#cliente_id_modal_upd").value = feedback.selection.value.name; 	
		document.querySelector("#cliente_id_modal_upd_id").value = feedback.selection.value.id; 	//setAttribute("placeholder", selection);
		console.log(feedback);
        //window.location.href = "{{url('companies?id=')}}"+feedback.selection.value.id;
	}
});



const autoCompleteContactsUpdjs = new autoComplete({
	data: {
		src: async () => {
			document.querySelector("#partecipante_id_modal_upd").setAttribute("placeholder", "Loading...");
			const source = await fetch(
				"{{url('api/ta/contacts')}}"
			);
			const data = await source.json();
            console.log('RESULTS: ',data)
			document.querySelector("#partecipante_id_modal_upd").setAttribute("placeholder", "Cerca Privato");
			return data;
		},
		key: ["name"],
		cache: false
	},
	sort: (a, b) => {
		if (a.match < b.match) return -1;
		if (a.match > b.match) return 1;
		return 0;
	},
	placeHolder: "Cerca Privato",
	selector: "#partecipante_id_modal_upd",
	threshold: 3,
	debounce: 0,
	searchEngine: "strict",
	highlight: true,
	maxResults: 5,
	resultsList: {
		render: true,
		container: (source) => {
			source.setAttribute("id", "partecipante_id_modal_upd_list");
			source.setAttribute("style", "z-index: 10000; position: absolute;");
		},
		destination: document.querySelector("#partecipante_id_modal_upd"),
		position: "afterend",
		element: "ul"
	},
	resultItem: {
		content: (data, source) => {
			source.innerHTML = data.match;
		},
		element: "li"
	},
	noResults: () => {
		const result = document.createElement("li");
		result.setAttribute("class", "no_result");
		result.setAttribute("tabindex", "1");
		result.innerHTML = "No Results";
		document.querySelector("#partecipante_id_modal_upd_list").appendChild(result);
	},
	onSelection: (feedback) => {
		const selection = feedback.selection.value.id;
		document.querySelector("#partecipante_id_modal_upd").value = "";
		document.querySelector("#partecipante_id_modal_upd").value = feedback.selection.value.name; 	//setAttribute("placeholder", selection);
		document.querySelector("#partecipante_id_modal_upd_id").value = feedback.selection.value.id;
		console.log(feedback);
        //window.location.href = "{{url('contacts')}}/"+feedback.selection.value.id;
	}
});





</script>
@stop
