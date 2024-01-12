<div class="col-md-6">
    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">Credenziali</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Email</label>
                {!!Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Indirizzo Email', 'required', 'autocomplete' => 'off'])!!}
                @include('areaseb::components.add-invalid', ['element' => 'email'])
            </div>

            @if(isset($element))

                @if($element->id === $user->id)
                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <input name="password" type="password" class="form-control" autocomplete="off">
                            <div class="input-group-append">
                                <span class="input-group-text random"><i class="fa fa-random"></i></span>
                                <span class="input-group-text pw"><i class="fa fa-eye"></i></span>
                            </div>
                        </div>

                        @include('areaseb::components.add-invalid', ['element' => 'password'])
                    </div>
                @endif

            @else

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-group">
                        <input name="password" type="password" class="form-control" autocomplete="off">
                        <div class="input-group-append">
                            <span class="input-group-text random"><i class="fa fa-random"></i></span>
                            <span class="input-group-text pw"><i class="fa fa-eye"></i></span>
                        </div>
                    </div>

                    @include('areaseb::components.add-invalid', ['element' => 'password'])
                </div>

            @endif

            <div class="form-group">
                <label>Ruolo</label>
                @isset($element)
                    {!! Form::select('role_id[]', $roles, $element->roles, [
                        'class' => 'form-control select2bs4',
                        'multiple' => 'multiple',
                        'data-placeholder' => 'Seleziona almeno un ruolo',
                        'style' => 'width:100%',
                        'required']) !!}
                @else

                    @php
                        $role = old('role_id') ?? null;
                        if(request('role_id'))
                        {
                            $roleModel = \Spatie\Permission\Models\Role::where('id', request('role_id'))->first();
                            if($roleModel)
                            {
                                $role = $roleModel->id;
                                $roles = [$roleModel->id => $roleModel->name];
                            }
                        }
                    @endphp

                    {!! Form::select('role_id[]', $roles, $role, [
                        'class' => 'form-control select2bs4',
                        'multiple' => 'multiple',
                        'data-placeholder' => 'Seleziona almeno un ruolo',
                        'style' => 'width:100%',
                        'required']) !!}

                @endisset
            </div>
        </div>
    </div>

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Contatto</h3>
        </div>
        <div class="card-body">

            <div class="form-group">
                <label>Associa ad un Contatto</label>
                {!! Form::select('company_id', $contacts, $user_contact->id ?? null, [
                    'class' => 'form-control select2bs4',
                    'data-placeholder' => "Seleziona un Contatto",
                    'style' => 'width:100%']) !!}
                    <small><a href="{{url('contacts/create')}}" target="_BLANK"><i class="fa fa-plus"></i> Crea un nuovo Contatto</a></small>
            </div>

        </div>
    </div>


    <div class="card d-none d-md-flex">
        @if(empty($element))
            <div class="card-body">
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input name="sendEmail" value="1" class="custom-control-input" type="checkbox" id="sendEmail">
                        <label for="sendEmail" class="custom-control-label">Invia un'email con le credenziali all'utente</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm" style="border-top-left-radius:0;border-top-right-radius:0;"><i class="fa fa-save"></i> Salva</button>
        @else
            <button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm"><i class="fa fa-save"></i> Salva</button>
        @endif
    </div>



</div>

<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Contatti</h3>
        </div>
        <div class="card-body">
            <input type="hidden" name="origin" value="Manuale">
            <div class="form-group">
                <label>Nome</label>
                {!! Form::text('nome', $element->contact->nome ?? null, ['class' => 'form-control', 'required']) !!}
                @include('areaseb::components.add-invalid', ['element' => 'nome'])
            </div>
            <div class="form-group">
                <label>Cognome</label>
                {!! Form::text('cognome', $element->contact->cognome ?? null, ['class' => 'form-control', 'required']) !!}
                @include('areaseb::components.add-invalid', ['element' => 'cognome'])
            </div>

            <div class="form-group">
                <label>Nazione</label>
                {!! Form::select('nazione', $countries, null, ['class' => 'custom-select', 'id' => 'country']) !!}
            </div>
            <div class="form-group">
                <label>Mobile</label>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="changePrefix"></span>
                    </div>
                    {!!Form::text('cellulare', $element->contact->cellulare ?? null, ['class' => 'form-control', 'placeholder' => 'Cellulare'])!!}
                </div>
            </div>
            <div class="form-group">
                <label>indirizzo</label>
                {!!Form::text('indirizzo', $element->contact->indirizzo ?? null, ['class' => 'form-control', 'placeholder' => 'Indirizzo'])!!}
            </div>
            <div class="form-group">
                <label>Città</label>
                {!!Form::text('citta', $element->contact->citta ?? null, ['class' => 'form-control', 'placeholder' => 'Città'])!!}
            </div>
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label>CAP</label>
                        {!!Form::text('cap', $element->contact->cap ?? null, ['class' => 'form-control', 'placeholder' => 'CAP'])!!}
                    </div>
                </div>
                <div class="col-8">
                    <div class="form-group">
                        <label>Provincia</label>

                        {!!Form::text('provincia', $element->contact->provincia ?? null, [
                            'class' => 'form-control',
                            'placeholder' =>'Regione Estera',
                            'id' => 'region'])!!}

                        {!! Form::select('provincia', $provinces, $element->contact->provincia ?? null, [
                            'class' => 'form-control select2bs4',
                            'data-placeholder' => 'Seleziona una provincia',
                            'style' => 'width:100%',
                            'id' => 'provincia']) !!}
                    </div>

                </div>
                </div>

            

        </div>
    </div>


    <div class="card d-md-none">
        @if(empty($element))
            <div class="card-body">
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input name="sendEmail" value="1" class="custom-control-input" type="checkbox" id="sendEmail">
                        <label for="sendEmail" class="custom-control-label">Invia un'email con le credenziali all'utente</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm" style="border-top-left-radius:0;border-top-right-radius:0;"><i class="fa fa-save"></i> Salva</button>
        @else
            <button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm"><i class="fa fa-save"></i> Salva</button>
        @endif
    </div>

</div>


@section('scripts')
<script>

$('#div-piva').hide();
$('#div-parent').hide();

$('select[name="type_id"]').on('change', function(){
    let type = $(this).val();
    $('#div-piva').hide();
    $('#div-parent').hide();
    //sci club
    if(type == 4){
        $('#div-piva').show();
    }
    //figlio
    if(type == 2){
        $('#div-parent').show();
    }
});


$('select[name="company_id"]').on('change', function(){
    let url = baseURL+'api/companies/'+$(this).val()+'/contact';
    axios.get(url).then(response =>{
        console.log(response.data);
        if(response.data)
        {
            let contact = response.data;
            $('input[name="nome"]').val(contact.nome);
            $('input[name="cognome"]').val(contact.cognome);
            $('input[name="cellulare"]').val(contact.cellulare);
            $('input[name="indirizzo"]').val(contact.indirizzo);
            $('input[name="citta"]').val(contact.citta);
            $('input[name="cap"]').val(contact.cap);
            $('select[name="provincia"]').val(contact.provincia);
            $('select[name="provincia"]').trigger('change');
        }
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

        $('form#userForm').submit();
    });

    $('span.pw').on('click', function(){
        let btn = $(this).find('i');
        let input = $(this).parents('div').siblings('input[name="password"]');
        if($(input).attr('type') == 'password')
        {
            $(input).attr('type','text');
            $(btn).addClass('fa-eye-slash');
            $(btn).removeClass('fa-eye');
        }
        else
        {
            $(input).attr('type','password');
            $(btn).removeClass('fa-eye-slash');
            $(btn).addClass('fa-eye');
        }
    });
    $('span.random').on('click', function(){
        let input = $(this).parents('div').siblings('input[name="password"]');
        $(input).attr('type','text');
        $(input).val(randomStr(16));
    });

    function randomStr(length) {
       var result           = '';
       var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
       var charactersLength = characters.length;
       for ( var i = 0; i < length; i++ ) {
          result += characters.charAt(Math.floor(Math.random() * charactersLength));
       }
       return result;
    }


</script>
@stop
