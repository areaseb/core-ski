@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}companies">Contatti</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => $contact->fullname])

@section('content')

    <div class="row">

        <div class="col-md-3">

            <div class="card card-info card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        {!!$contact->avatar!!}
                        <h3 class="profile-username text-center">{{$contact->fullname}}</h3>
                        @if($contact->clients)
                            <p class="text-muted text-center">
                                @foreach($contact->clients as $type)
                                    @if($loop->last)
                                        {{$type->nome}}
                                    @else
                                        {{$type->nome}},
                                    @endif
                                @endforeach
                            </p>
                        @endif
                        @if($contact->company_id)
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item text-center" style="line-height:1rem;">
                                    <a href="{{$contact->company->url}}" >
                                        {{$contact->company->rag_soc}}
                                    </a>
                                </li>
                            </ul>
                        @endif
                        @can('contacts.write')
                            <a href="{{$contact->url}}/edit" class="btn btn-sm btn-warning btn-block"><b> <i class="fa fa-edit"></i> Modifica</b></a>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Dettagli</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Indirizzo</strong>
                    <p class="text-muted">{{$contact->indirizzo}} <br>
                        {{$contact->cap}}, {{$contact->citta}} {{$contact->provincia}} {{$contact->nazione}}
                    </p>
                    <hr>

                    <strong><i class="fas fa-at mr-1"></i> Contatti</strong>
                    @if($contact->cellulare)<p class="text-muted"><b>Tel:</b> {{$contact->cellulare}}</p>@endif
                    @if($contact->email)<p class="text-muted"><b>Email:</b> <small>{{$contact->email}}</small></p>@endif
                    <hr>
                    
                    <strong><i class="fas fa-lock mr-1"></i> Privacy</strong>
                    <p class="text-muted"><b>Accettazione:</b> @if($contact->privacy) Sì @else No @endif</p>
                    <hr>
                    
                    <strong><i class="fas fa-info-circle mr-1"></i> Dettagli</strong>
                    <p class="text-muted"><b>Livello:</b> 
                    	@php
                    		switch($contact->livello){
                    			case 'ELE':
                    				$liv = 'Elementare';
                    				break;
                    			case 'PRA':
                    				$liv = 'Primo Approccio';
                    				break;
                    			case 'BAS':
                    				$liv = 'Base';
                    				break;
                    			case 'AVA':
                    				$liv = 'Avanzato';
                    				break;
                    			case 'INT':
                    				$liv = 'Intermedio';
                    				break;
                    			default:
                    				$liv = '';
                    				break;
                    		}
                    	@endphp
                    	{{$liv}}
                    </p>
                    <p class="text-muted"><b>Età:</b> 
                    	@php
                    		if($contact->data_nascita){
	                    		list($a, $m, $g) = explode('-', $contact->data_nascita);
	                    		$eta = date('Y') - $a;
                    		} else {
                    			$eta = 0;
                    		}
                    		
                    	@endphp
                    	{{$eta}}
                    </p>
                    <hr>
	
					@if($contact->isDisabled($contact->id))
	                    <strong><i class="fa fa-wheelchair mr-1"></i> Disabilità</strong>
	                    <p class="text-muted">
		                    {!! $contact->disability !!}
	                	</p>
	                @endif
                </div>
                <div class="card-footer p-0">
                    <a href="#" class="btn btn-sm btn-secondary btn-block print"><i class="fa fa-print"></i> Stampa</a>
                </div>
            </div>
        </div>


        <div class="col-md-9">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link" id="tab-notes" href="#notes" data-toggle="tab">Note</a></li>                            
                       	<li class="nav-item"><a class="nav-link active" id="tab-reports" href="#reports" data-toggle="tab">Reports</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-storia" href="#storia" data-toggle="tab">Storico ore</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-ore" href="#ore" data-toggle="tab">Ore aperte</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-collettivi" href="#collettivi" data-toggle="tab">Collettivi</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-fatture" href="#fatture" data-toggle="tab">Fatture</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-prodotti" href="#prodotti" data-toggle="tab">Prodotti venduti</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-eventi" href="#eventi" data-toggle="tab">Eventi</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane" id="notes">
                            @include('areaseb::core.contacts.components.notes')
                        </div>   
                        <div class="active tab-pane active" id="reports">
                            @include('areaseb::core.contacts.components.reports')
                        </div>
                        <div class="tab-pane" id="storia">
                                @include('areaseb::core.contacts.components.storico_ore')
                        </div>
                        <div class="tab-pane" id="ore">
                                @include('areaseb::core.contacts.components.ore')
                        </div>
                        <div class="tab-pane" id="collettivi">
                                @include('areaseb::core.contacts.components.collettivi')
                        </div>
                        <div class="tab-pane" id="fatture">
                                @include('areaseb::core.contacts.components.invoices')
                        </div>
                        <div class="tab-pane" id="prodotti">
                                @include('areaseb::core.contacts.components.products')
                        </div>
                        <div class="tab-pane" id="eventi">
                                @include('areaseb::core.contacts.components.events')
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="modalAddDoc" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Inserisci i dati del documento in cui aggiungere l'ora</h5>
                        <h5 id="ccID" hidden></h5>
                        <h5 id="oracID" hidden></h5>                   
                        <h5 id="invoiceID" hidden></h5>
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


        <div class="modal fade" id="modalAddDocs" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Inserisci i dati del documento in cui aggiungere gli Items selezionati</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                            <b>Tipo di documento: </b>
                                <select class="form-control"  id="tipo_doc_m">
                                    <option value="R">Ricevuta</option>
                                    <option value="F">Fattura</option>
                                    <option value="D">Documento</option>
                                    <option value="A">Nota di accredito</option>
                                </select>
                            </div> 
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <b>N° documento: </b><input type="text" id="n_doc_m" class="form-control">
                            </div> 
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <b>Data documento: </b><input type="date" id="data_doc_m" class="form-control">
                            </div> 
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                            <b>Prodotto:</b>
                            {!! Form::select('prodotto_doc_m',$products, null, ['class' => 'select2 ucc', 'data-fouc', 'style' => 'width:100%']) !!}

                            </div> 
                        </div>

                        <br>
                        <div class="alert alert-info alert-info-add-doc" style="display:none">
                            <strong>Attenzione!</strong> Documento non trovato!
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="closemodalAddDocs">Annulla</button>
                        <button type="button" class="btn btn-success" id="btnAddDocs">Inserisci</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="modalOperazione" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Scegli operazione da effettuare</h5>
                        <h5 id="sel_totale" hidden></h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <h5>Vuoi fatturare tutto in una sola fattura o associare ad un documento già esistente?</h5> 
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="closemodalOperazione">Annulla</button>
                        <button type="button" class="btn btn-primary" onclick="fatturare()">Fattura</button>
                        <button type="button" class="btn btn-success" onclick="openModalDocs()">Aggiungi a Documento</button>
                    </div>
                </div>
            </div>
        </div>


    </div>

@stop

@section('scripts')
<script>
    $('a.print').on('click', function(e){
        window.print();
    });


    $('select[name="prodotto_doc"]').select2({width: '100%'});
    $('select[name="prodotto_doc_m"]').select2({width: '100%'});

    function clearFieldAddDoc(){
        $('#tipo_doc_m').val('');
        $('#n_doc_m').val('');
        $('#data_doc_m').val('');
        $('select[name="prodotto_doc_m"]').val('')

        $('#tipo_doc').val('');
        $('#n_doc').val('');
        $('#data_doc').val('');
        $('select[name="prodotto_doc"]').val('')
    }


    var s = sessionStorage.getItem("tab-customer");
    if(s != undefined){
        $('#tab-notes').removeClass('active');
        $('#notes').removeClass('active');

        $('#tab-' + s).addClass('active');
        $('#' + s).addClass('active');
        sessionStorage.clear();
    }



    function openModalDoc(sede_id, ora_id, sede_lbl, invoice_id = null){
        $('#ccID').val(sede_id)
        $('#oracID').val(ora_id)
        $('#lbl_cc').text(sede_lbl)
        $('#invoiceID').val(invoice_id)
        clearFieldAddDoc();
        $('#modalAddDoc').modal('toggle');
    }

    function openModalDocs(){
        clearFieldAddDoc();
        $('#modalOperazione').modal('toggle');
        $('#modalAddDocs').modal('toggle');
    }


    $("#closemodalAddDoc").click(function() {
        $('#modalAddDoc').modal('toggle');
    });

    $("#closemodalAddDocs").click(function() {
	    $('#modalAddDocs').modal('toggle');
    });

    $("#closemodalOperazione").click(function() {
        $('#modalOperazione').modal('toggle');
    });


    $( "#btnAddDoc" ).click(function() {
            var ora_id = $('#oracID').val()
            var invoice_id = $('#invoiceID').val()
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
                        if(result.data != null){
                            var tab = $('.tab-content .active').attr('id');
                            sessionStorage.setItem("tab-customer", tab);
                            location.reload();
                        }
                        else
                            $('.alert-info-add-doc').show();
                    }     
                }
            });
    })

    $( "#btnAddDocs" ).click(function() {
        var all = $('#sel_totale').val();
        var arrStr = "";
        var arrInvoices = '';
        var cb = $('.cbOp');
        
        if(all == 1){
            $.each(cb, function() {
                arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");
				arrInvoices = arrInvoices != '' ? arrInvoices + ',' + $(this).attr("data-invoiceid") : $(this).attr("data-invoiceid");
            }); 
        }
        else{
            $.each(cb, function() {
                var $this = $(this);
                if($this.is(":checked")) {
                    arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");
					arrInvoices = arrInvoices != '' ? arrInvoices + ',' + $(this).attr("data-invoiceid") : $(this).attr("data-invoiceid");
                }
            }); 
        }
        
        jQuery.ajax('/planning/add-documents-item',
        {
            method: 'POST',
            data: {
                "_token": '{{ csrf_token() }}',
                "tipo_doc": $('#tipo_doc_m').val(),
                "n_doc": $('#n_doc_m').val(),
                "data_doc": $('#data_doc_m').val(),
                "prodotto_doc":$('select[name="prodotto_doc_m"]').val(),
                "ids_ore": arrStr,
				'ids_invoices': arrInvoices,
            },

            complete: function (resp) {
                $('.alert').hide();
                var result = JSON.parse(resp.responseText);
                if(result.code){
                    if(result.data != null){
                        //var tab = $('.tab-content .active').attr('id');
                        //sessionStorage.setItem("tab-customer", tab);
                        //location.reload();
                        location.href = '/invoices/'+result.data+'/edit';
                    }
                    else
                        $('.alert-info-add-doc').show();
                }     
            }
        });
    })


        function scegliOperazione(all){
            var arrStr = "";
	        var arrOre = '';
	        var arrInvo = '';
            var cb = $('.cbOp');

            if(all == 1){
                $.each(cb, function() {
                    arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("id") : $(this).attr("id");   
	                arrOre = arrOre != '' ? arrOre + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");  
	                arrInvo = arrInvo != '' ? arrInvo + ',' + $(this).attr("data-invoiceid") : $(this).attr("data-invoiceid"); 
                }); 
            }
            else{
                $.each(cb, function() {
                    var $this = $(this);
                    if($this.is(":checked")) {
                        arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("id") : $(this).attr("id");
	                    arrOre = arrOre != '' ? arrOre + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");  
	                	arrInvo = arrInvo != '' ? arrInvo + ',' + $(this).attr("data-invoiceid") : $(this).attr("data-invoiceid"); 
                    }
                }); 
            }

            console.log('array: ', arrStr);
            /*if(arrStr == ''){
                alert('Per proseguire devi selezionare almeno un Elemento nella tabella!')
                return;
            }*/
            if(arrOre == ''){
	            alert('Per proseguire devi selezionare almeno un Elemento nella tabella!')
	            return;
	        }
            $('#sel_totale').val(all);
            $('#modalOperazione').modal('toggle');
        }




        function fatturare(){
            var all = $('#sel_totale').val();
            var arrStr = "";
	        var arrOre = '';
	        var arrInvo = '';
            var cb = $('.cbOp');

            if(all == 1){
                $.each(cb, function() {
                    arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("id") : $(this).attr("id");   
	                arrOre = arrOre != '' ? arrOre + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");  
	                arrInvo = arrInvo != '' ? arrInvo + ',' + $(this).attr("data-invoiceid") : $(this).attr("data-invoiceid"); 
                }); 
            }
            else{
                $.each(cb, function() {
                    var $this = $(this);
                    if($this.is(":checked")) {
                        arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("id") : $(this).attr("id");
	                    arrOre = arrOre != '' ? arrOre + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");  
	                	arrInvo = arrInvo != '' ? arrInvo + ',' + $(this).attr("data-invoiceid") : $(this).attr("data-invoiceid");
                    }
                }); 
            }

            console.log('array: ', arrStr);
            /*if(arrStr == ''){
                alert('Per proseguire devi selezionare almeno un Elemento nella tabella!')
                return;
            }*/
            if(arrOre == ''){
	            alert('Per proseguire devi selezionare almeno un Elemento nella tabella!')
	            return;
	        }

            jQuery.ajax('/ore_aperte/fatturare',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "ids_invoice": arrInvo,
	                "ids_ore": arrOre,
	                "ids_item": arrStr,
                },

                complete: function (resp) {
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        
                        var tab = $('.tab-content .active').attr('id');
                        console.log(tab);
                        sessionStorage.setItem("tab-customer",tab);
                        location.href = '/invoices/'+result.id+'/edit';
                        //location.reload();
                    }     
                }
            });
        }

</script>
@stop
