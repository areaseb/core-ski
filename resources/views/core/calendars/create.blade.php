{!! Form::open(['url' => url('calendars')]) !!}
    <div class="form-group">
        <label for="name" class="col-form-label">Nome</label>
        <input type="text" class="form-control" id="nome" name="nome" required>
    </div>

    <div class="form-group">
        <label for="name" class="col-form-label">Privato</label>
        {!!Form::select('privato', [0=> 'No', 1=>'Sì'], null, ['class' => 'form-control'])!!}
    </div>
   
    <div class="form-group">
        <label for="name" class="col-form-label">Condivisione utenti</label>
        {!! Form::select('user_id[]', $users, null, ['class' => 'select2 ucc','data-placeholder' => "Associa uno o più utenti",'multiple' => 'multiple','style' => 'width:100%']) !!}
    </div>
{!! Form::close() !!}

<script>

$('.select2').select2({width:'100%'});

</script>