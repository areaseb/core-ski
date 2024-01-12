@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}companies">Clienti</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Unisci Clienti'])


@section('content')

    {!! Form::open(['url' => route('api.companies.mergedb'), 'autocomplete' => 'off', 'id' => 'mergeForm']) !!}
        <div class="row">
            {!! Form::hidden('previous', url()->previous()) !!}
            @include('areaseb::components.errors')
            
            <div class="col-md-12">
			    <div class="card card-outline card-primary">
			        <div class="card-header">
			            <h3 class="card-title">Unisci clienti</h3>
			        </div>
			        <div class="card-body">
						<div class="row">
			                <div class="p-4 col-md-4">
			                    <label>Cliente 1</label>
			                    {!!Form::select('company1', $companies, null, ['class' => 'custom-select'])!!}
			                    <br>
			                    <label>Tipo</label>
			                    {!!Form::select('company1_tipo', ['T' => 'Contatto', 'Y' => 'Azienda'], null, ['class' => 'custom-select'])!!}
			                    <br>
			                    <div id="tipo_contatto">
				                    <label>Tipo contatto</label>
				                    {!!Form::select('company1_tipo_contatto', ['1' => 'Genitore', '2' => 'Figlio'], null, ['class' => 'custom-select'])!!}
				                </div>
			                </div>
			                <div class="p-4 col-md-4">
			                    <label>Cliente 2 (destinazione)</label>
			                    {!!Form::select('company2', $companies, null, ['class' => 'custom-select'])!!}
			                </div>
			                <div class="p-4 col-md-4">
			                	<br>
			                	<button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm"><i class="fas fa-link"></i> Unisci</button>
			                </div>
						</div>
						<div class="row">
							<div class="col-md-12 p-4">
								<p>I dati del Cliente 1 verranno spostati sotto il Cliente 2 e poi verrà eliminato. Alla fine ne resterà soltanto uno 😂</p>
							</div>
						</div>
		            </div>
		        </div>
			</div>            
            
        </div>
    {!! Form::close() !!}

@stop

@section('scripts')
<script>
$('select[name="company1"]').select2({width: '100%'});
$('select[name="company2"]').select2({width: '100%'});
$('select[name="company1_tipo"]').select2({width: '100%'});
$('select[name="company1_tipo_contatto"]').select2({width: '100%'});

$('select[name="company1_tipo"]').change( function() {
	console.log($('select[name="company1_tipo"]').val());
	if($('select[name="company1_tipo"]').val() == 'Y'){
		$('#tipo_contatto').hide();
	} else {
		$('#tipo_contatto').show();
	}
});

</script>
@stop