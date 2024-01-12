@php
    $company = Areaseb\Core\Models\Company::find(request('company_id'));

    if($company == null && isset($contact)){
        $company = Areaseb\Core\Models\Company::find($contact->company_id);
    } 
    
    $branches_list = $company != null ? $company->branches()->get() : [];
		
    $branches_company = [];
    foreach ($branches_list as $value) {
        array_push($branches_company,$value->branch_id);
    }
    if(count($branches_company) == 0){
		$branches_company[] = $contact->branchContact()->branch_id;
	}
 
    if(isset($contact)){
	    if($contact->branchContact()){
	    	$user_branch = $contact->branchContact()->branch_id;
	    } else {
	    	$user_branch = null;
	    }    	
    } else {
    	$user_branch = null;
    }
@endphp
<div class="col-md-12 row">
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Nominativo</h3>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipologia</label>
                            {!! Form::select('contact_type_id', $contact_type, null, ['class' => 'form-control', 'id' => 'contact_type_id', 'placeholder' => 'Tipologia Contatto']) !!}
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group">
                            <label>Nome</label>
                            {!! Form::text('nome', null, ['class' => 'form-control', 'required']) !!}
                            @include('areaseb::components.add-invalid', ['element' => 'nome'])
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Cognome</label>
                    {!! Form::text('cognome', null, ['class' => 'form-control', 'required']) !!}
                    @include('areaseb::components.add-invalid', ['element' => 'cognome'])
                </div>

                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <label>Codice Fiscale</label>
                            {!!Form::text('cod_fiscale', null, ['class' => 'form-control', 'placeholder' => 'Codice Fiscale'])!!}
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Luogo di nascita</label>
                            {!!Form::text('luogo_nascita', null, ['class' => 'form-control', 'placeholder' => 'Città'])!!}
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form-group">
                            <label>Data di nascita</label>
                            {!!Form::date('data_nascita', null, ['class' => 'form-control',])!!}
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

                <div class="col-4">
                    <div class="form-group">
                        <label>Livello</label>
                        <select name="livello" id="livello" class='form-control'>
                            <option value="PRA">Primo Approccio</option>
                            <option value="ELE">Elementare</option>
                            <option value="BAS">Base</option>
                            <option value="INT">Intermedio</option>
                            <option value="AVA">Avanzato</option>
                        </select>
                    </div>
                </div>

            </div>


            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Note</label>
                        {!!Form::textarea('note_contact', null, ['class' => 'form-control'])!!}
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Note segreteria</label>
                        {!!Form::textarea('note_segreteria', null, ['class' => 'form-control'])!!}
                    </div>
                </div>

            </div>


                <div class="form-group">
                    <label>Email</label>
                    {!!Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Indirizzo Email', 'required', 'autocomplete' => 'off'])!!}
                    @include('areaseb::components.add-invalid', ['element' => 'email'])
                </div>

                <div class="form-group">
                    <label>Mobile</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="changePrefix"></span>
                        </div>
                        {!!Form::text('cellulare', null, ['class' => 'form-control', 'placeholder' => 'Cellulare'])!!}
                    </div>
                </div>

                <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input name="is_disabile" value="1" class="custom-control-input" type="checkbox" id="is_disabile">
                            <label for="is_disabile" class="custom-control-label">Disabile</label>
                        </div>
                </div>

                <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input name="privacy" value="1" class="custom-control-input" type="checkbox" id="privacy">
                            <label for="privacy" class="custom-control-label">Accettazione Privacy</label>
                        </div>
                </div>



            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class=" card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Indirizzo</h3>
            </div>
            <div class="card-body">

                <div class="form-group">
                    <label>Nazione</label>
                    {!! Form::select('nazione', $countries, null, ['class' => 'custom-select', 'id' => 'country']) !!}
                </div>

                <div class="form-group">
                    <label>Indirizzo</label>
                    {!!Form::text('indirizzo', null, ['class' => 'form-control', 'placeholder' => 'Indirizzo'])!!}
                </div>
                <div class="form-group">
                    <label>Città</label>
                    {!!Form::text('citta', null, ['class' => 'form-control', 'placeholder' => 'Città'])!!}
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <label>CAP</label>
                            {!!Form::text('cap', null, ['class' => 'form-control', 'placeholder' => 'CAP'])!!}
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="form-group">
                            <label>Provincia</label>

                            {!!Form::text('provincia', null, [
                                'class' => 'form-control',
                                'placeholder' =>'Regione Estera',
                                'id' => 'region'])!!}

                            {!! Form::select('provincia', $provinces, null, [
                                'class' => 'form-control select2bs4',
                                'data-placeholder' => 'Seleziona una provincia',
                                'style' => 'width:100%',
                                'id' => 'provincia']) !!}
                            </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


<div class="col-md-12" id="div-disabile">

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Dati disabilità</h3>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipologia</label>
                            {!! Form::select('disabile_tipo_id', $disabile_tipo, null, ['class' => 'form-control', 'id' => 'disabile_tipo_id', 'placeholder' => 'Tipologia']) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                        <label>Attrezzo</label>
                            {!! Form::select('disabile_attrezzi_id', $disabile_attrezzi, null, ['class' => 'form-control', 'id' => 'disabile_attrezzi_id', 'placeholder' => 'Attrezzo']) !!}
                       
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Altezza (cm)</label>
                            {!! Form::text('altezza', null, ['class' => 'form-control', 'required']) !!}
                            @include('areaseb::components.add-invalid', ['element' => 'altezza'])
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Peso (Kg)</label>
                            {!!Form::text('peso', null, ['class' => 'form-control',  'required', 'autocomplete' => 'off'])!!}
                            @include('areaseb::components.add-invalid', ['element' => 'peso'])
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Larghzza bacino (cm)</label>
                            {!! Form::text('bacino', null, ['class' => 'form-control', 'required']) !!}
                            @include('areaseb::components.add-invalid', ['element' => 'bacino'])
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Seduta</label>
                            {!! Form::select('disabile_sedute_id', $disabile_sedute, null, ['class' => 'form-control', 'id' => 'disabile_sedute_id', 'placeholder' => 'Seduta']) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                        <label>Mezzi di sintesi</label>
                            {!!Form::select('sintesi', [1 => 'Si', 0 => 'No'], null, ['class' => 'custom-select', 'id' => 'sintesi'])!!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Catetere</label>
                            {!!Form::select('catetere', [1 => 'Si', 0 => 'No'], null, ['class' => 'custom-select', 'id' => 'catetere'])!!}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Note</label>
                            {!! Form::textarea('note', null, ['class' => 'form-control', 'required']) !!}
                            @include('areaseb::components.add-invalid', ['element' => 'note'])
                        </div>
                    </div>

                </div>



            </div>
        </div>
</div>



<div class="col-md-12" id="div-maestro">

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Dati aggiuntivi Maestro</h3>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Colore</label>
                            <br>
                            <input type="color" id="color" name="color" value="#0000ff">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                        <label>Tipo socio</label>
                        {!!Form::select('tipo_socio', [3 => 'Socio professionale', 2 => 'Socio partner', 1 => 'Socio aspirante', 0 => 'Non socio'], null, ['class' => 'custom-select', 'id' => 'tipo_socio'])!!}                       
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Disciplina</label>
                            {!!Form::select('disciplina', [ 0 => 'Discesa', 1 => 'Fondo', 2 => 'Snowboard'], null, ['class' => 'custom-select', 'id' => 'disciplina'])!!}                       

                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Specializzazioni</label>
                            {!! Form::select('specializzazioni[]',$specializzazioni, null, ['class' => 'select2 ucc', 'data-placeholder' => 'Associa uno o più specializzazioni', 'data-fouc', 'multiple' => 'multiple','style' => 'width:100%']) !!}                 

                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Iscr.collegio</label>
                            {!! Form::text('collegio', null, ['class' => 'form-control', 'required']) !!}
                            @include('areaseb::components.add-invalid', ['element' => 'collegio'])
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ordine</label>
                            {!! Form::text('ordine', null, ['class' => 'form-control', 'required']) !!}
                        </div>
                    </div>

                </div>


            </div>
        </div>
</div>



<div class="col-md-12 row">
    <div class="col-md-6">
        <div class="card card-outline card-warning pad-tab">
            <div class="card-header">
                <h3 class="card-title">Associa Contatto</h3>
            </div>
            <div class="card-body">

                <div class="form-group">
                    <label>Cliente</label>
                    {!! Form::select('company_id', $companies, request('company_id'), [
                        'class' => 'form-control select2bs4',
                        'data-placeholder' => "Seleziona un'cliente",
                        'style' => 'width:100%']) !!}
                        <small><a href="{{url('companies/create')}}" target="_BLANK"><i class="fa fa-plus"></i> Crea un nuovo cliente</a></small>
                </div>

                <div class="form-group">
                    <label>Utente</label>
                    {!! Form::select('user_id', $users, null, [
                        'class' => 'form-control select2bs4',
                        'data-placeholder' => "Seleziona un utente",
                        'style' => 'width:100%']) !!}
                </div>

            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-outline card-danger pad-tab">
            <div class="card-header">
                <h3 class="card-title">Tipologia</h3>
            </div>
            <div class="card-body">
                <div class="row">


                    @php
                        $subscribed = 0;
                        if(isset($contact))
                        {
                            $subscribed = $contact->subscribed;
                        }
                    @endphp

                    <div class="col-6">
                        <div class="form-group">
                            <label>Newsletter</label>
                            {!! Form::select('subscribed', [0=>'No',1=>'Sì'], $subscribed, [
                                'class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form-group">
                            <label>Liste</label>
                            @isset($contact)
                                {!! Form::select('list_id[]', $lists, $contact->lists()->pluck('lists.id'), [
                                    'class' => 'form-control select2bs4',
                                    'multiple' => 'multiple',
                                    'data-placeholder' => 'Aggiungi contatto ad una lista',
                                    'style' => 'width:100%']) !!}
                            @else
                                {!! Form::select('list_id[]', $lists, old('list_id') ?? null, [
                                    'class' => 'form-control select2bs4',
                                    'multiple' => 'multiple',
                                    'data-placeholder' => 'Aggiungi contatto ad una lista',
                                    'style' => 'width:100%']) !!}
                            @endisset
                        </div>
                    </div>

                    @php

                        $origin = 'Altro';
                        if(isset($contact))
                        {
                            $origin = $contact->origin;
                        }
                    @endphp

                    <div class="col-4">
                        <div class="form-group">
                            <label>Origine</label>
                            {!! Form::select('origin', $origins, $origin, [
                                'class' => 'form-control add-tag',
                                'data-placeholder' => 'Origine del cliente',
                                'style' => 'width:100%',
                                'required']) !!}
                        </div>
                    </div>


                    <div class="col-4">
                        <div class="form-group">
                        <label>Attivo</label>
                            {!!Form::select('attivo', [1 => 'Si', 0 => 'No'], null, ['class' => 'custom-select', 'id' => 'attivo'])!!}
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form-group">
                            <label>Sedi</label>
                            {!! Form::select('branch_id[]',$branches, $user_branch, ['class' => 'select2 ucc', 'data-placeholder' => 'Associa uno o più sedi', 'data-fouc', 'multiple' => 'multiple','style' => 'width:100%']) !!}
                        </div>
                    </div>

                    @if(\Illuminate\Support\Facades\Schema::hasTable('testimonial_contact'))
                        @php
                            $testimonial = null;
                            if(isset($contact))
                            {
                                if($contact->testimonial()->exists())
                                {
                                    $testimonial = $contact->testimonial()->first()->id;
                                }
                            }
                        @endphp

                        <div class="col-6">
                            <div class="form-group">
                                <label>Referente</label>
                                {!!Form::select('testimonial_id', $testimonials, $testimonial, ['class' => 'custom-select'])!!}
                            </div>
                        </div>
                    @endif

                    @if(\Illuminate\Support\Facades\Schema::hasTable('agent_contact'))
                        @php
                            $agent = null;
                            if(isset($contact))
                            {
                                if($contact->agent()->exists())
                                {
                                    $agent = $contact->agent()->first()->id;
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

    var contact = <?php echo isset($contact) ? json_encode($contact) : "null"; ?>;
    console.log('CONTATTO: ', contact);

    var branches = <?php echo isset($contact) && count($contact->listBranches()) > 0 ? json_encode($contact->listBranches()) : "null"; ?>;

    var p_iva = <?php echo isset($company) && $company->private? $company->private : "null"; ?>;
    var contact_disabled = <?php echo isset($contact) && $contact->isDisabled($contact->id) != null ? json_encode($contact->isDisabled($contact->id)) : "null"; ?>;
    console.log('contact_disabled:', contact_disabled)
    var livello = '<?php echo isset($contact) && $contact->livello? $contact->livello : "null"; ?>';
    var notecontatto = '<?php echo isset($contact) && $contact->note ? str_replace(array("\r", "\n"), "\\n", $contact->note) : "null"; ?>';
    var privacy = '<?php echo isset($contact) && $contact->privacy == 1 ? $contact->privacy : "null"; ?>';
    var sesso = '<?php echo isset($contact) && $contact->sesso != null ? $contact->sesso : "null"; ?>';

    $( document ).ready(function() {
        if(branches != null){
        $('select[name="branch_id[]"]').val(branches);
        $('select[name="branch_id[]"]').trigger('change');
        }

        if(livello != 'null')
            $('#livello').val(livello);

        if(sesso != 'null')
            $('#sesso').val(sesso);

        if(notecontatto != 'null')
            $('textarea[name="note_contact"]').text(notecontatto);
        
        if(privacy != 'null')
            $('#privacy').prop('checked',true)

        console.log(privacy)
        console.log(p_iva)
        if(p_iva == 1 || p_iva == null){
            $("#contact_type_id option[value='4']").remove();
            setValue(false);
        }
        else{
            $("#contact_type_id option[value='1']").remove();
            $("#contact_type_id option[value='2']").remove();
            if(contact == null)
                setValue(true);
        }
        if(contact_disabled != null){
            $('#is_disabile').prop('checked',true)
            $('#div-disabile').show();
            $('#disabile_tipo_id').val(contact_disabled.disabile_tipo_id);
            $('#disabile_attrezzi_id').val(contact_disabled.disabile_attrezzi_id);
            $('input[name="altezza"]').val(contact_disabled.altezza);
            $('input[name="peso"]').val(contact_disabled.peso);
            $('input[name="bacino"]').val(contact_disabled.bacino);
            $('#disabile_sedute_id').val(contact_disabled.disabile_sedute_id);
            $('#sintesi').val(contact_disabled.sintesi);
            $('#catetere').val(contact_disabled.catetere);
            $('textarea[name="note"]').text(contact_disabled.note);
        }

    });



    $('select[name="branch_id[]"]').select2({width: '100%'});
    $('select[name="specializzazioni[]"]').select2({width: '100%'});

    var selectedBranches = <?php echo json_encode($branches_company); ?>;
    console.log('selectedBranches ' + selectedBranches);
    function setValue(isEmpty){
        if(!isEmpty){
            if(contact == null){
                $('input[name="email"]').val(company.email);
                $('input[name="cellulare"]').val(company.mobile);
                $('input[name="indirizzo"]').val(company.address);
                $('input[name="citta"]').val(company.city);
                $('input[name="cap"]').val(company.zip);
                $('select#country').val(company.nation);
                $('select#provincia').val(company.province);
            }
            else{
                $('input[name="email"]').val(contact.email);
                $('input[name="cellulare"]').val(contact.cellulare);
                $('input[name="indirizzo"]').val(contact.indirizzo);
                $('input[name="citta"]').val(contact.citta);
                $('input[name="cap"]').val(contact.cap);
                $('select#country').val(contact.nazione);
                $('select#provincia').val(contact.provincia);
            }
            
            $('select[name="branch_id[]"]').val(selectedBranches).trigger('change');
        }
        else{
            $('input[name="email"]').val('');
            $('input[name="cellulare"]').val('');
            $('input[name="indirizzo"]').val('');
            $('input[name="citta"]').val('');
            $('input[name="cap"]').val('');
            $('select#country').val('');
            $('select#provincia').val('');
            $('select[name="branch_id[]"]').val([]).trigger('change');
        }
        
    }
    var company = <?php echo json_encode($company); ?>;
    console.log('COMPANY:', company);
    //setValue(false);


    $('.add-select').select2({tags: true, placeholder: 'Posizione'});
    $('.add-tag').select2({tags: true, placeholder: 'Origine contatto'});


    $('#div-maestro').hide();
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

    $('select#contact_type_id').on('change', function(){
        $('#div-maestro').hide();
        setValue(true);
        if($(this).val() == 3)
            $('#div-maestro').show();
        if($(this).val() == 1 || $(this).val() == 2)
            setValue(false);

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

        $('form#contactForm').submit();
    })

</script>
@stop
