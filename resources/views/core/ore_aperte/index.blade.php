@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Ore aperte'])


@section('content')
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
									<a class="btn btn-primary" href="#" onclick="scegliOperazione(1)" style="margin-left:10px">Fattura TUTTE le ore aperte</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
				@include('areaseb::core.ore_aperte.components.advanced-search', ['url_action' => 'ore_aperte'])
                    <br>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-php">
                            <thead>
                                <tr>
                                    <th>Sede</th>
                                    <th>Cliente</th>
									<th>Data</th>
									<th>Ora</th>
									<th>Disciplina</th>
									<th>Maestro</th>
									<th>Importo</th>
									<th></th>
									<th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $dd)
                                <tr>
                                    <td> {{$dd->sede_lbl}}</td>
									<td> {{$dd->cliente}}</td>
									<td> {{ date("d/m/Y", strtotime($dd->data_inv))}}</td>
									<td> {{ 'dalle '.substr($dd->ora_in_inv,0,5).' alle '.substr($dd->ora_out_inv,0,5)}}</td>
									<td> {{$dd->disciplina_desc}}</td>
									<td> {{$dd->maestro}}</td>
									<td> {{'€ '.$dd->importo}}</td>
									<td>
										@if($dd->tipo == 'Y')
											<a href="/companies/{{$dd->cliente_id}}" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-eye"></i></a>
										@else
											<a href="/contacts/{{$dd->cliente_id}}" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-eye"></i></a>
										@endif
										<a href="#" onclick="openModalDoc({{$dd->sede_id}},{{$dd->ora_id}},'{{$dd->sede_lbl}}')" class="btn btn-success btn-icon btn-sm"><i class="fa fa-file"></i></a>
										
									</td>
									<td>
										<input type="checkbox" id="{{$dd->item_id}}" data-oraid="{{$dd->ora_id}}" class="cbOp">
									</td>
                                </tr>
                                
                                @endforeach
                                
                            </tbody>
                        </table>
                    </div>
                </div>



				<div class="modal fade" id="modalAddDoc" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5>Inserisci i dati del documento in cui aggiungere l'ora</h5>
                                <h5 id="ccID" hidden></h5>
                                <h5 id="oracID" hidden></h5>
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



                <div class="modal fade" id="modalAddDocs" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
                    aria-hidden="true">
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
        </div>
    </div>
@stop

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/js/autoComplete.min.js"></script>

<script>
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


function openModalDoc(sede_id, ora_id, sede_lbl){
	$('#ccID').val(sede_id)
    $('#oracID').val(ora_id)
	$('#lbl_cc').text(sede_lbl)
    clearFieldAddDoc();
	$('#modalAddDoc').modal('toggle');
}

function openModalDocs(){
    clearFieldAddDoc();
    $('#modalOperazione').modal('toggle');
	$('#modalAddDocs').modal('toggle');
}




$("#closemodalAddDocs").click(function() {
	$('#modalAddDocs').modal('toggle');
});

$("#closemodalAddDoc").click(function() {
	$('#modalAddDoc').modal('toggle');
});


$("#closemodalOperazione").click(function() {
	$('#modalOperazione').modal('toggle');
});

$( "#btnAddDoc" ).click(function() {
            var ora_id = $('#oracID').val()
            jQuery.ajax('/planning/add-document-ora',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "tipo_doc": $('#tipo_doc').val(),
                    "n_doc": $('#n_doc').val(),
                    "data_doc": $('#data_doc').val(),
                    "prodotto_doc":$('select[name="prodotto_doc"]').val(),
                    "ora_id":ora_id
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        if(result.data != null)
                            window.location.href = "/ore_aperte";
                        else
                            $('.alert-info-add-doc').show();
                    }     
                }
            });
})


$( "#btnAddDocs" ).click(function() {
    var all = $('#sel_totale').val();
    var arrStr = "";
    var cb = $('.cbOp');
    
	if(all == 1){
		$.each(cb, function() {
			arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");
    	}); 
	}
	else{
		$.each(cb, function() {
			var $this = $(this);
			if($this.is(":checked")) {
				arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");
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
        },

        complete: function (resp) {
            $('.alert').hide();
            var result = JSON.parse(resp.responseText);
            if(result.code){
                if(result.data != null)
                    window.location.href = "/ore_aperte";
                else
                    $('.alert-info-add-doc').show();
            }     
        }
    });
})



function scegliOperazione(all){
	var arrStr = "";
	var cb = $('.cbOp');
    
	if(all == 1){
		$.each(cb, function() {
			arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");
    	}); 
	}
	else{
		$.each(cb, function() {
			var $this = $(this);
			if($this.is(":checked")) {
				arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");
			}
    	}); 
	}

	console.log('array: ', arrStr);
    if(arrStr == ''){
        alert('Per proseguire devi selezionare almeno un Elemento nella tabella!')
        return;
    }

    $('#sel_totale').val(all);
    $('#modalOperazione').modal('toggle');
}

function fatturare(){
    $('#modalOperazione').modal('toggle');
    var all = $('#sel_totale').val();
	var arrStr = "";
	var cb = $('.cbOp');
    
	if(all == 1){
		$.each(cb, function() {
			arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("id") : $(this).attr("id");
    	}); 
	}
	else{
		$.each(cb, function() {
			var $this = $(this);
			if($this.is(":checked")) {
				arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("id") : $(this).attr("id");
			}
    	}); 
	}

	console.log('array: ', arrStr);
    if(arrStr == ''){
        alert('Per proseguire devi selezionare almeno un Elemento nella tabella!')
        return;
    }

	jQuery.ajax('/ore_aperte/fatturare',
	{
		method: 'POST',
		data: {
			"_token": '{{ csrf_token() }}',
			"ids_invoice": arrStr,
		},

		complete: function (resp) {
			var result = JSON.parse(resp.responseText);
			if(result.code){
					window.location.href = "/ore_aperte";
			}     
		}
	});
}


 $('[data-toggle="tooltip"]').tooltip();



</script>


@stop
