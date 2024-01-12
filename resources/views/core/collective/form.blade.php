<div class="col-md-12 row">
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Dettagli Corso</h3>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nome</label>
                            {!! Form::text('nome', null, ['class' => 'form-control', 'required']) !!}
                            @include('areaseb::components.add-invalid', ['element' => 'nome'])
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                        <label>Disciplina</label>
                        {!!Form::select('disciplina', [ 1 => 'Discesa', 2 => 'Fondo', 4 => 'Snowboard'], null, ['class' => 'custom-select', 'id' => 'disciplina', 'required'])!!}
                        @include('areaseb::components.add-invalid', ['element' => 'disciplina'])
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Specializzazioni</label>
                            {!! Form::select('specializzazioni[]',$specializzazioni, null, ['class' => 'select2 ucc', 'data-placeholder' => 'Associa uno o più specializzazioni', 'data-fouc', 'multiple' => 'multiple','style' => 'width:100%', 'required']) !!}
                            @include('areaseb::components.add-invalid', ['element' => 'specializzazioni'])
                        </div>
                    </div>

                </div>

                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Data Ora inizio</label>
                            <input type="datetime-local" class="form-control" id="data_in" name="data_in">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Data Ora fine</label>
                            <input type="datetime-local" class="form-control" id="data_out" name="data_out">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class=" card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Frequenza</h3>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input name="is_continuous" value="1" class="custom-control-input" type="checkbox" id="is_continuous">
                                <label for="is_continuous" class="custom-control-label">Continuativo</label>
                            </div>
                        </div>
                        <div class="form-group">
                        <label>oppure</label>
                        {!!Form::select('giorni[]', [ 1 => 'Lunedì', 2 => 'Martedì', 3 => 'Mercoledì',  4 => 'Giovedì',  5 => 'Venerdì',  6 => 'Sabato',  0 => 'Domenica'], null, ['class' => 'custom-select',  'multiple' => 'multiple'])!!}                       
                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                         <label>Centro di costo</label>
                            {!! Form::select('branch_id',$branches, null, ['class' => 'custom-select', 'data-placeholder' => 'Associa un Centro di costo', 'id' => 'branch_id','style' => 'width:100%']) !!}
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>



    <div class="col-md-6">
        <div class="card">
            <button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm"><i class="fa fa-save"></i> Salva</button>
        </div>
    </div>

</div>


@section('scripts')
<script src="{{asset('plugins/jquery-ui/jquery-ui.min.js')}}"></script>
<script>
    var data_in = '<?php echo isset($collective) && $collective->data_in != null ? $collective->data_in.'T'.$collective->ora_in : "null"; ?>';
    var data_out = '<?php echo isset($collective) && $collective->data_out != null ? $collective->data_out.'T'.$collective->ora_out : "null"; ?>';


    var centro_costo = <?php echo isset($collective) && $collective->centro_costo != null ? '"'.str_replace(',', '', $collective->centro_costo).'"' : "null"; ?>;
    var is_continuos = <?php echo isset($collective) && $collective->frequenza != null && $collective->frequenza == 'C' ? 'true' : 'false'; ?>;

    var specializations = <?php echo isset($collective) && $collective->specialita != null ? json_encode(explode(",", $collective->specialita)) : "null"; ?>;
    var giorni = <?php echo isset($collective) && $collective->frequenza != null && $collective->frequenza != 'C' ? json_encode(explode(",",$collective->frequenza)) : "null"; ?>;

    console.log(giorni);
    $( document ).ready(function() {
        $('select[name="specializzazioni[]"]').select2({width: '100%'});
        $('select[name="giorni[]"]').select2({width: '100%'});

        $('#data_in').val(data_in)
        $('#data_out').val(data_out)
        if(giorni != null){
            $('select[name="giorni[]"]').val(giorni);
            $('select[name="giorni[]"]').trigger('change');
        }
        if(specializations != null){
            $('select[name="specializzazioni[]"]').val(specializations);
            $('select[name="specializzazioni[]"]').trigger('change');
        }

        if(centro_costo != null){
            $('#branch_id').val(centro_costo);
        }
        if(is_continuos){
            $('select[name="giorni[]"]').prop('disabled', true)
            $('#is_continuous').prop('checked', true)
        }
            
    });

    

    $('.add-select').select2({tags: true, placeholder: 'Posizione'});
    $('.add-tag').select2({tags: true, placeholder: 'Origine contatto'});

    //$('input[name="ordine"]').prop('disabled',true)
    $('#div-disabile').hide();

    $('input[name="citta"').on('focusout', function(){
        $.post("{{route('api.city.zip')}}", {_token: token, citta: $(this).val()}).done(function( data ) {
            $('input[name="cap"]').val(data.cap);
            $('select[name="provincia"]').val(data.provincia);
            $('select[name="provincia"]').trigger('change');
        });
    });

    function prefix()
    {
        $.post("{{url('api/countries')}}", {
            _token: $('input[name="_token"]').val(),
            iso: $('select#country').find(':selected').val()
        }).done(function(data){
            if(data !== null)
            {
                $('span#changePrefix').text('+'+data);
                if(data != '39')
                {
                    $('select#provincia').select2('destroy').hide();
                    $('input#region').show();
                }
                else
                {
                    $('select#provincia').select2().show();
                    $('input#region').hide();
                }
            }
        });
    }

    prefix();

    $('select#country').on('change', function(){
        prefix();
    });

    $('#is_continuous').on('change', function(){
        if ($(this).is(':checked')) 
            $('select[name="giorni[]"]').prop('disabled', true)
        else
            $('select[name="giorni[]"]').prop('disabled', false)
    });


    $('select[name="giorni[]"]').on('change', function(){
        if($(this).val() != '')
            $('#is_continuous').prop('disabled', true)
        else
            $('#is_continuous').prop('disabled', false)
    });




    $('select#branch_id').on('change', function(){
        $.post("{{route('offices.maxNumOrder')}}", {_token: token, branch_id: $(this).val()}).done(function( data ) {
            $('input[name="ordine"]').val(data.ordine);
        });
    });


    $('#is_disabile').click(function() { 
        $('#div-disabile').hide();
        if ($(this).is(':checked')) { 
            $('#div-disabile').show();
        } 
    });





    $('button#submitForm').on('click', function(e){
        e.preventDefault();
        let region = $('input#region');
        let province = $('select#provincia');

        if(region.val() == '')
        {
            region.val(province.val());
        }
        if(province.val() == '')
        {
            province.val(region.val());
        }

        $('form#collectiveForm').submit();
    })

</script>
@stop
