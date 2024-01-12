<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Nominativo</h3>
        </div>
        <div class="card-body">

            <div class="form-group">
                <label>Ragione Sociale</label>
                {!! Form::text('rag_soc', null, ['class' => 'form-control', 'required']) !!}
                @include('areaseb::components.add-invalid', ['element' => 'rag_soc'])
            </div>


            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Nazione</label>
                        {!! Form::select('nation', $countries, null, ['class' => 'custom-select', 'id' => 'country']) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label>Lingua</label>
                        {!! Form::select('lang', \Areaseb\Core\Models\Setting::ActiveLangsArray(), null, ['class' => 'custom-select', 'id' => 'country']) !!}
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Indirizzo</label>
                        {!!Form::text('address', null, ['class' => 'form-control'])!!}
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Città</label>
                {!!Form::text('city', null, ['class' => 'form-control'])!!}
            </div>
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label>CAP</label>
                        {!!Form::text('zip', null, ['class' => 'form-control'])!!}
                    </div>
                </div>
                <div class="col-8">
                    <div class="form-group">
                        <label>Provincia</label>

                        {!!Form::text('province', null, [
                            'class' => 'form-control',
                            'placeholder' =>'Regione Estera',
                            'id' => 'region'])!!}

                        {!! Form::select('province', $provinces, null, [
                            'class' => 'form-control select2bs4',
                            'data-placeholder' => 'Seleziona una provincia',
                            'style' => 'width:100%',
                            'id' => 'provincia']) !!}
                    </div>
                </div>
            </div>

            @if(class_exists(\Areaseb\Maps\MapsServiceProvider::class))

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text input-group-text-sm">LAT</span>
                                </div>
                                {!!Form::text('lat', null, ['class' => 'form-control'])!!}
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text input-group-text-sm">LNG</span>
                                </div>
                                {!!Form::text('lng', null, ['class' => 'form-control'])!!}
                            </div>
                        </div>
                    </div>
                </div>

            @endif

            <div class="form-group">
                <label>Email principale</label>
                {!!Form::text('email', null, ['class' => 'form-control', 'autocomplete' => 'off', 'data-type' => 'email'])!!}
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email ordini</label>
                        {!!Form::text('email_ordini', null, ['class' => 'form-control', 'autocomplete' => 'off', 'data-type' => 'email'])!!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email fatturazione</label>
                        {!!Form::text('email_fatture', null, ['class' => 'form-control', 'autocomplete' => 'off', 'data-type' => 'email'])!!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6 form-group">
                    <label>Settore</label>
                    <div class="input-group">
                        {!!Form::text('settore', null, ['class' => 'form-control'])!!}
                    </div>
                </div>

                <div class="col-6 form-group">
                        <label>Attivo</label>
                        {!!Form::select('active',['1' => 'Sì','0' => 'No' ] , null, ['class' => 'custom-select'])!!}
                </div>
            </div>

            <div class="form-group">
                <label>Telefono</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="changePrefixPhone"></span>
                    </div>
                    {!!Form::text('phone', null, ['class' => 'form-control'])!!}
                </div>
            </div>


            <div class="form-group">
                <label>Cellulare</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="changePrefixMobile"></span>
                    </div>
                    {!!Form::text('mobile', null, ['class' => 'form-control'])!!}
                </div>
            </div>

            <div class="form-group">
                <div class="form-group">
                    <label>Sitoweb</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-globe"></i></span>
                        </div>
                        {!!Form::text('website', null, ['class' => 'form-control', 'placeholder' => 'con https://'])!!}
                    </div>
                </div>
            </div>

        </div>
    </div>


</div>

<div class="col-md-6">
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Fatturazione</h3>
        </div>
        <div class="card-body pb-0">

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipologia</label>
                        {!!Form::select('private', [0 => 'Azienda', 1 => 'Persona fisica'], null, ['class' => 'custom-select', 'id' => 'privato'])!!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>SDI</label>
                        {!!Form::text('sdi', null, ['class' => 'form-control', 'id' => 'sdi', 'maxlength' => '7'])!!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>PEC</label>
                        {!!Form::text('pec', null, ['class' => 'form-control', 'data-type' => 'email'])!!}
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>P.IVA</label>

@php
    $piva = null;
    if(isset($company))
    {
        $piva = $company->clean_piva;
    }
@endphp

                        {!!Form::text('piva', $piva, ['class' => 'form-control'])!!}
                        @include('areaseb::components.add-invalid', ['element' => 'piva'])
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>CF</label>
                        {!!Form::text('cf', null, ['class' => 'form-control'])!!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Esenzione Default</label>
                        {!!Form::select('exemption_id', $exemptions, 12, ['class' => 'form-control', 'placeholder' => 'Esenzione automatica'])!!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Pagamento Default</label>
                        {!!Form::select('pagamento', config('invoice.payment_types'), 'RIDI', ['class' => 'form-control'])!!}
                    </div>
                </div>

            </div>

            <div class="form-group">
                <label>Sconto Default</label>
                <div class="row">
                    <div class="col relative">
                        <div class="input-group">
                            {!! Form::number('s1', null, ['class' => 'form-control input-decimal', 'min' => 0, 'max' => 99]) !!}
                            <div class="input-group-append">
                                <span class="input-group-text input-group-text-sm" id="basic-addon2">%</span>
                            </div>
                            @include('areaseb::components.add-invalid', ['element' => 's1'])
                        </div>
                        <span class="abs plus">+</span>
                    </div>
                    <div class="col relative">
                        <div class="input-group">
                            {!! Form::number('s2', null, ['class' => 'form-control input-decimal', 'min' => 0, 'max' => 99]) !!}
                            <div class="input-group-append">
                                <span class="input-group-text input-group-text-sm" id="basic-addon2">%</span>
                            </div>
                            @include('areaseb::components.add-invalid', ['element' => 's2'])
                        </div>
                        <span class="abs plus">+</span>
                    </div>
                    <div class="col">
                        <div class="input-group">
                            {!! Form::number('s3', null, ['class' => 'form-control input-decimal', 'min' => 0, 'max' => 99]) !!}
                            @include('areaseb::components.add-invalid', ['element' => 's3'])
                            <div class="input-group-append">
                                <span class="input-group-text input-group-text-sm" id="basic-addon2">%</span>
                            </div>
                        </div>
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
$('select[name="branch_id[]"]').select2({width: '100%'});
//SETTAGGI INIZIALE CON PRIVATO = NO
//$('#sdi').val('0000000');
//$('input[name="piva"]').prop("disabled", true);



    $('.add-select').select2({tags: true, width:'100%', allowClear:true});
    $('select[name="exemption_id"]').select2({allowClear:true, width: '100%'});
    $('select[name="pagamento"]').select2({allowClear:true, width: '100%'});
    $('select[name="origin"]').select2({allowClear:true, tags: true, width: '100%'});
    $('select[name="client_id"]').select2({width: '100%'});
    $('select[name="sector_id"]').select2({allowClear:true, tags: true, width: '100%'});
    $('select[name="contact_type_id"]').select2({allowClear:true, width: '100%'});

    $('input[name="city"').on('focusout', function(){
        $.post("{{route('api.city.zip')}}", {_token: token, citta: $(this).val()}).done(function( data ) {
            $('input[name="zip"]').val(data.cap);
            $('select[name="province"]').val(data.provincia);
            $('select[name="province"]').trigger('change');
        });
    });

    function prefix()
    {
        $.post("{{url('api/countries')}}", {
            _token: token,
            iso: $('select#country').find(':selected').val()
        }).done(function(data){
            if(data !== null)
            {
                $('span#changePrefixPhone').text('+'+data);
                $('span#changePrefixMobile').text('+'+data);
                if(data != '39')
                {
                    $('select#provincia').select2('destroy').hide();
                    $('input#region').show();
                    if($('input[name="sdi"]').val() == '')
                    {
                        $('input[name="sdi"]').val('XXXXXXX');
                    }
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
        visibilityField();
    });

    function visibilityField(){
        if($('select#country').val() == 'IT'){
                if($('select#privato').val() == 1)
                    $('#sdi').val('0000000');
                else
                    $('#sdi').val('');
            }  

            $('input[name="piva"]').prop("disabled", false);

            if($('select#privato').val() == 1){
                $('input[name="piva"]').prop("disabled", true);
            }   
    }


    $('select#privato').on('change', function(){
        visibilityField();
    });


    // function adjustFatturazione()
    // {
    //     let pec = $('input[name="pec"]');
    //     let pec = $('input[name="pec"]');
    // }
    //
    // adjustFatturazione()
    //
    // $('select[name="privato"]').on('change', function(){
    //     adjustFatturazione();
    // });

    let selection = $('select.tipologia').select2("data");
    isReferente(selection);

    function isReferente(selection)
    {
        selection = $('select.tipologia').select2("data");
        let check = 0
        $.each(selection, function(i,v){
            if(v.text == 'Referente')
            {
                check++;
            }
        });
        if(check > 0)
        {
            $('div#parentSelect').removeClass('d-none');
        }
        else
        {
            $('div#parentSelect').addClass('d-none');
        }
    }

    $('select.tipologia').on('change', function(e){
        let selection = $(this).select2("data");
        isReferente(selection);
    });

    // $('input[name="piva"]').on('focusout', function(){
    //     $('input[name="cf"]').val($(this).val());
    // });


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

        $('form#officeForm').submit();
    });

    @if(request()->has('q'))

        let field = "{{request()->get('q')}}";
        $('input[name="'+field+'"]').addClass('is-invalid');
        err("Compila i campi obbligatori prima di inviare la fattura")

    @endif

</script>
@stop
