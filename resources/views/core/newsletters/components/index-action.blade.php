<button id="actions" type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" data-display="static">
    Azioni
</button>
<div class="dropdown-menu dropdown-menu-right" >
    @if($newsletter->template)
        <a href="{{$newsletter->template->url}}" target="_blank" class="dropdown-item"><i class="fa fa-eye"></i> Anteprima</a>
        @can('newsletters.write')
            <a href="{{$newsletter->url}}/send-test" class="dropdown-item btn-modal" data-toggle="modal" data-target="#modal" data-save="Invia"><i class="fa fa-eye"></i> Invia test email</a>
        @endcan
    @endif
    @can('newsletters.write')
        <a href="{{$newsletter->url}}/edit" class="dropdown-item"><i class="fa fa-edit"></i> Modifica</a>
        <a href="#" onclick="event.preventDefault;document.getElementById('duplicateNewsletter-{{$newsletter->id}}').submit();" class="dropdown-item"><i class="fa fa-clone"></i> duplica</a>
        {!! Form::open(['url' => route('newsletters.duplicate', $newsletter->id), 'class' => 'd-none', 'id' => 'duplicateNewsletter-'.$newsletter->id]) !!}
            <button class="btn btn-success btn-sm" type="submit"><i class="fa fa-clone"></i></button>
        {!! Form::close() !!}
    @endcan
    @can('newsletters.delete')
        {!! Form::open(['method' => 'delete', 'url' => $newsletter->url, 'id' => "form-".$newsletter->id]) !!}
            <button type="submit" id="{{$newsletter->id}}" class="dropdown-item delete"><i class="fa fa-trash"></i> Elimina</button>
        {!! Form::close() !!}
    @endcan
    @can('newsletters.write')
        <div class="dropdown-divider"></div>
        <a href="{{$newsletter->url}}/send" class="dropdown-item btn-modal" data-toggle="modal" data-target="#modal" data-save="Invia Newsletter"><i class="fas fa-paper-plane"></i> Invia Newsletter</a>
    @endcan
</div>
