<div class="direct-chat-msg" id="wrapNote-{{$note->id}}">
    <div class="direct-chat-infos clearfix">
        <span class="direct-chat-name float-left">{{(is_null($note->user) || is_null($note->user->contact) ) ? $company->rag_soc : $note->user->contact->fullname}}</span>
        <span class="direct-chat-timestamp float-right">
            {!! Form::open(['method' => 'delete', 'url' => route('notes.destroy', $note->id), 'id' => "form-".$note->id]) !!}
                {{$note->created_at->format('d/m/Y H:i:s')}}
                <button type="submit" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                <a href="{{route('notes.edit', $note->id)}}" data-title="Modifica nota" class="btn btn-warning btn-xs btn-modal"><i class="fas fa-edit"></i></a>

                @if(\Illuminate\Support\Facades\Schema::hasTable('killer_quotes'))
                    <a href="{{route('killerquotes.create')}}?note_id={{$note->id}}&company_id={{$note->company_id}}" class="btn btn-info btn-xs"><i class="fas fa-file-invoice-dollar"></i></a>
                @endif

            {!! Form::close() !!}

        </span>
    </div>
    <div class="direct-chat-text" style="margin-left:0;" id="note-{{$note->id}}">
        {!!$note->description!!}
        @if($note->filename)
            <br>
            <a target="_blank" href={{asset('storage/'.$note->filename)}}>{{$note->filename}}</a>
        @endif
    </div>
</div>
