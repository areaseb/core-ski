<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Nominativo</h3>
        </div>
        <div class="card-body">

                <div class="form-group" style="width:30%" >
                    <label>Tipologia</label>
                    {!!Form::select('private', [1 => 'Persona fisica',0 => 'Azienda'], null, ['class' => 'custom-select', 'id' => 'privato'])!!}
                </div>

            @if(isset($company))
                <div class="form-group">
                    <label id="lbl_rag_soc">Ragione Sociale</label>
                    {!! Form::text('rag_soc', null, ['class' => 'form-control', 'required']) !!}
                    @include('areaseb::components.add-invalid', ['element' => 'rag_soc'])
                </div>
            @else
                <div class="form-group">
                    <label id="lbl_rag_soc">Ragione Sociale</label>
                    <div class="input-group">
                        {!! Form::text('rag_soc', null, ['class' => 'form-control', 'required']) !!}
                        <!--<div class="input-group-append">
                            <div class="input-group-text">
                                <input id="create-contact" type="checkbox" checked="" name="createContact"> &nbsp;<small>crea contatto</small>
                            </div>
                        </div>-->
                    </div>
                    @include('areaseb::components.add-invalid', ['element' => 'rag_soc'])
                </div>

                <div class="row" id="insertCreateContact">
                    <div class="col-md-4">
                        <div class="form-group">

                            {!! Form::text('nome', null, ['class' => 'form-control', 'placeholder' => 'Nome (obbligatorio)']) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">

                            {!! Form::text('cognome', null, ['class' => 'form-control', 'placeholder' => 'Cognome (obbligatorio)']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::select('contact_type_id', $contact_type, null, ['class' => 'form-control', 'id' => 'contact_type_id', 'placeholder' => 'Tipologia Contatto']) !!}
                        </div>
                    </div>
                </div>
            @endif



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
                        {!!Form::text('address', null, ['class' => 'form-control', 'placeholder' => 'Indirizzo'])!!}
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Città</label>
                {!!Form::text('city', null, ['class' => 'form-control', 'placeholder' => 'Città'])!!}
            </div>
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label>CAP</label>
                        {!!Form::text('zip', null, ['class' => 'form-control', 'placeholder' => 'CAP'])!!}
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

            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label>Luogo di nascita</label>
                        {!!Form::text('luogo_nascita', null, ['class' => 'form-control', 'placeholder' => 'Città'])!!}
                    </div>
                </div>

                <div class="col-4">
                    <div class="form-group">
                        <label>Data di nascita</label>
                        {!!Form::date('data_nascita', null, ['class' => 'form-control'])!!}
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label>Nickname</label>
                        {!!Form::text('nickname', null, ['class' => 'form-control', 'placeholder' => 'Nickname'])!!}
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>Sesso</label>
                        <select name="sesso" id="sesso" class="form-control">
                            <option value="M">Maschio</option>
                            <option value="F">Femmina</option>
                        </select>
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
                                {!!Form::text('lat', null, ['class' => 'form-control', 'placeholder' => 'Latitudine: 45.50840732201837'])!!}
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text input-group-text-sm">LNG</span>
                                </div>
                                {!!Form::text('lng', null, ['class' => 'form-control', 'placeholder' => 'Longitudine: 11.626233561358067'])!!}
                            </div>
                        </div>
                    </div>
                </div>

            @endif

            <div class="form-group">
                <label>Email principale</label>
                {!!Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Indirizzo Email', 'autocomplete' => 'off', 'data-type' => 'email'])!!}
            </div>
            @if($user->roles->first()->name == 'super')
	            <div class="row">
	                <div class="col-md-6">
	                    <div class="form-group">
	                        <label>Email ordini</label>
	                        {!!Form::text('email_ordini', null, ['class' => 'form-control', 'placeholder' => 'Indirizzo email per ordini', 'autocomplete' => 'off', 'data-type' => 'email'])!!}
	                    </div>
	                </div>
	                <div class="col-md-6">
	                    <div class="form-group">
	                        <label>Email fatturazione</label>
	                        {!!Form::text('email_fatture', null, ['class' => 'form-control', 'placeholder' => 'Indirizzo email per fatture', 'autocomplete' => 'off', 'data-type' => 'email'])!!}
	                    </div>
	                </div>
	            </div>
	        @endif

            <div class="row">
                <div class="col-6 form-group">
                    <label>Settore</label>
                    <div class="input-group">
                        {!!Form::text('settore', null, ['class' => 'form-control', 'placeholder' => 'Settore'])!!}
                    </div>
                </div>

                <div class="col-6 form-group">
                        <label>Attivo</label>
                        {!!Form::select('active',['1' => 'Sì','0' => 'No'] , null, ['class' => 'custom-select'])!!}
                </div>
            </div>

            <div class="form-group">
                <label>Telefono</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="changePrefixPhone"></span>
                    </div>
                    {!!Form::text('phone', null, ['class' => 'form-control', 'placeholder' => 'Telefono'])!!}
                </div>
            </div>


            <div class="form-group">
                <label>Cellulare</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="changePrefixMobile"></span>
                    </div>
                    {!!Form::text('mobile', null, ['class' => 'form-control', 'placeholder' => 'Cellulare'])!!}
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
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>SDI</label>
                        @php
                            $sdi = null;
                            if(isset($company))
                            {
                                $sdi = $company->sdi;
                            }
                        @endphp
                        {!!Form::text('sdi', $sdi, ['class' => 'form-control', 'id' => 'sdi', 'placeholder' => 'Identificativo e-fattura', 'maxlength' => '7'])!!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>PEC</label>
                        {!!Form::text('pec', null, ['class' => 'form-control', 'placeholder' => 'PEC', 'data-type' => 'email'])!!}
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

                        {!!Form::text('piva', $piva, ['class' => 'form-control', 'placeholder' => 'Partita iva'])!!}
                        @include('areaseb::components.add-invalid', ['element' => 'piva'])
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>CF</label>
                        {!!Form::text('cf', null, ['class' => 'form-control', 'placeholder' => 'Codice fiscale'])!!}
                    </div>
                </div>
            </div>

            <div class="row @if($user->roles->first()->name != 'super') d-none @endif">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Esenzione Default</label>
                        {!!Form::select('exemption_id', $exemptions, null, ['class' => 'form-control', 'placeholder' => 'Esenzione automatica'])!!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Pagamento Default</label>
                        {!!Form::select('pagamento', config('invoice.payment_types'), null, ['class' => 'form-control'])!!}
                    </div>
                </div>

            </div>

            <div class="form-group @if($user->roles->first()->name != 'super') d-none @endif">
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
    
    <div class="card card-outline card-warning @if(!$user->can('companies.type')) d-none @endif">
        <div class="card-header">
            <h3 class="card-title">Tipologia</h3>
        </div>
        <div class="card-body pb-0">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Tipo</label>
                        @php
                            if(isset($company))
                            {
                                $client = $company->client_id;
                            }
                            else
                            {
                                $client = 2;
                            }
                        @endphp
                        {!! Form::select('client_id', $clients, $client, [
                            'class' => 'form-control',
                            'data-placeholder' => 'Seleziona una tipologia di cliente',
                            'style' => 'width:100%',
                            'required']) !!}
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Fornitore</label>
                        {!!Form::select('supplier',['0' => 'No', '1' => 'Sì'] , null, ['class' => 'custom-select'])!!}
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Partner</label>
                        {!!Form::select('partner',['0' => 'No', '1' => 'Sì'] , null, ['class' => 'custom-select'])!!}
                    </div>
                </div>

                <div class="col-6 d-none" id="parentSelect">
                    <div class="form-group">
                        <label>Azienda Madre</label>
                        {!!Form::select('parent_id',$referenti, null, ['class' => 'select2bs4'])!!}
                    </div>
                </div>

                <div class="col-6">
                    <div class="form-group">
                        <label>Origine</label>
                        {!!Form::select('origin', $origins, isset($company) ? $company->origin : 'Manuale', ['class' => 'form-control'])!!}
                    </div>
                </div>

                <div class="col-6">
                    <div class="form-group">
                        <label>Categoria</label>
                        {!!Form::select('sector_id', $sectors, null, ['class' => 'form-control add-select'])!!}
                        <p class="text-muted"><small>Scrivi per creare una nuova categoria</small></p>
                    </div>
                </div>


                <div class="col-6">
                    <div class="form-group">
                        <label>Sede</label>
                        @php
                        
                        	if(isset($company) && $company->branches->count() > 0){
                        		$branch_selected = $company->branches->first()->branch_id;
                        	} else {
                        		if(auth()->user()->hasRole('super')){
                        			$branch_selected = null;
                        		} else {
                        			$branch_selected = auth()->user()->contact->branchContact()->branch_id;
                        		}  
                        	} 
                        @endphp
                        
                        {!! Form::select('branch_id[]',$branches, $branch_selected, ['class' => 'select2 ucc', 'data-placeholder' => 'Associa uno o più sedi', 'data-fouc', 'multiple' => 'multiple','style' => 'width:100%']) !!}
                    </div>
                </div>

                @if(\Illuminate\Support\Facades\Schema::hasTable('testimonial_company'))
                    @php
                        $testimonial = null;
                        if(isset($company))
                        {
                            if($company->testimonial()->exists())
                            {
                                $testimonial = $company->testimonial()->first()->id;
                            }
                        }
                    @endphp

                    <div class="col-6">
                        <div class="form-group">
                            <label>Testimonial</label>
                            {!!Form::select('testimonial_id', $testimonials, $testimonial, ['class' => 'custom-select'])!!}
                        </div>
                    </div>
                @endif


                @if(\Illuminate\Support\Facades\Schema::hasTable('agent_company'))
                    @php
                        $agent = null;
                        if(isset($company))
                        {
                            if($company->agent()->exists())
                            {
                                $agent = $company->agent()->first()->id;
                            }
                        }
                    @endphp

                    <div class="col-6">
                        <div class="form-group">
                            <label>Agente</label>
                            {!!Form::select('agent_id', $agents, $agent, ['class' => 'custom-select'])!!}
                        </div>
                    </div>
                @endif

                <input value="{{$byplanning}}" name="byplanning" type="hidden">
                @if(isset($date)) <input value="{{$date}}" name="date" type="hidden"> @endif

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
$('select#privato').prop("selectedIndex", 1);

//SETTAGGI INIZIALE CON PRIVATO = SI
@if(!isset($company))
	$('#sdi').val('0000000');
@endif

@if(isset($company) && $company->nation == 'IT')
	$('select#provincia').select2().show();
	$('input#region').hide();
@elseif(isset($company) && $company->nation != 'IT')
	$('select#provincia').select2('destroy').hide();
	$('input#region').show();
@endif


$("#contact_type_id").find('[value="4"]').remove();

var branches = <?php echo isset($company) && $company->listBranches() != null && count($company->listBranches()) > 0 ? json_encode($company->listBranches()) : "null"; ?>;
console.log('branches:', branches)
var p_iva = '<?php echo isset($company) && $company->piva != null ? $company->piva : "null"; ?>';
var sdi = '<?php echo isset($company) && $company->sdi != null ? strval($company->sdi) : "null"; ?>';
if(sdi == 0){
	sdi = '0000000';
}
console.log('sdi:', sdi);
var sesso = '<?php echo isset($company) && $company->sesso != null ? $company->sesso : "null"; ?>';

$( document ).ready(function() {
    $('input[name="piva"]').prop("disabled", true);
    if(branches != null){
        $('select[name="branch_id[]"]').val(branches);
        $('select[name="branch_id[]"]').trigger('change');
    }

    if(p_iva != null && p_iva != 'null'){
        //$('input[name="piva"]').prop("disabled", false);
        $('select#privato').val(0)
        visibilityField();
    }
    else{
        $('input[name="piva"]').val('')
        $('select#privato').val(1)
        visibilityField();
        //$('input[name="piva"]').prop("disabled", true);
    }

    if(sesso != null){
        //$('input[name="piva"]').prop("disabled", false);
        $('select#sesso').val(sesso)
    }
});


const toggleCreateContact = (bool) => {

    let html = `
        <div class="col-md-4">
            <div class="form-group">
                <input class="form-control" placeholder="Nome (obbligatorio)" name="nome" type="text" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <input class="form-control" placeholder="Cognome (obbligatorio)" name="cognome" type="text" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::select('contact_type_id', $contact_type, null, ['class' => 'form-control', 'id' => 'contact_type_id', 'placeholder' => 'Tipologia Contatto']) !!}
            </div>
        </div>
    `;

    if(bool)
    {
        $('#insertCreateContact').html(html);
    }
    else
    {
        $('#insertCreateContact').html('');
    }
}


//toggleCreateContact($('input#create-contact').is(":checked"))
visibilityField();

$('input#create-contact').on('change', function(){
    toggleCreateContact($('input#create-contact').is(":checked"))
    if(!this.checked) {
        $('select#privato').val(0)
        $('input[name="piva"]').prop("disabled", false);
    }
    else{
        $('select#privato').val(1)
        $('input[name="piva"]').prop("disabled", true);
    }
});



    $('.add-select').select2({tags: true, placeholder: 'Associa ad una categoria', width:'100%', allowClear:true});
    $('select[name="exemption_id"]').select2({placeholder: 'Esenzione di default preimpostata', allowClear:true, width: '100%'});
/*    $('select[name="exemption_id"]').val(12);
    $('select[name="exemption_id"]').trigger('change');*/


    $('select[name="pagamento"]').select2({placeholder: 'Metodo Pagamento', allowClear:true, width: '100%'});
    $('select[name="pagamento"]').val('RIDI');
    $('select[name="pagamento"]').trigger('change');

    $('select[name="origin"]').select2({placeholder: 'Provenienza', allowClear:true, tags: true, width: '100%'});
    $('select[name="client_id"]').select2({placeholder: 'Seleziona una tipologia di cliente', width: '100%'});
    $('select[name="client_id"]').val(3);
    $('select[name="client_id"]').trigger('change');

    $('select[name="sector_id"]').select2({placeholder: 'Associa almeno una Categoria', allowClear:true, tags: true, width: '100%'});
    //$('select[name="contact_type_id"]').select2({placeholder: 'Tipologia Contatto', allowClear:true, width: '100%'});

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

    
    function visibilityField(){
        toggleCreateContact($('select#privato').val() == 1)
        $('#contact_type_id').prop("disabled", false);
        if($('select#country').val() == 'IT'){
                if($('select#privato').val() == 1)
                    $('#sdi').val('0000000');
                else
                    $('#sdi').val(sdi != null ? sdi : '');
        }

            $('input[name="piva"]').prop("disabled", false);
            $('input[name="rag_soc"]').show();
            $('#lbl_rag_soc').show();

            if($('select#privato').val() == 1 && $('input[name="rag_soc"]').val() == ''){
                $('input[name="piva"]').prop("disabled", true);
                $('input[name="rag_soc"]').hide();
                $('#lbl_rag_soc').hide();
                $('#contact_type_id').val(1)
                //$('#contact_type_id').prop("disabled", true);
            }   
            
            if($('select#privato').val() == 1 && $('input[name="rag_soc"]').val() != ''){
            	$('input[name="piva"]').prop("disabled", true);
                $('input[name="rag_soc"]').show();
                $('#lbl_rag_soc').show();
                $('#contact_type_id').val(1)
                //$('#contact_type_id').prop("disabled", true);
            }
    }


    $('select#country').on('change', function(){
        prefix();
        visibilityField();
    });


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
        let branch_id = $('select[name="branch_id[]"]');

        if(region.val() == '')
        {
            region.val(province.val());
        }
        if(province.val() == '')
        {
            province.val(region.val());
        }
        console.log('branch_id: ', branch_id.val()[0]);
        if(branch_id.val()[0] == '')
        {
            alert('Ricordati di selezionare almeno una Sede.')
            return;
        }

        if($('input[name="rag_soc"]').val() == ''){
            let rag_soc = $('input[name="cognome"]').val() + ' ' + $('input[name="nome"]').val();

            $('input[name="rag_soc"]').val(rag_soc);
        }
        

        $('form#companyForm').submit();
    });

    @if(request()->has('q'))

        let field = "{{request()->get('q')}}";
        $('input[name="'+field+'"]').addClass('is-invalid');
        err("Compila i campi obbligatori prima di inviare la fattura")

    @endif

</script>
@stop
