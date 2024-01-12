
<div class="modal" tabindex="-1" role="dialog" id="calendar-modal-addEvent">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">

                    <div class="card col-12" style="box-shadow:none;">
                        <div class="card-header p-2">

                            <ul class="nav nav-pills">
                                <li class="nav-item"><h5 class="modal-title mr-5">Aggiungi Evento</h5></li>
                                <li class="nav-item"><a class="nav-link active" href="#singolo" data-toggle="tab">Evento singolo</a></li>
                                <li class="nav-item"><a class="nav-link" href="#ricursivo" data-toggle="tab">Ricorsivo</a></li>
                                <li class="nav-item"><a class="nav-link" href="#allday" data-toggle="tab">Più giorni</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                    <div class="active tab-pane" id="singolo">

                                        <form class="singolo">
                                            <div class="row">
                                                <div class="col-4 mb-3">
                                                    <label class="mb-0">In data</label>
                                                    <input disabled name="date_singolo" value="" class="form-control">
                                                    <input name="date_singolo" value="" class="d-none">
                                                </div>


                                                <div class="col-8">
                                                    <div class="row">
                                                        <div class="col-3">
                                                            <label class="mb-0">Da ora*</label>
                                                            <select class="custom-select" name="da_ora">
                                                                @foreach (range(6,22) as $value)
                                                                    <option
												                        	@if ($value == 10)
												                        	selected="selected"
												                        	@endif
												                        	>{{sprintf('%02d', $value)}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-3">
                                                            <label class="mb-0"></label>
                                                            <select class="custom-select" name="da_minuto">
                                                                @foreach (range(0,60,15) as $value)
                                                                    <option>{{sprintf('%02d', $value)}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-3">
                                                            <label class="mb-0">A ora</label>
                                                            <select class="custom-select" name="a_ora">
                                                                @foreach (range(6,22) as $value)
                                                                    <option
												                        	@if ($value == 11)
												                        	selected="selected"
												                        	@endif
												                        	>{{sprintf('%02d', $value)}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-3">
                                                            <label class="mb-0"></label>
                                                            <select class="custom-select" name="a_minuto">
                                                                @foreach (range(0,60,15) as $value)
                                                                    <option>{{sprintf('%02d', $value)}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </form>

                                    </div>
                                    <div class="tab-pane" id="ricursivo">
                                        <form class="ricursivo">
                                            <div class="row">

                                                <div class="col-12 mb-3">
                                                    <label class="mb-0">A partire dalla data</label>
                                                    <div class="input-group date" id="reservationdate" data-target-input="nearest">
                                                        <input name="from_date" type="text" class="form-control datetimepicker-input" data-target="#reservationdate"/>
                                                        <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-6">
                                                    <div class="row">
                                                        <div class="col-3">
                                                            <label class="mb-0">Da ora*</label>
                                                            <select class="custom-select current" name="da_ora">
                                                                @foreach (range(6,22) as $value)
                                                                    <option value="{{sprintf('%02d', $value)}}"
																					                        	@if ($value == 10)
																					                        	selected="selected"
																					                        	@endif
																					                        	>{{sprintf('%02d', $value)}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-3">
                                                            <label class="mb-4"></label>
                                                            <select class="custom-select current" name="da_minuto">
                                                                @foreach (range(0,60,15) as $value)
                                                                    <option value="{{sprintf('%02d', $value)}}">{{sprintf('%02d', $value)}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-3">
                                                            <label class="mb-0">A ora</label>
                                                            <select class="custom-select current" name="a_ora">
                                                                @foreach (range(6,22) as $value)
                                                                    <option value="{{sprintf('%02d', $value)}}"
																					                        	@if ($value == 11)
																					                        	selected="selected"
																					                        	@endif
																					                        	>{{sprintf('%02d', $value)}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-3">
                                                            <label class="mb-4"></label>
                                                            <select class="custom-select current" name="a_minuto">
                                                                @foreach (range(0,60,15) as $value)
                                                                    <option value="{{sprintf('%02d', $value)}}">{{sprintf('%02d', $value)}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-3">
                                                    <label class="mb-0">Ogni</label>
                                                    <select class="custom-select current" name="every">
                                                        <option value="7d">Settimana</option>
                                                        <option value="1">Mese</option>
                                                        <option value="2">2 Mesi</option>
                                                        <option value="3">3 Mesi</option>
                                                        <option value="4">4 Mesi</option>
                                                        <option value="6">6 Mesi</option>
                                                        <option value="12">Anno</option>
                                                    </select>
                                                </div>

                                                <div class="col-3">
                                                    <label class="mb-0">Ricorrenze</label>
                                                    <select class="custom-select current" name="x_times">
                                                        @foreach (range(2,30) as $value)
                                                            <option>{{$value}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-12 mt-3">
                                                    <div class="col-12 form-text text-muted" id="currentRic" ></div>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                    <div class="tab-pane" id="allday">
                                        <form class="allday">
                                            <div class="row">
                                                <div class="col-12 mt-3">
                                                    <label class="mb-0">Seleziona i giorni:</label>
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
                                        </form>
                                    </div>

                                <form class="common">
                                    <div class="row">

                                        <div class="col-12 mt-3">
                                            <label class="mb-0">Titolo*</label>
                                            <input name="titolo" class="form-control" type="text">
                                        </div>
                                        <div class="col-12 mt-3">
                                            <label class="mb-0">Impegno</label>
                                            <textarea name="summary" class="form-control"></textarea>
                                        </div>
                                        <div class="col-12 mt-3">
                                            <label class="mb-0">Luogo</label>
                                            <input name="location" class="form-control" id="luogo" type="text">
                                        </div>

                                        @if(request()->has('ids'))
                                            <div class="col-4 mt-3">
                                                <label class="mb-0">Salva su calendario</label>
                                                {!! Form::select('calendar_id',$calendarIdName, null, ['class' => 'select2 ucc', 'data-placeholder' => 'Seleziona calendario', 'data-fouc',]) !!}
                                            </div>
                                            <div class="col-8 mt-3"></div>
                                        @else
                                            <input type="hidden" name="calendar_id" value="{{$calendar->id}}" />
                                        @endif


                                        <div class="col-3 mt-3">
                                            {!! Form::select('user_id[]', $users, null, ['class' => 'select2 ucc','data-placeholder' => "Associa uno o più utenti",'multiple' => 'multiple','style' => 'width:100%']) !!}
                                        </div>

                                        <div class="col-3 mt-3">
                                            {!! Form::select('company_id[]', $companies, null, ['class' => 'select2 ucc','data-placeholder' => "Associa una o più aziende", 'id'=> 'company_id','style' => 'width:100%']) !!}
                                        </div>

                                        <div class="col-3 mt-3">
                                            {!! Form::select('contact_id[]',$contacts, null, ['class' => 'select2 ucc', 'id' => 'contact_id', 'data-placeholder' => 'Associa uno o più contatti', 'data-fouc', 'multiple' => 'multiple','style' => 'width:100%']) !!}
                                        </div>
                                        <div class="col-3 mt-3">
                                        {!! Form::text('emails', null, ['class' => 'form-control ti', 'data-role' => "tagsinput", 'placeholder' => "Invita utenti esterni" ]) !!}

                                        </div>

                                        <div class="col-6 mt-3">
                                            <input type="checkbox" name="invioEmail">
                                            <label >Notifica gli utenti coinvolti via e-mail</label>
                                        </div>

                                    </div>
                                    <input type="hidden" name="type" value="singolo">
                                </form>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                <button type="submit" class="btn btn-primary saveEvent">Salva</button>
            </div>
        </div>
    </div>
</div>



@push('scripts')
<script src="{{asset('plugins/tagsinput/tagsinput.min.js')}}"></script>
<script>
	
    var luoghi_companies = <?php echo json_encode($luoghi_companies); ?>;
    
    function getFormData($form){
        var unindexed_array = $form.serializeArray();
        var indexed_array = {};
        $.map(unindexed_array, function(n, i){
            indexed_array[n['name']] = n['value'];
        });

        let contact_id = [];
        $.each($('select[name="contact_id[]"]').select2('data'), function(){
            contact_id.push($(this)[0].id)
        });
        indexed_array['contact_id[]'] = contact_id;

        let user_id = [];
        $.each($('select[name="user_id[]"]').select2('data'), function(){
            user_id.push($(this)[0].id)
        });
        indexed_array['user_id[]'] = user_id;

        let company_id = [];
        $.each($('select[name="company_id[]"]').select2('data'), function(){
            company_id.push($(this)[0].id)
        });
        indexed_array['company_id[]'] = company_id;


        return indexed_array;
    }

    Number.prototype.pad = function(size) {
        var s = String(this);
        while (s.length < (size || 2)) {s = "0" + s;}
        return s;
    }

    function validate()
    {
        if($('input[name="titolo"]').val() == '')
        {
            notify("Il titolo è obbligatorio");
            return false;
        }

        let tipo = $('input[name="type"]').val();

        if(tipo == 'ricursivo')
        {
            let data_ric = $('input[name="from_date"]').val();
            if(data_ric == '')
            {
                notify("A partire dalla data è obbligatoria");
                return false;
            }
            else if(!moment(data_ric, 'DD/MM/YYYY').isValid())
            {
                notify("A partire dalla data non ha un formato valido");
                return false;
            }
        }
        return true
    }

    function notify(str)
    {
        new Noty({
            text: str,
            type: 'error',
            layout: 'center',
            modal: true,
            theme: 'bootstrap-v4',
            timeout: 750,
        }).show();
    }

    function createCurrentSelection()
    {
        let giorno = moment($('input[name="from_date"]').val(), 'DD/MM/YYYY');
        let daOra = $('select[name="da_ora"].current :selected').val();
        let daMinuto = $('select[name="da_minuto"].current :selected').val();
        let aOra = $('select[name="a_ora"].current :selected').val();
        let aMinuto = $('select[name="a_minuto"].current :selected').val();
        let ogni = $('select[name="every"].current :selected').val();
        let volte = $('select[name="x_times"].current :selected').val();
        let currentRic = '';
        if(ogni.includes('d'))
        {
            currentRic = 'Da '+ giorno.format('dddd') + ' ' + giorno.format('D/MM/YYYY') + ', ogni '+giorno.format('dddd') + ' della settimana dalle ' + daOra + ':' + daMinuto + ' alle ' + aOra + ':' + aMinuto + ' per '+ volte +' settimane.';
        }
        else
        {
            if(parseInt(ogni) === 1)
            {
                currentRic = 'Da '+ giorno.format('dddd') + ' ' + giorno.format('D/MM/YYYY') + ', ogni '+giorno.format('Do') + ' del mese dalle ' + daOra + ':' + daMinuto + ' alle ' + aOra + ':' + aMinuto + ' per '+ volte +' mesi.';
            }
            else
            {
                currentRic = 'Da '+ giorno.format('dddd') + ' ' + giorno.format('D/MM/YYYY') + ', il '+giorno.format('Do') + ' di ogni '+ ogni + ' mesi, dalle '+ daOra + ':' + daMinuto + ' alle ' + aOra + ':' + aMinuto + ' per '+ volte +' ricorrenze.';
            }
        }
        $('#currentRic').text(currentRic);
    }

    $('select.current').on('change', function(){

        if( $(this).attr('name') == 'da_ora' && $(this).hasClass('current') )
        {
            let oneMore = parseInt($('select[name="da_ora"].current').val())+1
            $('select[name="a_ora"].current').val(oneMore.pad());

            $('select[name="da_ora"].current').on('change', function(){
                let value = parseInt($(this).find(':selected').val())+1;
                $('select[name="a_ora"].current').val(value.pad());
            });
        }

        createCurrentSelection();
    });



    $('select#company_id').on('change', function(){
        console.log($(this).val());
        var company_id = $(this).val()
      
         $.ajax({
			method: "GET",
			url: "/get-contacts/" + company_id + '/0',
			success: function(risposta) {
				
				$('#contact_id').empty();
				
				var data = [];
				
				Object.entries(risposta).forEach(entry => {
				    const [key, value] = entry;
				    
					data.push({
						id: key,
						text: value
					});
					
				});
				
				console.log(data);
				$("#contact_id").select2({data: data});
			},
			error: function() {

			}
	    });
	    
	    
	    
        for (var key in luoghi_companies) {
            var value = luoghi_companies[key];
         
            if(value.key == company_id)
                {
                    $('#luogo').val(value.value);
                    return;
                }

        }
        
    });

    $('.fc-timeGridWeek-button.fc-timeGridWeek-button').on('click', function(){
        alert();
    });


    function eventModal(info, calendar)
    {
        console.log('modal opened');

        $('#calendar-modal-addEvent').modal('show');
        $('.select2.ucc').select2({width:'100%'});

        $('.nav-pills .nav-link').on('click', function(){
            let tipo = $(this).attr('href').replace("#", '');
            $('input[name="type"]').val(tipo);
        });

        let oneMore = parseInt($('select[name="da_ora"]').val())+1
        $('select[name="a_ora"]').val(oneMore.pad());

        $('select[name="da_ora"]').on('change', function(){
            let value = parseInt($(this).find(':selected').val())+1;
            $('select[name="a_ora"]').val(value.pad());
        });

        let clickedDate = moment(info.dateStr);

        $('#range').daterangepicker({
            startDate: clickedDate.format('DD/MM/YYYY'),
            endDate: clickedDate.add(7,'days').format('DD/MM/YYYY'),
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Applica',
                cancelLabel: 'Annulla',
                fromLabel: 'Da',
                toLabel: 'A'
            }
        });

        $('#reservationdate').datetimepicker({
            format: 'L',
            date: moment(info.dateStr)
        });

        $('input[name="date_singolo"]').val(moment(info.dateStr).format('DD/MM/YYYY'));

        createCurrentSelection();
    }


    $('button.saveEvent').on('click', function(){
        if(!validate())
        {
            console.log('validation did not passed');
            return false;
        }

        let tipo = $('input[name="type"]').val();
        let tipoFormValues = getFormData($('form.'+tipo));
        let commonFormValue = getFormData($('form.common'));
        let token = {_token: "{{csrf_token()}}"};
        let eventData = {
                ...tipoFormValues,
                ...commonFormValue,
                ...token
            };

        console.log('eventData:', eventData);

        $.post("{{url('events')}}", eventData).done(function( response ) {

/*            console.log(response);
            return false;*/
            $('#calendar-modal-addEvent').modal('hide');
            window.location.href = "{{request()->url()}}";
/*
            if(tipo == 'ricursivo')
            {
                $.each(response, function(){
                    let e = $(this)[0];
                    var r_starts_at_str = moment(e.starts_at, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DDTHH:mm:ss');
                    var r_ends_at_str = moment(e.ends_at, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DDTHH:mm:ss');
                    var r_starts_at = new Date(r_starts_at_str);
                    var r_ends_at = new Date(r_ends_at_str);

                    calendar.addEvent({
                        id: e.id,
                        title: e.title,
                        start:r_starts_at,
                        end: r_ends_at,
                        startStr: r_starts_at_str,
                        endStr: r_ends_at_str,
                        timeFormat: 'H(:mm)',
                        backgroundColor: '#ffc107',
                        borderColor: '#ffc107'
                    });
                });
                console.log('ricursivo');
            }
            else
            {
                var starts_at_str = moment(response.starts_at, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DDTHH:mm:ss');
                var ends_at_str = moment(response.ends_at, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DDTHH:mm:ss');
                var starts_at = new Date(starts_at_str);
                var ends_at = new Date(ends_at_str);

                if(tipo == 'singolo')
                {
                    if (!isNaN(starts_at.valueOf()))
                    {
                        calendar.addEvent({
                            id: response.id,
                            title: response.title,
                            start:starts_at,
                            end: ends_at
                        });
                    }
                    console.log('singolo');
                }
                else if(tipo == 'allday')
                {
                    ends_at = new Date(moment(response.ends_at, 'YYYY-MM-DD HH:mm:ss').add(1,'days').format('YYYY-MM-DDTHH:mm:ss'));

                    if (!isNaN(starts_at.valueOf()))
                    {
                      calendar.addEvent({
                            id: response.id,
                            title: response.title,
                            start: starts_at,
                            end: ends_at,
                            allDay: true,
                            backgroundColor: '#28a745',
                            borderColor: '#28a745'
                      });
                    }
                    console.log('allday');
                }
            }//end if
*/
        });
    });


</script>
@endpush
