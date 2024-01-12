<div class="col-md-6">
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Dati Generali</h3>
        </div>
        <div class="card-body pt-1 pb-1">
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
                <label class="col-sm-3 col-form-label">Azienda</label>
                <div class="col-sm-9">
                    {!! Form::select('company_id[]',$companies,
                        $selectedCompany, ['class' => 'select2', 'data-placeholder' => 'Seleziona una o più aziende', 'data-fouc', 'multiple' => 'multiple']) !!}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Contatti</label>
                <div class="col-sm-9">
                    {!! Form::select('contact_id[]',$contacts,
                        $selectedContacts, ['class' => 'select2', 'data-placeholder' => 'Seleziona uno o più contatti', 'data-fouc', 'multiple' => 'multiple']) !!}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Utenti</label>
                <div class="col-sm-9">
                    {!! Form::select('user_id[]',$users,
                        $selectedUsers, ['class' => 'select2', 'data-placeholder' => 'Seleziona uno o più utenti', 'data-fouc', 'multiple' => 'multiple']) !!}
                </div>
            </div>

        </div>

    </div>
</div>



<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Singolo</h3>
        </div>
        <div class="card-body">

            <div class="form-group row mt-3">
                <label class="col-sm-3 col-form-label">In Data</label>
                <div class="col-sm-9">
                    <div class="input-group date" id="reservationdate" data-target-input="nearest">
                        <input name="from_date" type="text" class="form-control datetimepicker-input" data-target="#reservationdate"/>
                        <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Da ora*</label>
                <div class="col-sm-9">
                    <select class="custom-select" name="da_ora">
                        @foreach (range(6,22) as $value)
                            @if($event->starts_at->format('G') == $value)
                                <option selected="selected">{{sprintf('%02d', $value)}}</option>
                            @else
                                <option>{{sprintf('%02d', $value)}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Da minuto</label>
                <div class="col-sm-9">
                    <select class="custom-select" name="da_minuto">
                        @foreach (range(0,60,15) as $value)
                            @if($event->starts_at->format('i') == sprintf('%02d', $value))
                                <option selected="selected">{{sprintf('%02d', $value)}}</option>
                            @else
                                <option>{{sprintf('%02d', $value)}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">A ora</label>
                <div class="col-sm-9">
                    <select class="custom-select" name="a_ora">
                        @foreach (range(6,22) as $value)
                            @if($event->ends_at->format('G') == $value)
                                <option selected="selected">{{sprintf('%02d', $value)}}</option>
                            @else
                                <option>{{sprintf('%02d', $value)}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row mb-4">
                <label class="col-sm-3 col-form-label">A minuto</label>
                <div class="col-sm-9">
                    <select class="custom-select" name="a_minuto">
                        @foreach (range(0,60,15) as $value)
                            @if($event->ends_at->format('i') == sprintf('%02d', $value))
                                <option selected="selected">{{sprintf('%02d', $value)}}</option>
                            @else
                                <option>{{sprintf('%02d', $value)}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

        </div>
    </div>



</div>

<div class="col-md-6">


    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Calendario</h3>
        </div>
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Calendario</label>
                <div class="col-sm-9">
                    {!!Form::select('calendar_id', $calendars, null, ['class' => 'custom-select'])!!}
                </div>
            </div>

        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Più giorni</h3>
        </div>
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Da/a</label>
                <div class="col-sm-9">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="far fa-calendar-alt"></i>
                            </span>
                        </div>
                        <input type="text" name="range" class="form-control float-right" id="range">
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-footer p-0">
                    <button type="submit" class="btn btn-success btn-block"><i class="fa fa-save"></i> Salva</button>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-footer p-0">
                    <button class="btn btn-danger btn-block delete-Event" data-id="{{$event->id}}"><i class="fas fa-trash"></i> Elimina</button>
                </div>
            </div>
        </div>
    </div>
</div>



@section('scripts')
<script>
    $('select[name="company_id[]"]').select2({width: '100%'});
    $('select[name="user_id[]"]').select2({width: '100%'});
    $('select[name="contact_id[]"]').select2({width: '100%'});

    $('#reservationdate').datetimepicker({
        format: 'L',
        date: moment("{{$event->starts_at->format('Y-m-d')}}")
    });

    $('#range').daterangepicker({
        // startDate: clickedDate.format('DD/MM/YYYY'),
        // endDate: clickedDate.add(7,'days').format('DD/MM/YYYY'),
        autoUpdateInput: false,
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Applica',
            cancelLabel: 'Annulla',
            fromLabel: 'Da',
            toLabel: 'A'
        }
    });

    $('button.delete-Event').on('click', function(e){
        e.preventDefault();
        let eventId = $(this).attr('data-id');
        let form = $('form#form-delete-event-'+eventId).submit();
        console.log(form, eventId);
    });


</script>
@stop
