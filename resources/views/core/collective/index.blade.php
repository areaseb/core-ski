@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Gestione Collettivi'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">

                    <div class="row">
             
                       <div class="col-6">
                            <!-- <div class="form-group ta">
                                <input style="width:100%" id="autoCompleteContacts" type="text" tabindex="1">@if(request()->has('id'))<a title="reset" href="{{url('masters')}}" class="btn btn-danger reset"><i class="fas fa-times"></i></a>@endif
                        		<div class="selection"></div>
                            </div>-->
                        </div>
                        <div class="col-6 text-right">

                            <div class="card-tools">

                                <div class="btn-group" role="group">
                                    <div class="form-group mr-3 mb-0 mt-2">
                                        <div class="cu&tipo=Fstom-control custom-switch">
                 
                                        </div>
                                    </div>

									<div class="btn-group" role="group">
                                            <button id="create" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" data-display="static" aria-expanded="false">
                                                CSV
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" title="esporta maestri da csv" href="{{url('exports/masters/'. str_replace(request()->url(), '',request()->fullUrl()))}}"><i class="fas fa-download"></i> Esporta in csv</a>
                                            </div>
                                        </div>

                                    <a class="btn btn-primary" href="{{route('collective.create')}}"><i class="fas fa-plus"></i> Aggiungi corso</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-body">

                    @include('areaseb::core.collective.components.advanced-search', ['url_action' => 'collective'])

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-php">
                            <thead>
                                <tr>
                                    <th>Disciplina</th>
									<th>Nome</th>
                                    <th>Periodo</th>
									<th>Orari</th>
									<th>Frequenza</th>
                                    <th>Maestri</th>
                                    <th data-orderable="false" style="width:320px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($collectives as $item)
                                <tr id="row-{{$item->id}}">
									<td>{{$item->disciplina}}</td>
									<td>{{$item->nome}}</td>
									<td>{{ date('d/m/Y', strtotime($item->data_in))}} - {{ date('d/m/Y', strtotime($item->data_out))}} </td>
									<td>{{$item->ora_in}} - {{$item->ora_out}}</td>
									<td>{!! $item->frequenza !!}</td>
                                    <td>{!! $item->listMastersInTable()!!}</td>
                                    <td>
                                            {!! Form::open(['method' => 'delete', 'url' => 'collective/'.$item->id, 'id' => "form-".$item->id]) !!}
											
                                                <a class="btn btn-sm btn-primary" href="{{route('collective.show', $item->id)}}"><i class="fas fa-eye"></i></a>
                                                <a class="btn btn-sm btn-warning" href="{{route('collective.edit', $item->id)}}"><i class="fas fa-edit"></i></a>
                                                <button type="submit" id="{{$item->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                            {!! Form::close() !!}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <p class="text-left text-muted">{{$collectives->count()}} of {{ $collectives->total() }} collettivi </p>
                    {{ $collectives->appends(request()->input())->links() }}
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


/*const autoCompletejs = new autoComplete({
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
				"{{url('api/ta/contacts_master')}}"
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
});*/


 $('[data-toggle="tooltip"]').tooltip();



</script>


@stop
