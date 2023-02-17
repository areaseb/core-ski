@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Clienti'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">

                    <div class="row">
                        <div class="col-3">
                            <div class="form-group ta">
                                <input style="width:100%" id="autoComplete" type="text" tabindex="1">@if(request()->has('id'))<a title="reset" href="{{url('companies')}}" class="btn btn-danger reset"><i class="fas fa-times"></i></a>@endif
                        		<div class="selection"></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group ta">
                                <input style="width:100%" id="autoCompleteContacts" type="text" tabindex="1">@if(request()->has('id'))<a title="reset" href="{{url('companies')}}" class="btn btn-danger reset"><i class="fas fa-times"></i></a>@endif
                        		<div class="selection"></div>
                            </div>
                        </div>
                        <div class="col-6 text-right">

                            <div class="card-tools">

                                <div class="btn-group" role="group">
                                    <div class="form-group mr-3 mb-0 mt-2">
                                        <div class="cu&tipo=Fstom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="customSwitch1" @if(request()->input() && !request()->has('id')) checked @endif>
                                            <label class="custom-control-label" for="customSwitch1">Ricerca Avanzata</label>
                                        </div>
                                    </div>

                                    @can('companies.read')

                                        <div class="btn-group" role="group">
                                            <button id="create" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" data-display="static" aria-expanded="false">
                                                CSV
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" title="esporta aziende da csv" href="{{url('exports/companies/'. str_replace(request()->url(), '',request()->fullUrl()))}}"><i class="fas fa-download"></i> Esporta in csv</a>
                                                @can('companies.write') <a class="dropdown-item" title="importa aziende da csv" href="{{url('imports/companies')}}"><i class="fas fa-upload"></i> Importa da csv</a> @endcan
                                            </div>
                                        </div>
                                        <a class="btn btn-primary" href="{{route('companies.create')}}"><i class="fas fa-plus"></i> Crea Azienda</a>

                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-body">

                    @include('areaseb::core.companies.components.advanced-search', ['url_action' => 'companies'])

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-php">
                            <thead>
                                <tr>
                                    <th data-field="rag_soc" data-order="asc">Ragione Sociale <i class="fas fa-sort"></i></th>
                                    <th>Contatto</th>
                                    <th>Tipo</th>
                                    <th>
                                        <a href="#" data-toggle="tooltip" data-placement="top" title="" data-original-title="TEXT" style="color:#000;">Categoria</a>
                                    </th>
                                    <th>Email</th>
                                    <th>Telefono</th>
                                    <th>Origine</th>
                                    <th data-field="created_at" data-order="asc">Data <i class="fas fa-sort"></i></th>
                                    @can('companies.write')
                                        <th data-orderable="false" style="width:320px;"></th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($companies as $company)
                                    @php
                                        $contact = null;
                                        $notes = $company->note_list;
                                    @endphp
                                    <tr id="row-{{$company->id}}" @if($company->client->nome == 'Lead') class="table-info" @endif</tr>
                                        <td>

                                            @if($company->supplier)
                                                <span style="font-size:100%;" class="badge badge-danger badge-pill">F</span>
                                            @endif

                                            @if($notes)

                                                <a class="badge badge-info" href="{{$company->url}}"
                                                        data-toggle="tooltip"
                                                        data-html="true"
                                                        data-original-title="{{$notes}}">
                                                    <i class="far fa-comment"></i>
                                                </a>

                                            @endif

                                            <a class="defaultColor" href="{{$company->url}}">
                                                {{$company->rag_soc}}
                                            </a>
                                        </td>
                                        <td>
                                            @if($company->contacts->isEmpty())
                                                <small><a title="crea contatto" href="{{route('contacts.create')}}?company_id={{$company->id}}" class="badge badge-primary"><i class="fa fa-plus"></i></a></small>
                                            @else
                                                @php $contact = $company->contacts->first(); @endphp
                                                <small><a title="modifica contatto" href="{{route('contacts.edit', $contact->id)}}" class="badge badge-warning"><i class="fa fa-edit"></i></a> {{$contact->fullname}} </small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                {{$company->client ? $company->client->nome : ''}}
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                {{$company->sector ? $company->sector->nome : ''}}
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                {{$company->email}}
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                @if($company->int_mobile)
                                                    {{$company->int_mobile}}
                                                @elseif($company->int_phone)
                                                    {{$company->int_phone}}
                                                @else
                                                    @if($contact)
                                                        {{$contact->int_number}}
                                                    @endif
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <small>{{$company->origin}}</small>
                                        </td>
                                        <td>
                                            <small>{{$company->created_at->format('d/m/Y')}}</small>
                                        </td>
                                        @can('companies.write')
                                            <td class="pl-2" style="position:relative;">
                                                {!! Form::open(['method' => 'delete', 'url' => $company->url, 'id' => "form-".$company->id]) !!}
                                                    <a href="{{$company->url}}/edit" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>

                                                    @can('companies.delete')
                                                        <button type="submit" id="{{$company->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                                    @endcan

                                                    @includeIf('deals::quick-btn.link-company')
                                                    @includeIf('killerquote::quick-btn.link-company')

                                                    <a href="{{ route('invoices.create') }}?company_id={{ $company->id }}" target="_BLANK" title="create fattura" class="btn btn-primary btn-icon btn-sm"><i class="fas fa-file-invoice"></i></a>

                                                    @if($company->email)
                                                        <a href="mailto:{{$company->email}}" target="_BLANK" class="btn btn-orange btn-icon btn-sm"><i class="far fa-paper-plane"></i></a>
                                                    @endif

                                                    @if($company->int_mobile || (!is_null($contact) && !is_null($contact->int_number)))

                                                        @php $wan = $company->int_mobile ?? $contact->int_number; @endphp

                                                        @if($company->client->nome != '')
                                                            @if($company->client->nome == 'Lead')
                                                                <a data-model="Company" data-id="{{$company->id}}" style="position:absolute; right:3px; top:5px;" target="_BLANK" href="https://web.whatsapp.com/send?phone={{$wan}}&text=Buongiorno {{is_null($contact) ? $company->rag_soc : $contact->fullname}}{{config('core.wa') ?? ''}}" class="btn btn-sm btn-success waClicked"><i class="fab fa-whatsapp"></i></a>
                                                            @else
                                                                <a style="position:absolute; right:3px; top:5px;" target="_BLANK" href="https://web.whatsapp.com/send?phone={{$wan}}&text=Buongiorno {{is_null($contact) ? $company->rag_soc : $contact->fullname}}{{config('core.wa') ?? ''}}" class="btn btn-sm btn-success"><i class="fab fa-whatsapp"></i></a>
                                                            @endif
                                                        @else
                                                            <a style="position:absolute; right:3px; top:5px;" target="_BLANK" href="https://web.whatsapp.com/send?phone={{$wan}}&text=Buongiorno {{is_null($contact) ? $company->rag_soc : $contact->fullname}}{{config('core.wa') ?? ''}}" class="btn btn-sm btn-success"><i class="fab fa-whatsapp"></i></a>
                                                        @endif

                                                    @endif

                                                {!! Form::close() !!}
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <p class="text-left text-muted">{{$companies->count()}} of {{ $companies->total() }} aziende</p>
                    {{ $companies->appends(request()->input())->links() }}
                </div>

            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/js/autoComplete.min.js"></script>

<script>
 $('[data-toggle="tooltip"]').tooltip();


 @if(config('core.LTP'))
     $('a.waClicked').on('click', function(e){
         e.preventDefault();
         let redirect = $(this).attr('href');
         let data = {};
         data = {
             model: 'Company',
             id: $(this).attr('data-id'),
             _token: "{{csrf_token()}}",
             field: 'client_id',
             value: '1'
         }
         $.post(baseURL+'update-field', data).done(function( response ) {
             console.log(response);
             window.open(redirect, '_blank').focus();
         });
     });
 @endif


const autoCompletejs = new autoComplete({
	data: {
		src: async () => {
			document.querySelector("#autoComplete").setAttribute("placeholder", "Loading...");
			const source = await fetch(
				"{{url('api/ta/companies')}}"
			);
			const data = await source.json();
            console.log('RESULTS: ',data)
			document.querySelector("#autoComplete").setAttribute("placeholder", "Cerca Aziende");
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
	placeHolder: "Cerca Aziende",
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
        window.location.href = "{{url('companies?id=')}}"+feedback.selection.value.id;
	}
});



const autoCompleteContactsjs = new autoComplete({
	data: {
		src: async () => {
			document.querySelector("#autoCompleteContacts").setAttribute("placeholder", "Loading...");
			const source = await fetch(
				"{{url('api/ta/contacts')}}"
			);
			const data = await source.json();
            console.log('RESULTS: ',data)
			document.querySelector("#autoCompleteContacts").setAttribute("placeholder", "Cerca Contatto");
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
	selector: "#autoCompleteContacts",
	threshold: 2,
	debounce: 0,
	searchEngine: "strict",
	highlight: true,
	maxResults: 5,
	resultsList: {
		render: true,
		container: (source) => {
			source.setAttribute("id", "autoCompleteContacts_list");
		},
		destination: document.querySelector("#autoCompleteContacts"),
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
		document.querySelector("#autoCompleteContacts_list").appendChild(result);
	},
	onSelection: (feedback) => {
		const selection = feedback.selection.value.name;
		document.querySelector("#autoCompleteContacts").value = "";
		document.querySelector("#autoCompleteContacts").setAttribute("placeholder", selection);
		console.log(feedback);
        window.location.href = "{{url('contacts')}}/"+feedback.selection.value.id;
	}
});


 $('[data-toggle="tooltip"]').tooltip();



</script>


@stop
