@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Costi'])



@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light pb-0">
                    <div class="row">
                        <div class="col-12 col-sm-6 col-xl-2">
                            <div class="form-group ta mb-3">
                                <input style="width:100%" id="autoComplete" type="text" tabindex="1">
                        		<div class="selection"></div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="form-group">
                                {!!Form::select('company_id', $companies, request('company_id'), ['class' => 'select2Comp'])!!}
                            </div>
                        </div>
                        <div class="col-12 col-sm-4 col-xl-1">
                        <div class="form-group">
                            {!!Form::select('category_id', $selectCats, request('category_id'), ['class' => 'select2Cat'])!!}
                        </div>
                    </div>
                    @if(request('category_id'))
                        <div class="col-12 col-sm-4 col-xl-1">
                            <div class="form-group">
                                {!!Form::select('expense_id', $selectExps, request('expense_id'), ['class' => 'select2Exp'])!!}
                            </div>
                        </div>
                    @endif

                    <div class="col-6 col-sm-4 col-xl-2">
                        <div class="form-group">
                            <div class="row">
                                <div class="col">
                                    {!!Form::select('anno', [''=>'']+Areaseb\Core\Models\Cost::yearsArray(), request('anno'), ['class' => 'select2A'])!!}
                                </div>
                                <div class="col">
                                    {!!Form::select('mese', [''=>'']+__('dates.months_arr'), request('mese'), ['class' => 'select2M'])!!}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(request('category_id'))
                        @php $offset = 'offset-xl-1'; @endphp
                    @else
                        @php $offset = 'offset-xl-2'; @endphp
                    @endif

                    <div class=" col-6 col-sm-4 col-xl-2 {{$offset}} text-right">
                            <div class="card-tools">
                                @if(request()->input())<a title="reset" href="{{url('costs')}}" class="btn btn-danger reset"><i class="fas fa-times"></i></a>@endif
                                @can('costs.write')
                                    <a class="btn btn-primary" href="{{url('costs/create')}}"><i class="fas fa-plus"></i> Crea Acquisto</a>
                                    @if(config('core.modules')['fe'])
                                        <div class="btn-group" role="group">
                                            <button id="create" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" data-display="static" aria-expanded="false">
                                                XML
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item export" title="importa fattura da xml" href="{{url('api/costs/export/' . str_replace(request()->url(), '',request()->fullUrl()) )}}"><i class="fas fa-download"></i> Esporta</a>
                                                <a class="dropdown-item" title="importa fattura da xml" href="{{url('api/costs/import')}}"><i class="fas fa-upload"></i> Importa Xml</a>
                                            </div>
                                        </div>
                                    @endif
                                @endcan

                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-sm table-bordered table-striped table-php">
                            <thead>
                                <tr>
                                    <th>Numero</th>
                                    <th data-field="data" data-order="asc">Data <i class="fas fa-sort"></i></th>
                                    <th data-field="data_ricezione" data-order="asc">Data Ric. <i class="fas fa-sort"></i></th>
                                    <th>Fornitore</th>
                                    <th>Prodotto</th>
                                    <th data-field="imponibile" data-order="asc">Imponibile <i class="fas fa-sort"></i></th>
                                    <th>IVA</th>
                                    <th data-field="totale" data-order="asc">Totale <i class="fas fa-sort"></i></th>
                                    <th>Scadenza</th>
                                    @can('costs.write')
                                        <th>Saldato</th>
                                    @endcan
                                    <th style="width:150px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($costs as $cost)
                                    <tr id="row-{{$cost->id}}" @if($cost->is_my_company) class="bg-success disabled" @endif>
                                        <td>{{$cost->nome}}</td>
                                        <td>{{$cost->data->format('d/m/Y')}}</td>
                                        <td>
                                            @if(is_null($cost->data_ricezione))
                                                {{$cost->data->format('d/m/Y')}}
                                            @else
                                                {{$cost->data_ricezione->format('d/m/Y')}}
                                            @endif
                                        </td>
                                        <td><a class="defaultColor" href="{{$cost->company->url}}">{{$cost->company->rag_soc}}</a></td>
                                        <td>
                                            @if($cost->expense->is_default)
                                                <b><span class="text-danger">{{$cost->expense->nome}}</span></b>
                                            @else
                                                {{$cost->expense->nome}}
                                            @endif

                                        </td>

                                        @if($cost->imponibile < 0)
                                            <td class="bg-success disabled">{{$cost->imponibile_formatted}}</td>
                                        @else
                                            <td>{{$cost->imponibile_formatted}}</td>
                                        @endif

                                        <td>{{$cost->iva}}</td>

                                        @if($cost->totale < 0)
                                            <td class="bg-success disabled">{{$cost->totale_formatted}}</td>
                                        @else
                                            <td>{{$cost->totale_formatted}}</td>
                                        @endif

                                        <td>{{$cost->data_scadenza->format('d/m/Y')}}</td>
                                        @can('costs.write')
                                            <td class="text-center">
                                                @if($cost->saldato)
                                                    <a href="{{route('costs.payments.show', $cost->id)}}" class="btn btn-default"><i class="fa text-success fa-check"></i></a>
                                                @else
                                                    @if($cost->payment_status)
                                                        <a href="{{route('costs.payments.show', $cost->id)}}" class="btn btn-default btn-sm" style="background-color:{{$cost->payment_color}}; color:#000;">
                                                            {{$cost->payment_status}}%
                                                        </a>
                                                    @else
                                                        <a href="{{route('costs.payments.show', $cost->id)}}" class="btn btn-default"><i class="fa text-danger fa-times"></i></a>
                                                    @endif
                                                @endif
                                            </td>
                                        @endcan

                                        <td class="pl-2">
                                            {!! Form::open(['method' => 'delete', 'url' => $cost->url, 'id' => "form-".$cost->id]) !!}
                                                @can('costs.write')
                                                    <a href="{{$cost->url}}/edit" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                                    <a href="{{$cost->url}}/media" class="btn btn-info btn-icon btn-sm"><i class="fa fa-image"></i></a>
                                                @endcan
                                                @can('costs.delete')
                                                    <button type="submit" id="{{$cost->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                                @endcan



                                                @if(config('core.modules')['fe'])
                                                    @if($cost->media()->xml()->exists())
                                                        @if($cost->media()->pdf()->exists())
                                                            <a href="{{$cost->pdf}}" target="_BLANK" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-file-pdf"></i></a>
                                                        @else
                                                            @if($cost->media()->where('filename', 'like', '%.p7m')->exists())
                                                                <a href="{{url('pdf/costs/'.$cost->id)}}" target="_BLANK" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-file-pdf"></i></a>
                                                            @else
                                                                <a href="{{url('pdf/costs/'.$cost->id)}}" target="_BLANK" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-file-pdf"></i></a>
                                                            @endif
                                                        @endif
                                                    @elseif($cost->media()->pdf()->exists())
                                                        <a href="{{$cost->pdf}}" target="_BLANK" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-file-pdf"></i></a>
                                                    @endif
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
                    <p class="text-left text-muted">{{$costs->count()}} of {{ $costs->total() }} Costi</p>
                    {{ $costs->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
    @include('areaseb::core.accounting.costs.components.stats-bottom')
@stop


@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/js/autoComplete.min.js"></script>

<script>

$('.select2Comp').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Seleziona Azienda'});
$('.select2Comp').on('change', function(){
    sendForm()
});

$('.select2Cat').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Categoria'});
$('.select2Cat').on('change', function(){
    sendForm()
});

$('.select2Exp').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Spesa'});
$('.select2Exp').on('change', function(){
    sendForm()
});


$('.select2A').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Anno'});
$('.select2A').on('change', function(){
    sendForm()
});

$('.select2M').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Mese'});
$('.select2M').on('change', function(){
    sendForm()
});

function sendForm()
{
    let expense = $('.select2Exp').find(':selected').val();
    if(typeof(expense) == 'undefined')
    {
        expense = '';
    }
    let company = $('.select2Comp').find(':selected').val();
    let category = $('.select2Cat').find(':selected').val();
    let anno = $('.select2A').find(':selected').val();
    let mese = $('.select2M').find(':selected').val();
    window.location.href = '?company_id='+company+'&category_id='+category+'&expense_id='+expense+'&anno='+anno+'&mese='+mese;
}


// document.querySelector("#autoComplete").addEventListener("autoComplete", (event) => {console.log(event);});
const autoCompletejs = new autoComplete({
	data: {
		src: async () => {
			document.querySelector("#autoComplete").setAttribute("placeholder", "Loading...");
			const source = await fetch(
				"{{url('api/ta/costs')}}"
			);
			const data = await source.json();
			document.querySelector("#autoComplete").setAttribute("placeholder", "Cerca numero");
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
	placeHolder: "Cerca numero",
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
        window.location.href = "{{url('costs?id=')}}"+feedback.selection.value.id;
	}
});


</script>
@stop
