@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}contacs">Contatti</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => $contact->fullname])

@section('content')

    <div class="row">

        <div class="col-md-3">

            <div class="card card-info card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        {!!$contact->avatar!!}
                        <h3 class="profile-username text-center">{{$contact->fullname}}</h3>
                        <h7 class="text-center">Codice Fiscale: {{$contact->company != null ? $contact->company->cf : ' - '}}</h7>
                        @if($contact->clients)
                            <p class="text-muted text-center">
                                @foreach($contact->clients as $type)
                                    @if($loop->last)
                                        {{$type->nome}}
                                    @else
                                        {{$type->nome}},
                                    @endif
                                @endforeach
                            </p>
                        @endif
                        @if($contact->company_id)
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item text-center" style="line-height:1rem;">
                                    <a href="{{$contact->company->url}}" >
                                        {{$contact->company->rag_soc}}
                                    </a>
                                </li>
                            </ul>
                        @endif
                        @can('contacts.write')
                            <a href="/contacts-master/{{$contact->id}}/edit" class="btn btn-sm btn-warning btn-block"><b> <i class="fa fa-edit"></i> Modifica</b></a>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Dettagli</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Indirizzo</strong>
                    <p class="text-muted">{{$contact->indirizzo}} <br>
                        {{$contact->cap}}, {{$contact->citta}} {{$contact->provincia}} {{$contact->nazione}}
                    </p>
                    <hr>

                    <strong><i class="fa fa-info mr-1"></i> Info Maestro</strong>
                    @if($contact->dataMaster($contact->id) != null && $contact->dataMaster($contact->id)->collegio)<p class="text-muted"><b>N. Iscr. collegio:</b> {{$contact->dataMaster($contact->id)->collegio}}</p>@endif
                    @if($contact->dataMaster($contact->id) != null && $contact->dataMaster($contact->id)->tipo_socio)<p class="text-muted"><b>Tipo socio:</b> {{$contact->dataMaster($contact->id)->tipo_socio_desc}}</p>@endif
                    @if($contact->dataMaster($contact->id) != null && $contact->dataMaster($contact->id)->disciplina)<p class="text-muted"><b>Disciplina:</b> {{$contact->dataMaster($contact->id)->disciplina_desc}}</p>@endif
                    @if($contact->dataMaster($contact->id) != null && $contact->dataMaster($contact->id)->specializations != "")<p class="text-muted"><b>Specializzazioni:</b> {{$contact->dataMaster($contact->id)->specializations}}</p>@endif
                    

                    @if($contact->dataMaster($contact->id) != null && $contact->dataMaster($contact->id)->color)<p class="text-muted"><b>Colore:</b> <input type="color" id="color" name="color" value="{{$contact->dataMaster($contact->id)->color}}" disabled></p>@endif
                    @if($contact->dataMaster($contact->id) != null && $contact->dataMaster($contact->id)->ordine)<p class="text-muted"><b>Ordine:</b> {{$contact->dataMaster($contact->id)->ordine}}</p>@endif
                    <p class="text-muted"><b>Attivo:</b> {{$contact->attivo == 1 ? 'Si' : 'No'}}</p>
                    <hr>

                    <strong><i class="fas fa-at mr-1"></i> Contatti</strong>
                    @if($contact->cellulare)<p class="text-muted"><b>Tel:</b> {{$contact->cellulare}}</p>@endif
                    @if($contact->email)<p class="text-muted"><b>Email:</b> <small>{{$contact->email}}</small></p>@endif
                </div>
                <div class="card-footer p-0">
                    <a href="#" class="btn btn-sm btn-secondary btn-block print"><i class="fa fa-print"></i> Stampa</a>
                </div>
            </div>
        </div>


        <div class="col-md-9">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#disponibilita" data-toggle="tab">Disponibilità</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-acconti" href="#acconti" data-toggle="tab">Acconti</a></li>
                        @if($contact->note)
                            <li class="nav-item"><a class="nav-link" href="#notes" data-toggle="tab">Note</a></li>
                            <li class="nav-item"><a class="nav-link" href="#reports" data-toggle="tab">Reports</a></li>
                            <li class="nav-item"><a class="nav-link" href="#eventi" data-toggle="tab">Eventi</a></li>
                        @else
                            <li class="nav-item"><a class="nav-link" href="#reports" data-toggle="tab">Reports</a></li>
                            <li class="nav-item"><a class="nav-link" href="#eventi" data-toggle="tab">Eventi</a></li>
                        @endif
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                            <div class="tab-pane active" id="disponibilita">
                                @include('areaseb::core.contacts_master.components.availabilities')
                            </div>
                            <div class="tab-pane" id="acconti">
                                @include('areaseb::core.contacts_master.components.down-payment')
                            </div>

                        @if($contact->note)
                            <div class="tab-pane" id="notes">
                                @include('areaseb::core.contacts_master.components.notes')
                            </div>
                            <div class="tab-pane" id="reports">
                                @include('areaseb::core.contacts_master.components.reports')
                            </div>
                            <div class="tab-pane" id="eventi">
                                @include('areaseb::core.contacts_master.components.events')
                            </div>
                        @else
                            <div class="tab-pane" id="reports">
                                @include('areaseb::core.contacts_master.components.reports')
                            </div>
                            <div class="tab-pane" id="eventi">
                                @include('areaseb::core.contacts_master.components.events')
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>


    </div>

@stop

@section('scripts')

<script>

    var s = sessionStorage.getItem("acconti");
    console.log(s);
    if(s != undefined){     
        $('.nav-link').removeClass('active');
        $('.tab-pane').removeClass('active');
        $('#tab-acconti').addClass('active');
        $('#acconti').addClass('active');
        sessionStorage.clear();
    }



    $('a.print').on('click', function(e){
        window.print();
    });
    
    
    
</script>
@stop
