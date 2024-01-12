@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Prima nota'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">

                </div>
                <div class="card-body">

                    @include('areaseb::core.prima_nota.components.advanced-search', ['url_action' => 'prima_nota'])
                    <br>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-php">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    @foreach(config('invoice.payment_modes') as $item)
                                        @if($item != '')
                                            <th>{{$item}}  </th>
                                        @endif
                                    @endforeach                            
                                    <th>Totale incassato</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($arr_finale as $dd)
                                <tr>
                                    @foreach($dd as $d)
										@if (strpos($d, "/") != false)
											<td> {{$d}}</td>
										@else
											<td> € &nbsp;{{$d}}</td>
										@endif

                                    @endforeach  
                                </tr>
                                
                                @endforeach
                                
                            </tbody>
                        </table>
                    </div>
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
