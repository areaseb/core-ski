{!! Form::model($calendar, ['url' => $calendar->url, 'method' => 'PATCH', 'id' => 'calendarForm']) !!}
    <div class="form-group">
        <label for="name" class="col-form-label">Nome:</label>
        {!!Form::text('nome', null, ['class' => 'form-control', 'required' => 'required'])!!}
    </div>

    <div class="form-group">
        <label for="name" class="col-form-label">Privato:</label>
        {!!Form::select('privato', [0=> 'No', 1=>'Sì'], null, ['class' => 'form-control'])!!}
    </div>
{!! Form::close() !!}
