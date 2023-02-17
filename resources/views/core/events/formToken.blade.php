{!! Form::hidden('user_id', $utente->id) !!}

<div class="col-md-6 offset-md-3">
		<br>
		<center><img src="{{Areaseb\Core\Models\Setting::DefaultLogo()}}" alt="{{config('app.name')}} Logo" class="brand-image" align="center" width="50%"></center>
		<br>

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title text-center w100">Aggiungi Evento</h3>
        </div>
        <div class="card-body">
            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Calendario</label>
                <div class="col-sm-9">
                    {!! Form::select('calendar_id',$calendars, $calendar, ['class' => 'select2']) !!}
                </div>
            </div>

            <div class="form-group row mt-3">
                <label class="col-sm-3 col-form-label">In Data</label>
                <div class="col-sm-9">
                    <div class="input-group date" id="reservationdate" data-target-input="nearest">
                        <input name="from_date" type="text" class="form-control datetimepicker-input" data-target="#reservationdate" value="{{date('d/m/Y')}}" />
                        <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Dalle*</label>
                <div class="col-sm-9">
                    <select class="custom-select" name="da_ora" onChange="aggiorna_ora()" style="width: 40%; margin-right: 10%;">
                        @foreach (range(6,22) as $value)
                        <option
                        	@if ($value == 10)
                        	selected="selected"
                        	@endif
                        	>{{sprintf('%02d', $value)}}</option>
                        @endforeach
                    </select>
                    <select class="custom-select" name="da_minuto" style="width: 40%;">
                        @foreach (range(0,60,15) as $value)
                        <option>{{sprintf('%02d', $value)}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Alle</label>
                <div class="col-sm-9">
                    <select class="custom-select" name="a_ora" style="width: 40%; margin-right: 10%;">
                        @foreach (range(6,22) as $value)
                        <option
                        	@if ($value == 11)
                        	selected="selected"
                        	@endif
                        	>{{sprintf('%02d', $value)}}</option>
                        @endforeach
                    </select>
                    <select class="custom-select" name="a_minuto" style="width: 40%;">
                        @foreach (range(0,60,15) as $value)
                        <option>{{sprintf('%02d', $value)}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Titolo*</label>
                <div class="col-sm-9">
                    {!! Form::text('title', null, ['class' => 'form-control', 'required']) !!}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Descrizione</label>
                <div class="col-sm-9">
                    {!! Form::textarea('summary', null, ['class' => 'form-control', 'rows' => '1']) !!}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Luogo</label>
                <div class="col-sm-9">
                    {!! Form::text('location', null, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Cliente</label>
                <div class="col-sm-9">
                    {!! Form::select('company_id',$companies, null, ['class' => 'select2', 'data-placeholder' => 'Seleziona un\'azienda', 'data-fouc']) !!}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Utenti</label>
                <div class="col-sm-9">
                    {!! Form::select('users[]',$users, null, ['class' => 'select2', 'multiple','data-placeholder' => 'Condividi con altri utenti', 'data-fouc']) !!}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Utenti</label>
                <div class="col-sm-9">
                    {!! Form::text('emails', null, ['class' => 'form-control ti', 'data-role' => "tagsinput", 'placeholder' => "Associa uno o più indirizzi email" ]) !!}
                </div>
            </div>

        </div>

        <div class="card-footer p-0">
            <button type="submit" class="btn btn-success btn-block"><i class="fa fa-save"></i> Salva</button>
        </div>

    </div>
</div>


@section('scripts')
<script>
    $('select[name="company_id"]').select2({width: '100%'});
    $('select[name="calendar_id"]').select2({width: '100%'});
    $('select[name="users[]"]').select2({width: '100%'});

    $('#reservationdate').datetimepicker({
        format: 'L'
    });

    function pad (str, max) {
		  str = str.toString();
		  return str.length < max ? pad("0" + str, max) : str;
		}

    function aggiorna_ora() {
			var dalle = parseInt($( 'select[name="da_ora"]' ).val());
			var alle = parseInt(dalle + 1);
			alle = String(alle);
			alle = pad(alle, 2);
			$( 'select[name="a_ora"]' ).val(alle).change();
		}

</script>
@stop
