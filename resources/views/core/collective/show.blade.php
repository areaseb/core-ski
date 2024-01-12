@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}collective">Gestione Collettivi</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => $collective->nome])

@section('content')

    <div class="row">

        <div class="col-md-2">

            <div class="card card-info card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <h3 class="profile-username text-center">{{$collective->nome}}</h3>
                        <a href="{{$collective->id}}/edit" class="btn btn-sm btn-warning btn-block"><b> <i class="fa fa-edit"></i> Modifica</b></a>

                    </div>
                </div>
            </div>

            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Dettagli</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fa fa-info mr-1"></i> Info</strong>
                    <p class="text-muted"><b>Data inizio:</b> {{ date('d/m/Y', strtotime($collective->data_in))}} 
                    <p class="text-muted"><b>Data fine:</b> {{ date('d/m/Y', strtotime($collective->data_out))}} 

                    <p class="text-muted"><b>Ora inizio:</b> {{$collective->ora_in}}
                    <p class="text-muted"><b>Ora inizio:</b> {{$collective->ora_out}}

                    @if($collective->frequenza != null)<p class="text-muted"><b>Frequenza:</b> {!! $collective->frequenza!!} </p>@endif
                    @if($collective->disciplina != null)<p class="text-muted"><b>Disciplina:</b> {{$collective->disciplina}} </p>@endif
                    @if($collective->specialita != null)<p class="text-muted"><b>Specializzazione:</b> {{$collective->specialita}} </p>@endif
                    <p class="text-muted"><b>Maestri:</b> <br>
                    	@php
                    		$masters = array();
	                    	foreach($availabilities as $availability){
	                    		foreach($availability as $data => $maestri){
	                    			foreach($maestri as $maestro){
	                    				$masters[] = "$maestro->cognome $maestro->nome";
	                    			}
	                    		}
	                    	}	   
	                    @endphp
	                    @foreach(array_unique($masters) as $master)
	                    	{{ $master }}<br>
	                    @endforeach
	                </p>
                    @if($collective->centro_costo != null)<p class="text-muted"><b>Centro di costo:</b> {{$collective->centro_costo}} </p>@endif

                </div>
                <div class="card-footer p-0">
                    <a href="#" class="btn btn-sm btn-secondary btn-block print"><i class="fa fa-print"></i> Stampa</a>
                </div>
            </div>
        </div>


        <div class="col-md-10">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" id="tab-allievi" href="#allievi" data-toggle="tab">Allievi</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-acconti" href="#acconti" data-toggle="tab">Situazione Contabile</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-liste" href="#liste" data-toggle="tab">Liste giornaliere</a></li>

                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                            <div class="tab-pane active" id="allievi">
                            @include('areaseb::core.collective.components.students')
                            </div>
                            <div class="tab-pane" id="acconti">
                            @include('areaseb::core.collective.components.accounting_situation')
                            </div>
                            <div class="tab-pane" id="liste">
                            @include('areaseb::core.collective.components.daily-lists')
                            </div>

                    </div>
                </div>
            </div>
        </div>


    </div>

@stop

@section('scripts')
<script src="{{asset('plugins/jquery-ui/jquery-ui.min.js')}}"></script>
<script>

    var s = sessionStorage.getItem("moveStudente");
    if(s != undefined){
        $('#tab-allievi').removeClass('active');
        $('#allievi').removeClass('active');

        $('#tab-liste').addClass('active');
        $('#liste').addClass('active');
        sessionStorage.clear();
    }
    $('#livello').prop('disabled', true)

    $('select#company_id').on('change', function(){
        console.log($(this).val())
        reloadDropDown();
    });

    $('select#contact_id').on('change', function(){
        console.log($(this).val())
        reloadDataStudent();
    });

    $( document ).ready(function() {

        

        $('#imposta_sortable').click(function(e) {
            e.stopPropagation();
            console.log('attiva_sortable');
            $('tbody').sortable({
            opacity: 0.6,
            items: '> tr:not(:first)',
            appendTo: 'parent',
            helper: 'clone'
            }).disableSelection();
        });

        $('#imposta_droppable').click(function(e) {
            e.stopPropagation();
            console.log('attiva_droppable');  
            $('.droppable_class').droppable({
                accept: '.draggable_class' ,
                hoverClass: 'droppable_hover',
                tolerance: 'pointer', // il mouse deve essere sopra il drop
                drop: function(e, ui) {

                    console.log('drop');
                    // POPOLARE OGGETTI VERIFICANDO VALIDAZIONE Dati
                    //Dati 'Da'
                    var id_row_collettivo_allievi = ui.draggable.attr('id_row_collettivo_allievi');
                    var rif_da = ui.draggable.attr('rif');
                    var rif_a = $(this).attr('rif');
                    if(rif_da == rif_a){
                        alert('Spostamento non corretto');
                        return false;
                    }

					let data_da = rif_da.split("-");
					let data_a = rif_a.split("-");

					data_da.shift();
					data_a.shift();

					data_da = data_da.join('-');
					data_a = data_a.join('-');

                    var id_maestro_da = rif_da.split("-")[0].substring(1);
                    var id_maestro_a = rif_a.split("-")[0].substring(1);

                    var id_row_collettivo_allievi = ui.draggable.attr('id_row_collettivo_allievi');
                    var allievo_da = ui.draggable.find('td').eq(1).text();
                    allievo_da = allievo_da.trim();

                    var maestro_da = $(rif_da + ' table tr:first td:first').text().trim();
                    var maestro_a = $(rif_a + ' table td:first').text().trim();

                    var r=confirm('Confermi lo spostamento dell\'Allievo '+allievo_da+' \n DA : \n data : '+data_da+' \n maestro : '+maestro_da+' \n A : \n data : '+data_a+' \n maestro : '+maestro_a+' ');										

                    if (r===true){
                        // AJAX
						/* DEBUG
						alert('data_da ' + data_da)
                        alert('data_a ' + data_a)
                        alert('id_row_collettivo_allievi ' + id_row_collettivo_allievi)
                        alert('id_maestro_da ' + id_maestro_da)
                        alert('id_maestro_a ' + id_maestro_a)
						*/

                        console.log('implementa chiamata Ajax');
                        jQuery.ajax('/api/moveStudent', {
                            method: 'POST',
                            data: {
                                "_token": "{{csrf_token()}}",
                                "data_da": data_da,
                                "data_a": data_a,
                                "id_row_collettivo_allievi":id_row_collettivo_allievi,
                                "id_maestro_da":id_maestro_da,
                                "id_maestro_a":id_maestro_a,
                            },
                            success: function(resp) {
                                var result = JSON.parse(resp);
                                console.log('result: ', result)
                                if (result.code) {
                                    var ajax_return=alert('Allievo gestito correttamente');

									sessionStorage.setItem("moveStudente","true");

                                    window.location.href = window.location.href + '?cache=' + Date.now();
                                } else {

                                    alert('Spostamento allievo non riuscito!');
									window.location.href = window.location.href + '?cache=' + Date.now();
                                }

                                //$(rif_a + ' table tr:last').after('<tr>' + ui.draggable.html() + '</tr>');
                                                    //ui.draggable.remove();

								
                                var data = {};
                                    data.type_key = 'ajax';
                                    data.id = id_collettivo;
                                upload_data_asynchronously(data);
                                //}
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                            	alert('ERRORE di ritorno della chiamata Ajax!');
                            	$('.loader').fadeOut('slow');
                            },
                    	});
                    }else{
                        alert('dati non completi, modifica interrotta');
                    }
                    }
                });          
        });


        $('#imposta_sortable').trigger('click');
        $('#imposta_droppable').trigger('click');
    });
</script>
@stop
