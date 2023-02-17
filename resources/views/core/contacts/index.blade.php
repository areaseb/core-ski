@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Contatti'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group ta">
                                <input style="width:100%" id="autoComplete" type="text" tabindex="1">@if(request()->has('id'))<a title="reset" href="{{url('contacts')}}" class="btn btn-danger reset"><i class="fas fa-times"></i></a>@endif
                        		<div class="selection"></div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2 mt-md-0 text-right-if-996">
                            <div class="card-tools">

                                <div class="btn-group" role="group">
                                    <div class="form-group mr-3 mb-0 mt-2">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="customSwitch1" @if(request()->input() && !request()->has('id')) checked @endif>
                                            <label class="custom-control-label" for="customSwitch1">Ricerca Avanzata</label>
                                        </div>
                                    </div>

                                    <div class="btn-group" role="group">
                                        <button id="lists" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" data-display="static">
                                            Liste
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @include('areaseb::core.contacts.components.list-nav')
                                        </div>
                                    </div>
                                    @can('contacts.write')

                                        <div class="btn-group" role="group">
                                            <button id="create" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" data-display="static" aria-expanded="false">
                                                CSV
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" title="esporta contatti in csv" href="{{url('exports/contacts/'. str_replace(request()->url(), '',request()->fullUrl()))}}"><i class="fas fa-download"></i> Esporta in csv</a>
                                                @can('contacts.write') <a class="dropdown-item" title="importa aziende da csv" href="{{url('imports/contacts')}}"><i class="fas fa-upload"></i> Importa da csv</a> @endcan
                                            </div>
                                        </div>

                                        <a class="btn btn-primary" href="{{route('contacts.create')}}"><i class="fas fa-plus"></i> Crea</a>

                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    @include('areaseb::core.contacts.components.advanced-search', ['url_action' => 'contacts'])
                    <div class="table-responsive">
                        <table id="table" class="table table-sm table-bordered table-striped table-php">
                            <thead>
                                <tr>
                                    <th data-field="nome" data-order="asc">Nome <i class="fas fa-sort"></i></th>
                                    <th> Azienda</th>
                                    <th>Tipo</th>
                                    <th>Cat.</th>
                                    <th>Email</th>
                                    <th>Tel</th>
                                    <th style="width:77px;">Origine</th>
                                    <th data-field="created_at" data-order="asc" style="width:85px;">Data <i class="fas fa-sort"></i></th>
                                    @can('contacts.write')
                                        <th data-orderable="false"></th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>

                                @php
                                    $wa = config('core.wa') ?? '';
                                @endphp

                                @foreach($contacts as $contact)


                                    @php
                                        $t = '';
                                        if($contact->company)
                                        {
                                            if($contact->company->client)
                                            {
                                                $t = $contact->company->client->nome;
                                            }
                                        }
                                    @endphp

                                    <tr id="row-{{$contact->id}}" @if($t == 'Lead') class="table-info" @endif >
                                        <td><a class="defaultColor" @if($contact->note) data-toggle="tooltip" title="{{$contact->note}}" @endif href="{{$contact->url}}">{{$contact->fullname}}</a></td>
                                        <td>
                                            @if($contact->company_id)
                                                {{$contact->company->rag_soc}}
                                            @endif
                                        </td>
                                        <td>
                                            {{$t}}
                                        </td>
                                        <td>
                                            @if($contact->company)
                                                @if($contact->company->sector)
                                                    {{$contact->company->sector->nome}}
                                                @endif
                                            @endif
                                        </td>
                                        <td><small>{{$contact->email}}</small></td>
                                        <td><small>{{$contact->cellulare}}</small></td>
                                        <td><small>{{$contact->origin}}</small></td>
                                        <td><small>{{$contact->created_at->format('d/m/Y')}}</small></td>
                                        @can('contacts.write')
                                            <td class="pl-2" style="position:relative;">
                                                @include('areaseb::core.contacts.components.actions', ['url_action' => 'contacts'])
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <p class="text-left text-muted">{{$contacts->count()}} of {{ $contacts->total() }} contatti</p>
                    {{ $contacts->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
@stop






@push('scripts')


    <script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/js/autoComplete.min.js"></script>

    <script>

    $('[data-toggle="tooltip"]').tooltip();

    $('a.makeCompany').on('click', function(e){
        e.preventDefault();
        $('form#makeCompany-'+$(this).attr('data-id')).submit();
    });

    $('a.makeUser').on('click', function(e){
        e.preventDefault();
        $('form#makeUser-'+$(this).attr('data-id')).submit();
    });

    const autoCompletejs = new autoComplete({
    data: {
        src: async () => {
            document.querySelector("#autoComplete").setAttribute("placeholder", "Loading...");
            const source = await fetch(
                "{{url('api/ta/contacts')}}"
            );
            const data = await source.json();
            document.querySelector("#autoComplete").setAttribute("placeholder", "Cerca Contatto");
            return data;
        },
        key: ["name"],
        cache: false
    },
    sort: (a, b) => {
        if (a.match < b.match) return -1;
        if (a.match > b.match) return 1;
        return 0;
    },
    placeHolder: "Cerca Contatto",
    selector: "#autoComplete",
    threshold: 2,
    debounce: 0,
    searchEngine: "strict",
    highlight: true,
    maxResults: 5,
    resultsList: {
        render: true,
        container: (source) => {
            source.setAttribute("id", "autoComplete_list");
        },
        destination: document.querySelector("#autoComplete"),
        position: "afterend",
        element: "ul"
    },
    resultItem: {
        content: (data, source) => {
            source.innerHTML = data.match;
        },
        element: "li"
    },
    noResults: () => {
        const result = document.createElement("li");
        result.setAttribute("class", "no_result");
        result.setAttribute("tabindex", "1");
        result.innerHTML = "No Results";
        document.querySelector("#autoComplete_list").appendChild(result);
    },
    onSelection: (feedback) => {
        const selection = feedback.selection.value.name;
        document.querySelector("#autoComplete").value = "";
        document.querySelector("#autoComplete").setAttribute("placeholder", selection);
        console.log(feedback);
        window.location.href = "{{url('contacts?id=')}}"+feedback.selection.value.id;
    }
    });


    $('a.waClicked').on('click', function(e){
        e.preventDefault();
        let url = $(this).attr('href');
        let data = {};
        data._token = token;
        data.id = $(this).attr('data-id');
        data.model = $(this).attr('data-model');
        data.field = 'client_id';
        data.value = 1;
        console.log(data);
        axios.post(baseURL+'update-field', data).then((response) => {
            if(response.data == 'done')
            {
                window.open(url, '_blank');
            }
        })
    });

</script>

{{-- @includeIf('killerquote::quick-btn.script')
@includeIf('deals::quick-btn.script') --}}

@endpush
