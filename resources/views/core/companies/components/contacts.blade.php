<div class="row">
    @if($company->contacts()->exists())
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Cellulare</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($company->contacts as $contact)
                        <tr>
                            <td><i>{{$contact->pos}}</i> <b>{{$contact->fullname}}</b></td>
                            <td>{{$contact->email}}</td>
                            <td>{{$contact->cellulare}}</td>
                            <td>
                                {!! Form::open(['method' => 'delete', 'url' => $contact->url, 'id' => "form-".$contact->id]) !!}
                                    <button type="submit" id="{{$contact->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                
                                <a class="btn btn-sm btn-primary" href="{{route('contacts.edit', $contact->id)}}"><i class="fas fa-edit"></i></a>
                                <a class="btn btn-sm btn-warning" href="{{route('contacts.show', $contact->id)}}"><i class="fas fa-eye"></i></a>
                                {!! Form::close() !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <hr>
    <a href="{{route('contacts.create')}}?company_id={{$company->id}}" class="btn btn-success"><i class="fas fa-plus"></i> Aggiungi contatto</a>
</div>
