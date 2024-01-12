{!! Form::open(['method' => 'delete', 'url' => route('contacts.destroy', $contact->id), 'id' => "form-".$contact->id]) !!}

    <a href="{{$contact->url}}/edit" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>

    @can('contacts.delete')
        <button type="submit" id="{{$contact->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
    @endcan

    @can('companies.write')
        @if(is_null($contact->company))
            <a href="#" class="btn btn-info btn-icon btn-sm makeCompany" title="crea azienda" data-id="{{$contact->id}}"><i class="fa fa-user-tie"></i></a>
        @endif
    @endcan

    @can('users.write')
        @if(is_null($contact->user_id))
            <a href="#" class="btn btn-secondary btn-icon btn-sm makeUser" title="crea utente" data-id="{{$contact->id}}"><i class="fa fa-user"></i></a>
        @endif
    @endcan

    @includeIf('deals::quick-btn.link')
    @includeIf('killerquote::quick-btn.link')

    <a href="mailto:{{$contact->email}}" target="_BLANK" class="btn btn-orange btn-icon btn-sm"><i class="far fa-paper-plane"></i></a>

{!! Form::close() !!}


@includeIf('killerquote::quick-btn.form')
@includeIf('deals::quick-btn.form')

@can('companies.write')
    {!! Form::open(['url' => url('contacts/make-company'), 'id' => "makeCompany-".$contact->id, 'class' => 'd-none']) !!}
        <input type="hidden" name="id" value="{{$contact->id}}" />
        <button type="submit"></button>
    {!! Form::close() !!}
@endcan

@can('users.write')
    {!! Form::open(['url' => url('contacts/make-user'), 'id' => "makeUser-".$contact->id, 'class' => 'd-none']) !!}
        <input type="hidden" name="id" value="{{$contact->id}}" />
        <button type="submit"></button>
    {!! Form::close() !!}
@endcan

@if($contact->cellulare)
    @if($contact->int_number)
        @if($t != '')
            @if($t == 'Lead')
                <a data-model="Contact" data-id="{{$contact->id}}" style="position:absolute; right:3px; top:5px;" target="_BLANK" href="https://web.whatsapp.com/send?phone={{$contact->int_number}}&text=Buongiorno {{$contact->fullname}}{{$wa}}" class="btn btn-sm btn-success waClicked"><i class="fab fa-whatsapp"></i></a>
            @else
                <a style="position:absolute; right:3px; top:5px;" target="_BLANK" href="https://web.whatsapp.com/send?phone={{$contact->int_number}}&text=Buongiorno {{$contact->fullname}}{{$wa}}" class="btn btn-sm btn-success"><i class="fab fa-whatsapp"></i></a>
            @endif
        @else
            <a style="position:absolute; right:3px; top:5px;" target="_BLANK" href="https://web.whatsapp.com/send?phone={{$contact->int_number}}&text=Buongiorno {{$contact->fullname}}{{$wa}}" class="btn btn-sm btn-success"><i class="fab fa-whatsapp"></i></a>
        @endif
    @endif
@endif
