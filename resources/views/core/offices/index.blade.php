@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Sedi'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">

                    <div class="row">
                        <div class="col-3">
                            <div class="form-group ta">
                                <input style="width:100%" id="autoComplete" type="text" tabindex="1">@if(request()->has('id'))<a title="reset" href="{{url('offices')}}" class="btn btn-danger reset"><i class="fas fa-times"></i></a>@endif
                        		<div class="selection"></div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group ta">
                                <input style="width:100%" id="autoCompleteContacts" type="text" tabindex="1">@if(request()->has('id'))<a title="reset" href="{{url('offices')}}" class="btn btn-danger reset"><i class="fas fa-times"></i></a>@endif
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

                                    <a class="btn btn-primary" href="{{route('offices.create')}}"><i class="fas fa-plus"></i> Crea Sede</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-body">

                    @include('areaseb::core.offices.components.advanced-search', ['url_action' => 'offices'])

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-php">
                            <thead>
                                <tr>
                                    <th data-field="rag_soc" data-order="asc">Ragione Sociale <i class="fas fa-sort"></i></th>
                                    {{-- <th>Contatto</th> --}}
                                    <th>Tipo</th>
                                    <th>
                                        <a href="#" data-toggle="tooltip" data-placement="top" title="" data-original-title="TEXT" style="color:#000;">Categoria</a>
                                    </th>
                                    <th>Email</th>
                                    <th>Telefono</th>
                                    <th>Origine</th>
                                    <th data-field="created_at" data-order="asc">Data <i class="fas fa-sort"></i></th>
                                    <th data-orderable="false" style="width:320px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($offices as $company)
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

                                                <a class="badge badge-info" href="/offices/{{$company->id}}"
                                                        data-toggle="tooltip"
                                                        data-html="true"
                                                        data-original-title="{{$notes}}">
                                                    <i class="far fa-comment"></i>
                                                </a>

                                            @endif

                                            <a class="defaultColor" href="/offices/{{$company->id}}">
                                                {{$company->rag_soc}}
                                            </a>
                                        </td>
                                        {{-- <td>
                                            @if($company->contacts->isEmpty())
                                                <small><a title="crea contatto" href="contacts-office/create?company_id={{$company->id}}" class="badge badge-primary"><i class="fa fa-plus"></i></a></small>
                                            @else
                                                @php $contact = $company->contacts->first(); @endphp
                                                <small><a title="modifica contatto" href="{{route('contacts.edit', $contact->id)}}" class="badge badge-warning"><i class="fa fa-edit"></i></a> {{$contact->fullname}} </small>
                                            @endif
                                        </td> --}}
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
                                            <td class="pl-2" style="position:relative;">
                                                {!! Form::open(['method' => 'delete', 'url' => str_replace('companies','offices' ,$company->url), 'id' => "form-".$company->id]) !!}
                                                    <a href="/offices/{{$company->id}}/edit" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                                    <button type="submit" id="{{$company->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>

                                                    @if($company->email)
                                                        <a href="mailto:{{$company->email}}" target="_BLANK" class="btn btn-orange btn-icon btn-sm"><i class="far fa-paper-plane"></i></a>
                                                    @endif
                                                {!! Form::close() !!}
                                            </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <p class="text-left text-muted">{{$offices->count()}} of {{ $offices->total() }} sedi</p>
                    {{ $offices->appends(request()->input())->links() }}
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
