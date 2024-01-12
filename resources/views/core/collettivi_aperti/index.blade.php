@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Collettivi aperti'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
				@include('areaseb::core.collettivi_aperti.components.advanced-search', ['url_action' => 'collettivi_aperti'])
                    <br>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-php">
                            <thead>
                                <tr>
                                    <th>Collettivo</th>
									<th>Data</th>
									<th>Ora</th>
									<th>Disciplina</th>
									<th>Maestri</th>
									<th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $dd)
                                <tr>
									<td> {{$dd->collettivo}}</td>
									<td> {{ date("d/m/Y", strtotime($dd->data_inv))}}</td>
									<td> {{ 'dalle '.substr($dd->ora_in_inv,0,5).' alle '.substr($dd->ora_out_inv,0,5)}}</td>
									<td> {{$dd->disciplina_desc}}</td>
									<td> {!! $dd->maestri!!}</td>
									<td>
										<a href="/collective/{{$dd->collett_id}}" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-eye"></i></a>
										<a href="#" onclick="openModalDoc({{$dd->sede_id}},{{$dd->ora_id}},'{{$dd->sede_lbl}}')" class="btn btn-success btn-icon btn-sm"><i class="fa fa-file"></i></a>
								
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



            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/js/autoComplete.min.js"></script>

<script>

//GESTIONE DOC
function openModalDoc(sede_id, ora_id, sede_lbl){
	$('#ccID').val(sede_id)
    $('#oracID').val(ora_id)
	$('#lbl_cc').text(sede_lbl)
	$('#modalAddDoc').modal('toggle');
}

	$('select[name="prodotto_doc"]').select2({width: '100%'});
	$("#closemodalAddDoc").click(function() {
		$('#modalAddDoc').modal('toggle');
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


 $('[data-toggle="tooltip"]').tooltip();

</script>


@stop
