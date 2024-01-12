<div class="row">
    @if($company->contacts()->exists())
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipologia</th>
                        <th>Email</th>
                        <th>Cellulare</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($company->contacts as $contact)
                        <tr id="row-{{$contact->id}}">
                            @php
                                $isDisabile = Areaseb\Core\Models\ContactDisabled::where('contact_id', $contact->id)->count() > 0;
                            @endphp
                            @if($isDisabile)
                                <td><i class="fa fa-wheelchair" aria-hidden="true"></i>&nbsp&nbsp<b>{{$contact->fullname}}</b></td>
                            @else
                                <td><b>{{$contact->fullname}}</b></td>
                            @endif

                            @if($contact->contact_type_id == 3)
                            <td>Maestro</td>
                            @endif
                            @if($contact->contact_type_id == 4)
                            <td>Collaboratore</td>
                            @endif
                            <td>{{$contact->email}}</td>
                            <td>{{$contact->cellulare}}</td>
                            <td>
                                {!! Form::open(['method' => 'delete', 'url' => $contact->url, 'id' => "form-".$contact->id]) !!}
                                    <a class="btn btn-sm btn-primary" href="{{route('contacts-office.show', $contact->id)}}"><i class="fas fa-eye"></i></a>
                                    <a class="btn btn-sm btn-warning" href="{{route('contacts-office.edit', $contact->id)}}"><i class="fas fa-edit"></i></a>
                                    @if(auth()->user()->hasRole('super'))
                                        <button type="submit" id="{{$contact->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                    @endif
                                {!! Form::close() !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <hr>
    <a href="{{route('contacts-office.create')}}?company_id={{$company->id}}" class="btn btn-success"><i class="fas fa-plus"></i> Aggiungi contatto</a>
</div>
