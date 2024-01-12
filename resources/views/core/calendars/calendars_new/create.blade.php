{!! Form::open(['url' => url('calendars')]) !!}
    <div class="form-group">
        <label for="name" class="col-form-label">Nome:</label>
        <input type="text" class="form-control" id="nome" name="nome" required>
    </div>

    <div class="form-group">
        <label for="name" class="col-form-label">Privato:</label>
        {!!Form::select('privato', [0=> 'No', 1=>'Sì'], null, ['class' => 'form-control'])!!}
    </div>
{!! Form::close() !!}
