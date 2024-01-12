<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Dati</h3>
        </div>
        <div class="card-body">
			<div class="row">
				<div class="col-md-8">
					<div class="form-group">
	                    <label>Nome</label>
	                    {!! Form::text('nome', null, ['class' => 'form-control', 'required']) !!}
	                </div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
	                    <label>Colore</label><br>
	                    @php
	                    	if(isset($label)){
	                    		$select = $label->colore;
	                    	} else {
	                    		$select = '#0000ff';
	                    	}
	                    	
	                    @endphp
                        <input type="color" id="color" name="colore" value="{{$select}}">
	                </div>
				</div>
			</div>

                

        </div>
    </div>

    <div class="card">

<button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm"><i class="fa fa-save"></i> Salva</button>

</div>
</div>


@section('scripts')
<script>

    $('button#submitForm').on('click', function(e){
        e.preventDefault();
        $('form#labelForm').submit();
    });
</script>
@stop
