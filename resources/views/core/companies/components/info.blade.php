<div class="row">
    @if($company->s1)
        <div class="col col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h5 class="mb-0">Sconto</h5>
                    <p class="mb-0">{{$company->sconto}}% <span style="font-size:80%">({{$company->s1}}% + {{$company->s2}}% + {{$company->s3}}%)</span></p>
                </div>
            </div>
        </div>
    @endif

    @if($company->exemption_id)
        @php
            $ex = Areaseb\Core\Models\Exemption::find($company->exemption_id);
        @endphp
        <div class="col col-md-4">
            <div class="small-box bg-warning" style="min-height:88px;">
                <div class="inner">
                    <h6 class="mb-0">Esenzione {{$ex->perc}}%</h6>
                    <p class="mb-0"></p>
                    <p class="mb-0" style="font-size:80%;">{{$ex->nome}}</p>
                </div>
            </div>
        </div>
    @endif



        <div class="col col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Note</h6>
                </div>
                <div class="card-body">

                    <div class="direct-chat-messages allNotes" style="height:auto">
                        @if(!is_null($company->note))
                            @foreach($company->note as $key => $n)
                                <div class="direct-chat-msg" id="wrapNote-{{$key}}">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="direct-chat-name float-left">{{$n['user']}}</span>
                                        <span class="direct-chat-timestamp float-right">{{$n['data']}} <a href="#" data-key="{{$key}}" class="text-danger deleteNote"><i class="fas fa-trash"></i></a> <a href="#" data-key="{{$key}}" class="text-success editNote"><i class="fas fa-edit"></i></a></span>
                                    </div>
                                    <div class="direct-chat-text" style="margin-left:0;" id="note-{{$key}}">
                                        {{$n['note']}}
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="input-group">
                        <input class="form-control" name="note" type="text" value="" placeholder="Aggiungi nota">
                        <div class="input-group-append">
                            <span class="input-group-text addNote bg-primary"><i class="fas fa-plus"></i></span>
                        </div>
                    </div>

                </div>
            </div>
        </div>



</div>

@push('scripts')
    <script>

        let notes = [];
        $.get("{{route('api.company.notes', $company->id)}}", function(response){
            if(response)
            {
                notes = response;
            }
        });

        $('.addNote').on('click', function(){
            let note = {
                "data": moment().format('DD/M/Y H:mm'),
                "user": "{{$user->contact->fullname}}",
                "note": $(this).parent('div').siblings('input').val()
            };
            notes.push(note);
            addNoteHtml(note);
            $(this).parent('div').siblings('input').val('');

            $.post("{{route('api.company.addNotes', $company->id)}}", {_token: token, obj: notes}).done(function( data ) {
                console.log(data);
            });

        });

        function addNoteHtml(note)
        {
            let html = '<div class="direct-chat-msg"><div class="direct-chat-infos clearfix"><span class="direct-chat-name float-left">'+note.user+'</span><span class="direct-chat-timestamp float-right">'+note.data+'</span></div><div class="direct-chat-text" style="margin-left:0;">'+note.note+'</div></div>';
            $('.allNotes').append(html);
        }

        $('.allNotes').on('click', 'a.deleteNote', function(e){
            e.preventDefault();
            let key = $(this).attr('data-key');
            let newNotes = [];
            for(var k in notes)
            {
                if(k != key)
                {
                    newNotes.push(notes[k]);
                }
            }

            $('#wrapNote-'+key).remove();

            $.post("{{route('api.company.addNotes', $company->id)}}", {_token: token, obj: newNotes}).done(function( data ) {
                console.log(data);
            });
        });

        $('.allNotes').on('click', 'a.editNote', function(e){
            e.preventDefault();
            let key = $(this).attr('data-key');
            let text = $('#note-'+key).text().trim();
            $('input[name="note"]').val(text);
            let newNotes = [];
            for(var k in notes)
            {
                if(k != key)
                {
                    newNotes.push(notes[k]);
                }
            }
            $('#wrapNote-'+key).remove();
            $.post("{{route('api.company.addNotes', $company->id)}}", {_token: token, obj: newNotes}).done(function( data ) {
                console.log(data);
            });
        })




    </script>
@endpush
