@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Crea Lista'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">
                    <h5 class="card-title" style="line-height:1.2rem;">Filtra e crea lista<br> <p class=" mb-0 text-muted"><small>Usa i filtri per create la tua selezione ed infine premi "Crea Lista".</small></p></h5>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <div class="form-group mr-3 mb-0 mt-2">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="customSwitch1" checked>
                                    <label class="custom-control-label" for="customSwitch1">Ricerca Avanzata</label>
                                </div>
                            </div>
                            @if(request()->input())
                                <a href="{{url('lists/create?'.request()->getQueryString())}}" data-toggle="modal" data-title="Nomina lista" data-modal-title="Nomina lista" data-target="#modal" class="btn btn-primary btn-modal">Crea Lista</a>
                            @else
                                <a href="{{url('lists/create')}}" data-toggle="modal" data-title="Nomina lista" data-modal-title="Nomina lista" data-target="#modal" class="btn btn-primary btn-modal">Crea Lista</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    @include('areaseb::core.lists.components.advanced-search', ['url_action' => 'create-list'])


                        <div class="table-responsive">
                            <table id="table" class="table table-sm table-bordered table-striped table-php">
                                <thead>
                                    <tr>
                                        <th data-field="nome" data-order="asc">Nome <i class="fas fa-sort"></i></th>
                                        <th data-field="tipo" data-order="asc">Tipo <i class="fas fa-sort"></i></th>
                                        <th>Orig.</th>
                                        <th>Cat.</th>
                                        <th class="d-none d-xl-table-cell" data-field="updated_at" data-order="asc">Modificato <i class="fas fa-sort"></i></th>
                                        <th class="d-none d-xl-table-cell">Creato</th>
                                        <th>Provincia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contacts as $contact)

                                        <tr id="row-{{$contact->id}}">
                                            <td>
                                                <a class="defaultColor" href="{{$contact->url}}">
                                                    {{$contact->fullname}}
                                                    @if($contact->company)
                                                        <small style="color:#333">{{$contact->company->rag_soc}}</small>
                                                    @endif
                                                </a>
                                            </td>
                                            <td>
                                                @if($contact->company)
                                                    @if($contact->company->client_id)
                                                        {{$contact->company->client->nome}}
                                                    @endif
                                                @endif
                                            </td>
                                            <td>{{$contact->origin}}</td>
                                            <td>
                                                @if($contact->company)
                                                    @if($contact->company->sector_id)
                                                        {{$contact->company->sector->nome}}
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="d-none d-xl-table-cell" data-sort="{{$contact->updated_at->timestamp}}">{{$contact->updated_at->format('d/m/Y')}}</td>
                                            <td class="d-none d-xl-table-cell" data-sort="{{$contact->created_at->timestamp}}">{{$contact->created_at->format('d/m/Y')}}</td>
                                            <td>{{$contact->provincia}}</td>
                                        </tr>

                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer text-center">
                            <p class="text-left text-muted">{{$contacts->count()}} of {{ $contacts->total() }} contatti</p>
                            {{ $contacts->appends(request()->input())->links() }}
                        </div>
                </div>
            </div>
        </div>
    </div>
@stop
