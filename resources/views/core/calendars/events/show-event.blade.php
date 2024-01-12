<div class="modal" tabindex="-1" role="dialog" id="calendar-modal-showEvent">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header" style="display:block;position:relative;">
                <h4 id="e-title" class="mb-0"></h4>
                <h5 class="mb-0"><b><span id="e-data"></span></b></h5>
                <button style="position:absolute; right: 3%;top: 20%;" type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-12" id="e-summary"></div>
                </div>
                <div class="row">
                    <div class="col-12" id="e-location"></div>
                </div>
                <div class="row" id="e-dynamic">

                </div>
            </div>

            <div class="modal-footer" style="display:block;">
                <div class="row">
                    <div class="text-left col">
                        <a href="#" class="btn btn-danger e-delete">Elimina per tutti</a>
                        <a href="#" class="btn btn-danger e-delete-single">Elimina solo per me </a>
                        <a href="#" class="btn btn-success e-done"><i class="fa fa-check"></i> Fatto</a>
                        <form action="" method="POST" class="d-none e-delete-form">
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit">ELIMINA</button>
                        </form>

                        <form action="" method="POST" class="d-none e-delete-form" id="form-delete-single">
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit">ELIMINA</button>
                        </form>
                    </div>
                    <div class="text-right col">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                        <a href="#" class="btn btn-warning" id="e-edit">Modifica</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
    <script>

    $('a.showEvent').on('click', function(e){
        e.preventDefault();
        showModal($(this).attr('data-eventId'));
    });

    function showModal(infoEventId)
    {
        let url = "{{url('events')}}/"+infoEventId;
        $.get(url, function(data){
            console.log(data);
            let notes = null;
            if('notes' in data){
                notes = data.notes;
            }

            let deal = null;
            if('deal_id' in data){
                deal = data.deal_id;
            }

            const event = data.event[0];
            let companies = '';
            let users = '';
            let contacts = '';
            if(event.companies.length)
            {
                console.log(event.companies);
                const company = event.companies[0];
                companies = '<div class="col-4">';
                companies += '<h6 class="mb-0 text-underline text-primary">Azienda</h6>';
                companies += '<h6>'+company.rag_soc+'</h6>';
                companies += '</div>';
            }

            if(event.users.length)
            {
                console.log('ho users');
                users += '<div class="col-4">';
                users += '<h6 class="mb-0 text-underline text-primary">Utenti</h6>';
                event.contacts.forEach( el => {
                        users += '<h6 class="mb-0">'+el.nome+' '+el.cognome+'</h6>';
                    }
                );
                users += '</div>';
            }

            if(event.contacts.length)
            {
                console.log('ho contacts');
                contacts += '<div class="col-4">';
                contacts += '<h6 class="mb-0 text-underline text-primary">Contatti</h6>';
                event.contacts.forEach( el => {
                        contacts += '<h6 class="mb-0">'+el.nome+' '+el.cognome+'</h6>';
                    }
                );
                contacts += '</div>';
            }

            if(event.location)
            {
                let linkLocation =  '<div class="mb-2"><b>Luogo</b>: <a href="https://maps.google.com/?q='+event.location+'" target="_BLANK">'+event.location+'</a></div>';
                $('#e-location').html(linkLocation);
            }
            else
            {
                $('#e-location').html('');
            }

            $text_title = event.title;
            $('#e-title').html($text_title);
            let da_a = moment(event.starts_at).format('dddd') + " " + moment(event.starts_at).format('LL') + " dalle " + moment(event.starts_at).format('HH:mm') + " alle " + moment(event.ends_at).format('HH:mm')+ '<br> <p style="font-size: 17px;">creato da <b style="color:#0056b3">'+ event.autore +'</b></p>';

            if(parseInt(event.allday) === 1)
            {
                 da_a = "da "+ moment(event.starts_at).format('dddd D MMMM')+" a " + moment(event.ends_at).format('dddd D MMMM YYYY');
            }


            $('#e-data').html(da_a);
            let summary = '<p class="mb-3">'+event.summary+'</p>';
            if(deal)
            {
                summary += '<a href="'+baseURL+'deals/'+deal+'/edit" class="mb-3 btn btn-sm btn-primary"><i class="fa fa-eye"></i> Vedi trattativa</a>';
            }
            if(event.summary)
            {
                $('#e-summary').html(summary);
            }
            else
            {
                $('#e-summary').html('');
            }





            let addNotes = '';
            if(notes)
            {
                addNotes = '<div class="text-right"><a href="'+baseURL+'killerquotes/'+data.killer_id+'/notes/create" class="btn btn-sm btn-primary btn-modal"><i class="fa fa-plus"></i> Nota</a></div>';
            }

            if(notes == 'empty')
            {
                let noteText = addNotes;
                $('#e-summary').append(noteText);

                $('a.btn-modal').on('click', function(e){
                    e.preventDefault();
                    $('#global-modal').modal('show').find('.modal-body').load($(this).attr('href'));
                    $('button.btn-save-modal').on('click', function(){
                        $('#global-modal .modal-content form').submit();
                    });
                });

            }
            else if(notes)
            {
                let noteText = addNotes+'<b>Note Preventivo</b><br><div style="height:auto;" class="pt-0 direct-chat-messages mb-3">';
                for(note in notes)
                {
                    let nota = notes[note];
                    noteText += '<div class="direct-chat-msg" id="line-note-'+nota.id+'"><div class="direct-chat-infos clearfix"><span class="direct-chat-name float-left"></span><span class="direct-chat-timestamp float-right">'+moment(nota.created_at).format('DD/MM/YYYY')+' <a data-note_id="'+nota.id+'" href="'+baseURL+'killerquotes/'+data.killer_id+'/notes/'+nota.id+'" class="deleteNote text-danger"> <i class="fa fa-trash"></i></a></span> </div><div class="direct-chat-text m-0">'+nota.note+'</div></div>';
                }
                noteText += '</div>';
                $('#e-summary').append(noteText);

                $('a.btn-modal').on('click', function(e){
                    e.preventDefault();
                    $('#global-modal').modal('show').find('.modal-body').load($(this).attr('href'));
                    $('button.btn-save-modal').on('click', function(){
                        $('#global-modal .modal-content form').submit();
                    });
                });

                $('a.deleteNote').on('click', function(e){
                    e.preventDefault();
                    let note_id = $(this).attr('data-note_id');
                    let params = {};
                    params._token = token;
                    axios.delete($(this).attr('href'), params).then(response => {
                        if(response.data == 'done')
                        {
                            $('div#line-note-'+note_id).remove();
                        }
                    });
                });

            }


            if(parseInt(event.done) === 1)
            {
                $('a.e-done').html('<i class="fa fa-times"></i> Non Fatto').removeClass('btn-success').addClass('btn-danger');
            }

            $('#e-dynamic').html(companies+contacts+users);
            let url = "{{url('events')}}/"+infoEventId;
            $('#e-edit').attr('href', url+'/edit');
            $('#calendar-modal-showEvent').modal('show');
            console.log('show event');

            $('a.e-delete').on('click', function(e){
                e.preventDefault();
                let form = $(this).siblings('form');
                form.attr('action', url);


                var notyConfirm = new Noty({
                    text: '<h6 class="mb-0">Siete sicuri?</h6><hr class="mt-0 mb-1">',
                    timeout: false,
                    modal: true,
                    layout: 'center',
                    closeWith: 'button',
                    theme: 'bootstrap-v4',
                    buttons: [
                        Noty.button('Annulla', 'btn btn-light ml-5', function () {
                            notyConfirm.close();
                        }),
                        Noty.button('Sì, elimina <i class="fa fa-trash"></i>', 'btn btn-danger ml-1', function () {
                                form.submit();
                            },
                            {id: 'button1', 'data-status': 'ok'}
                        )
                    ]
                }).show();


            });


            $('a.e-delete-single').on('click', function(e){
                e.preventDefault();
                let form = $('#form-delete-single').siblings('form');
                let url = '/event/delete-for-me/'+infoEventId;
                form.attr('action', url);


                var notyConfirm = new Noty({
                    text: '<h6 class="mb-0">Siete sicuri?</h6><hr class="mt-0 mb-1">',
                    timeout: false,
                    modal: true,
                    layout: 'center',
                    closeWith: 'button',
                    theme: 'bootstrap-v4',
                    buttons: [
                        Noty.button('Annulla', 'btn btn-light ml-5', function () {
                            notyConfirm.close();
                        }),
                        Noty.button('Sì, elimina<i class="fa fa-trash"></i>', 'btn btn-danger ml-1', function () {
                                form.submit();
                            },
                            {id: 'button1', 'data-status': 'ok'}
                        )
                    ]
                }).show();


            });

            $('a.e-done').on('click', function(e){

                $.post( "{{url('api/events')}}/"+infoEventId+"/done", { _token: "{{csrf_token()}}"}).done(function( data ) {
                    console.log( "Data Loaded: " + data );
                    $('#calendar-modal-showEvent').modal('hide');
                    location.reload();
                });

            });
        });
    }

    </script>
@endpush
