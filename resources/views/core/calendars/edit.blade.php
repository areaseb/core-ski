{!! Form::model($calendar, ['url' => $calendar->url, 'method' => 'PATCH', 'id' => 'calendarForm']) !!}
    <div class="form-group">
        <label for="name" class="col-form-label">Nome:</label>
        {!!Form::text('nome', null, ['class' => 'form-control', 'required' => 'required'])!!}
    </div>

    <div class="form-group">
        <label for="name" class="col-form-label">Privato:</label>
        {!!Form::select('privato', [0=> 'No', 1=>'Sì'], null, ['class' => 'form-control'])!!}
    </div>
   
    <div class="form-group">
        <label for="name" class="col-form-label">Condivisione utenti</label>
        {!! Form::select('user_id[]', $users, $selected, ['class' => 'select2 ucc','data-placeholder' => "Associa uno o più utenti",'multiple' => 'multiple','style' => 'width:100%']) !!}
    </div>
{!! Form::close() !!}

<script>

$('.select2').select2({width:'100%'});

</script>