@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Clienti'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">

                    <div class="row">
                        <div class="col-2">
                            <div class="form-group ta">
                                <input style="width:100%" id="autoComplete" type="text" tabindex="1">@if(request()->has('id'))<a title="reset" href="{{url('companies')}}" class="btn btn-danger reset"><i class="fas fa-times"></i></a>@endif
                        		<div class="selection"></div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group ta">
                                <input style="width:100%" id="autoCompleteContacts" type="text" tabindex="1">@if(request()->has('id'))<a title="reset" href="{{url('companies')}}" class="btn btn-danger reset"><i class="fas fa-times"></i></a>@endif
                        		<div class="selection"></div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <input type="text" class="form-control" name="search" value="@if(isset($_GET['search'])) @php echo $_GET['search'] @endphp @endif" placeholder="Ricerca libera">
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
										@if(auth()->user()->can('companies.merge') || auth()->user()->hasRole('super'))
                                        	<a class="btn btn-warning" href="{{route('api.companies.merge')}}"><i class="fas fa-link"></i> Unisci clienti</a>
                                        @endif
                                        <div class="btn-group" role="group">
                                            <button id="create" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" data-display="static" aria-expanded="false">
                                                CSV
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" title="esporta aziende da csv" href="{{url('exports/companies/'. str_replace(request()->url(), '',request()->fullUrl()))}}"><i class="fas fa-download"></i> Esporta in csv</a>
                                                @can('companies.write') <a class="dropdown-item" title="importa aziende da csv" href="{{url('imports/companies')}}"><i class="fas fa-upload"></i> Importa da csv</a> @endcan
                                            </div>
                                        </div>
                                        <a class="btn btn-primary" href="{{route('companies.create')}}"><i class="fas fa-plus"></i> Crea Cliente</a>

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
                                    <th>Email</th>
                                    <th>Telefono</th>
                                    <th>CF / P.IVA</th>
                                    {{-- <th>Origine</th>
                                    <th data-field="created_at" data-order="asc">Data <i class="fas fa-sort"></i></th> --}}
                                    <th>Tipo</th>
                                    <th>
                                        <a href="#" data-toggle="tooltip" data-placement="top" title="" data-original-title="TEXT" style="color:#000;">Categoria</a>
                                    </th>
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
                                    <tr id="row-{{$company->id}}" @if($company->private == 1 && ($company->cf == '' || $company->address == '')) style="background-color: orange;" @endif>
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
                                                @php 
                                                	$contacts = $company->contacts; 
                                                @endphp
                                                @if($contacts)
	                                                @foreach($contacts as $contact)
	                                                	<div class="d-block">
			                                                <small><a title="modifica contatto" href="{{route('contacts.edit', $contact->id)}}" class="badge badge-warning"><i class="fa fa-edit"></i></a> <a href="{{route('contacts.show', $contact->id)}}">{{$contact->fullname}}</a> </small>
			                                                @if(Areaseb\Core\Models\ContactDisabled::where('contact_id', $contact->id)->count() > 0)
			                                                	<i class="fa fa-wheelchair float-right mt-1" aria-hidden="true"></i>
			                                                @endif
			                                            </div>
		                                            @endforeach
		                                        @endif
                                            @endif
                                        </td>
                                        <td>
                                            {{$company->email}}
                                        </td>
                                        <td>
                                            @if($company->int_mobile)
                                                <a href="tel:{{$company->int_mobile}}">{{$company->int_mobile}}</a>
                                            @elseif($company->int_phone)
                                                <a href="tel:{{$company->int_phone}}">{{$company->int_phone}}</a>
                                            @else
                                                @if($contact)
                                                    <a href="tel:{{$contact->int_number}}">{{$contact->int_number}}</a>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if($company->private)
                                                {{$company->cf}}
                                            @elseif(!$company->private)
                                                {{$company->piva}}
                                            @else
                                                @if($contact)
                                                    {{$contact->cod_fiscale}}
                                                @endif
                                            @endif
                                        </td>
                                        {{-- <td>
                                            <small>{{$company->origin}}</small>
                                        </td>
                                        <td>
                                            <small>{{$company->created_at->format('d/m/Y')}}</small>
                                        </td> --}}
                                        <td>
                                            {{$company->client ? $company->client->nome : ''}}
                                        </td>
                                        <td>
                                            {{$company->sector ? $company->sector->nome : ''}}
                                        </td>
                                        @can('companies.write')
                                            <td class="pl-2" style="position:relative;">
                                                {!! Form::open(['method' => 'delete', 'url' => $company->url, 'id' => "form-".$company->id]) !!}
                                                    <a href="{{$company->url}}/edit" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>

                                                    @if(auth()->user()->hasRole('super') || auth()->user()->can('companies.delete'))
                                                        <button type="submit" id="{{$company->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                                    @endif

                                                    @includeIf('deals::quick-btn.link-company')
                                                    @includeIf('killerquote::quick-btn.link-company')


                                                    @if($company->email)
                                                    	@php
                                                    		$subject = "Link di verifica dati e inserimento nuovi dati";
                                                    		$body = "LINK%20DI%20VERIFICA%0D%0A%0D%0AIn%20questi%20giorni%20stiamo%20cercando%20di%20porre%20le%20corrette%20basi%20per%20la%20stagione%20e%2C%20per%20questo%20motivo%2C%20vi%20chiediamo%20cortesemente%20di%20dedicare%20qualche%20minuto%20alla%20compilazione%20del%20form%20al%20link%20di%20seguito%20riportato%2C%20necessario%20all'effettuazione%20di%20una%20verifica%20e%20completamento%20delle%20anagrafiche%20caricate%20nel%20nostro%20gestionale%20(in%20particolare%20per%20la%20corretta%20emissione%2Farchiviazione%20delle%20ricevute%20delle%20lezioni%20svolte%2Fda%20svolgere).%0D%0A%0D%0ALink%20form%20verifica%20dati%20cliente%3A%0D%0Ahttps%3A%2F%2Fwww.sciedipassione.com%2Fform-verifica-dati-cliente%2F%0D%0APer%20motivi%20di%20sicurezza%2C%20l'accesso%20al%20form%20%C3%A8%20protetto%20dalla%20seguente%20password%3A%20%20sciedipassione%0D%0A%0D%0A%0D%0ALINK%20INSERIMENTO%20NUOVI%20DATI%0D%0A%0D%0AIn%20relazione%20alla%20richiesta%20di%20prenotazione%2C%20Le%20chiediamo%20la%20gentilezza%20di%20dedicare%20qualche%20minuto%20alla%20compilazione%20del%20form%20al%20link%20di%20seguito%20riportato%2C%20necessario%20al%20corretto%20caricamento%20delle%20lezioni%20(ed%20a%20una%20corretta%20successiva%20emissione%20delle%20ricevute%20per%20le%20lezioni%20svolte).%0D%0A%0D%0ALink%20form%20verifica%20dati%20cliente%3A%0D%0Ahttps%3A%2F%2Fwww.sciedipassione.com%2Fform-cliente%2F%0D%0APer%20motivi%20di%20sicurezza%2C%20l'accesso%20al%20form%20%C3%A8%20protetto%20dalla%20seguente%20password%3A%20sciedipassione%0D%0A%0D%0A";
                                                    		
                                                    		//per codificare il body: https://mailto.vercel.app/
                                                    		
                                                    	@endphp
                                                        <a href="mailto:{{$company->email}}" target="_BLANK" class="btn btn-orange btn-icon btn-sm" title="Scrivi al cliente"><i class="far fa-paper-plane"></i></a>
                                                        <a href="mailto:{{$company->email}}?subject={{$subject}}&body={{$body}}" target="_BLANK" class="btn btn-primary btn-icon btn-sm" title="Invia mail di richiesta dati al cliente"><i class="far fa-paper-plane"></i></a>
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
                    <p class="text-left text-muted">{{$companies->count()}} of {{ $companies->total() }} clienti</p>
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

 $("[name='search']").on('input', function(e){
/* 	e.preventDefault();
 	
 	let data = {};
    data.search = ($("[name='search']").val() == "") ? null : $("[name='search']").val();
    data._token = token;
    
    $.post( "{{url('api/companies/create-contacts')}}", data, function(response){
	    new Noty({
	        text: response,
	        type: 'success',
	        theme: 'bootstrap-v4',
	        timeout: 2500,
	        layout: 'topRight'
	    }).show();
	});*/
		
	setTimeout(() => {
		search = ($("[name='search']").val() == "") ? null : $("[name='search']").val();
	    
	    window.location.href="/companies?search=" + search;
	    
	}, "1500");
	
	
 });

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
			document.querySelector("#autoCompleteContacts").setAttribute("placeholder", "Cerca Privato");
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
	placeHolder: "Cerca Privato",
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
