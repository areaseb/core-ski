<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Dati</h3>
        </div>
        <div class="card-body">


                <div class="form-group">
                    <label>Luogo</label>
                    {!! Form::text('luogo', null, ['class' => 'form-control', 'required']) !!}
                </div>
                <div class="form-group">
                    <label>Hotel</label>
                    {!! Form::text('hotel', null, ['class' => 'form-control', 'required']) !!}
                </div>
                <div class="form-group">
                    <label>Sede</label>
                    @php
                    	if(isset($housing)){
                    		$select = explode(',', $housing->centro_costo);
                    	} else {
                    		$select = null;
                    	}
                    	
                    @endphp
                    {!! Form::select('branch_id[]',$branches, $select, ['class' => 'select2 ucc', 'data-placeholder' => 'Associa uno o più sedi', 'data-fouc', 'multiple' => 'multiple','style' => 'width:100%']) !!}
                </div>
        </div>
    </div>

    <div class="card">

<button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm"><i class="fa fa-save"></i> Salva</button>

</div>
</div>


@section('scripts')
<script>
    $('select[name="branch_id[]"]').select2({width: '100%'});
    $('button#submitForm').on('click', function(e){
        e.preventDefault();
        $('form#alloggioForm').submit();
    });
</script>
@stop
