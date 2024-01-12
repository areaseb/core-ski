@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}companies">Clienti</a></li>
@stop

@section('css')
<style>
.expandable tr:hover{cursor:pointer;}
</style>
@stop

@include('areaseb::layouts.elements.title', ['title' => $company->rag_soc])

@section('content')

    <div class="row">

        <div class="col-md-3">

            <div class="card card-info card-outline">
                <div class="card-body box-profile">
                    <div class="text-center pb-3">
                        {!!$company->avatar!!}
                        <h3 class="profile-username text-center">{{$company->rag_soc}}</h3>

                        @if($company->clients)
                            <p class="text-muted text-center mb-0">
                                @foreach($company->clients as $type)
                                    @if($loop->last)
                                        {{$type->nome}}
                                    @else
                                        {{$type->nome}},
                                    @endif
                                @endforeach
                            </p>
                        @endif

                        @if($company->partner)
                            <p class="text-success mb-0">Partner</p>
                        @endif
                        @if($company->supplier)
                            <p class="text-danger mb-0">Fornitore</p>
                        @endif

                    </div>
                    <ul class="list-group list-group-unbordered mb-3">
                        @if($company->contacts()->exists())
                            @foreach($company->contacts as $contact)
                                <li class="list-group-item text-center" style="line-height:1rem;">
                                    <a href="{{$contact->url}}" >
                                        {{$contact->fullname}}</b>
                                        <code style="color:#222">{{$contact->email}}</code>
                                    </a>
                                </li>
                            @endforeach
                        @else
                            <a href="{{route('contacts.create')}}?company_id={{$company->id}}" class="btn btn-success"><i class="fas fa-plus"></i> Aggiungi contatto</a>
                        @endif
                    </ul>
                    @can('companies.write')
                        <a href="{{$company->url}}/edit" class="btn btn-sm btn-warning btn-block"><b> <i class="fa fa-edit"></i> Modifica</b></a>

                        @if(Illuminate\Support\Facades\Schema::hasTable('deals'))
                            @if(Deals\App\Models\Deal::where('company_id', $company->id)->exists())
                                @php
                                    $d = Deals\App\Models\Deal::where('company_id', $company->id)->latest()->first()->id;
                                @endphp
                                <a href="{{ route('deals.edit', $d) }}" class="btn btn-sm btn-primary btn-block"><b> <i class="fa fa-eye"></i> Vedi trattativa</b></a>
                            @else
                                <a href="{{ route('deals.create')}}?company_id={{$company->id}}" class="btn btn-sm btn-primary btn-block"><b> <i class="fa fa-plus"></i> Crea trattativa</b></a>
                            @endif
                        @endif

                    @endcan
                </div>
            </div>

            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Dettagli</h3>
                </div>
                <div class="card-body psmb">
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Indirizzo</strong>
                    @php
                        $indirizzo_completo = $company->address.','.$company->zip.','.$company->city.','.$company->province;
                    @endphp

                    <br>
                    <a href="{{'https://www.google.com/maps/place/'.str_replace(' ', '+', $indirizzo_completo)}}" class="text-muted">{{$company->address}} <br>
                         {{$company->zip}}, {{$company->city}} ({{$company->province}}) {{$company->nation}}
                    </a>
                    <hr>

                    <strong><i class="fas fa-euro-sign mr-1"></i> Fatturazione</strong>
                    @if($company->pec)<p class="text-muted"><b>PEC:</b> {{$company->pec}}</p>@endif
                    @if($company->piva)<p class="text-muted"><b>P.IVA:</b> {{$company->piva}}</p>@endif
                    @if($company->cf)<p class="text-muted"><b>CF:</b> {{$company->cf}}</p>@endif
                    @if($company->sdi)<p class="text-muted"><b>SDI:</b> {{$company->sdi}}</p>@endif
                    <hr>
                    <strong><i class="fas fa-at mr-1"></i> Contatti</strong>
                    <p class="text-muted"><b>Tel:</b> @if($company->phone != '' && $company->phone != '+39') {{$company->phone}} @else {{$company->mobile}} @endif</p>
                    @if($company->email)<p class="text-muted"><b>Email:</b> <small>{{$company->email}}</small></p>@endif
                    @if($company->email_ordini)<p class="text-muted"><b>Email Ord.:</b> <small>{{$company->email_ordini}}</small></p>@endif
                    @if($company->email_fatture)<p class="text-muted"><b>Email Fatt.:</b> <small>{{$company->email_fatture}}</small></p>@endif
                    <hr>
                    <strong>Settore</strong>
                    <p class="text-muted">{{$company->settore != null ? $company->settore : ' - '}}</p>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link" href="#info" data-toggle="tab">Info</a></li>
                        @if($company->events()->exists())
                            <li class="nav-item"><a class="nav-link" href="#eventi" data-toggle="tab">Eventi</a></li>
                        @endif

                        <li class="nav-item"><a class="nav-link active" href="#contatti" data-toggle="tab">Contatti</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-storia" href="#storia" data-toggle="tab">Storico ore</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-ore" href="#ore" data-toggle="tab">Ore aperte</a></li>
                        {{--<li class="nav-item"><a class="nav-link" id="tab-collettivi" href="#collettivi" data-toggle="tab">Collettivi</a></li>--}}
                        @if($company->contacts->count() > 0)
	                        <li class="nav-item"><a class="nav-link" id="tab-ore" href="#ore_contatti" data-toggle="tab">Ore aperte contatti</a></li>
	                        <li class="nav-item"><a class="nav-link" id="tab-collettivi" href="#collettivi_contatti" data-toggle="tab">Collettivi contatti</a></li>
                        @endif
                        @if($company->invoices()->exists())
                            @can('invoices.read')
                                <li class="nav-item"><a class="nav-link" href="#fatture" data-toggle="tab">Fatture</a></li>
                                <li class="nav-item"><a class="nav-link" href="#fatture_contatti" data-toggle="tab">Fatture contatti</a></li>
                            @endcan
                            @can('products.read')
                                <li class="nav-item"><a class="nav-link" href="#prodotti" data-toggle="tab">Prodotti Venduti</a></li>
                            @endcan
                        @endif
                        @if(Illuminate\Support\Facades\Schema::hasTable('killer_quotes'))
                            @if(KillerQuote\App\Models\KillerQuote::where('company_id', $company->id)->exists())
                                <li class="nav-item"><a class="nav-link" href="#killerquotes" data-toggle="tab">Preventivi</a></li>
                            @endif
                        @endif
                        @if(Illuminate\Support\Facades\Schema::hasTable('deals'))
                            @if(Deals\App\Models\Deal::where('company_id', $company->id)->exists())
                                <li class="nav-item"><a class="nav-link" href="#deals" data-toggle="tab">Trattative</a></li>
                            @endif
                        @endif
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane" id="info">
                            @include('areaseb::core.companies.components.note')
                        </div>
                        <div class="active tab-pane" id="contatti">
                            @can('contacts.read')
                                @include('areaseb::core.companies.components.contacts')
                            @endcan
                        </div>
                        <div class="tab-pane" id="storia">
                                @include('areaseb::core.companies.components.storico_ore')
                        </div>
                        <div class="tab-pane" id="ore">
                                @include('areaseb::core.companies.components.ore')
                        </div>
                        {{--<div class="tab-pane" id="collettivi">
                                @include('areaseb::core.companies.components.collettivi')
                        </div>--}}
                        @if($company->contacts->count() > 0)
	                        <div class="tab-pane" id="ore_contatti">
	                                @include('areaseb::core.companies.components.ore_contatti')
	                        </div>
	                        <div class="tab-pane" id="collettivi_contatti">
	                                @include('areaseb::core.companies.components.collettivi_contatti')
	                        </div>
                        @endif
                        @if($company->events()->exists())
                            <div class="tab-pane" id="eventi">
                                @include('areaseb::core.companies.components.events')
                            </div>
                        @endif
                        <div class="tab-pane" id="fatture">
                            @can('invoices.read')
                                @include('areaseb::core.companies.components.invoices')
                            @endcan
                        </div>
                        <div class="tab-pane" id="fatture_contatti">
                            @can('invoices.read')
                                @include('areaseb::core.companies.components.invoices_contatti')
                            @endcan
                        </div>
                        <div class="tab-pane" id="prodotti">
                            @can('products.read')
                                @include('areaseb::core.companies.components.products')
                            @endcan
                        </div>
                        @includeIf('killerquote::company-tab')
                        @includeIf('deals::company-tab')
                    </div>
                </div>
            </div>
        </div>
    </div>



<div class="modal" tabindex="-1" role="dialog" id="add-sede-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aggiungi Sede</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('api/companies/sede/add')}}" method="post">
            @csrf
            @method('POST')

            <input class="form-control" hidden name="company_id" value="{{$company->id}}" type="text">

                <div class="modal-body">
                    <div class="form-group">
                        <label>Nome</label>
                        <input class="form-control" required name="nome" type="text">
                    </div>
                    <div class="form-group">
                        <label>Indirizzo</label>
                        <input class="form-control" name="indirizzo" type="text">
                    </div>
                    <div class="form-group">
                        <label>CAP</label>
                        <input class="form-control" name="cap" type="text">
                    </div>
                    <div class="form-group">
                        <label>Città</label>
                        <input class="form-control" name="citta" type="text">
                    </div>
                    <div class="form-group">
                        <label>Provincia</label>
                        <input class="form-control" name="provincia" type="text">
                    </div>
                    <div class="form-group">
                        <label>Paese</label>
                        <select class="custom-select" name="paese">
                            <option></option>
                            @foreach(Areaseb\Core\Models\Country::listCountries() as $item)
                                <option value="{{$item}}" >{{$item}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Telefono</label>
                        <input class="form-control" name="telefono" type="tel">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-primary">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal" tabindex="-1" role="dialog" id="update-sede-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifica Sede</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('api/companies/sede/update')}}" method="post">
            @csrf
            @method('POST')
            <input class="form-control" hidden name="id" id="sede_id" type="text">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nome</label>
                        <input class="form-control" required name="nome"  id="nome_update" type="text">
                    </div>
                    <div class="form-group">
                        <label>Indirizzo</label>
                        <input class="form-control" name="indirizzo"  id="indirizzo_update" type="text">
                    </div>
                    <div class="form-group">
                        <label>CAP</label>
                        <input class="form-control" name="cap"  id="cap_update" type="text">
                    </div>
                    <div class="form-group">
                        <label>Città</label>
                        <input class="form-control" name="citta" id="citta_update" type="text">
                    </div>
                    <div class="form-group">
                        <label>Provincia</label>
                        <input class="form-control" name="provincia"  id="provincia_update" type="text">
                    </div>
                    <div class="form-group">
                        <label>Paese</label>
                        <select class="custom-select" name="paese" id="paese_update">
                            <option></option>
                            @foreach(Areaseb\Core\Models\Country::listCountries() as $item)
                                <option value="{{$item}}" >{{$item}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Telefono</label>
                        <input class="form-control" name="telefono" id="telefono_update" type="tel">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-primary">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>

		<div class="modal fade" id="modalAddDoc" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Inserisci i dati del documento in cui aggiungere l'ora</h5>
                        <h5 id="ccID" hidden></h5>
                        <h5 id="oracID" hidden></h5>                        
                        <h5 id="invoiceID" hidden></h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <b>Sede: </b> <label id="lbl_cc"></label>
                            </div> 
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                            <b>Tipo di documento: </b>
                                <select class="form-control"  id="tipo_doc">
                                    <option value="R">Ricevuta</option>
                                    <option value="F">Fattura</option>
                                    <option value="D">Documento</option>
                                    <option value="A">Nota di accredito</option>
                                </select>
                            </div> 
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <b>N° documento: </b><input type="text" id="n_doc" class="form-control">
                            </div> 
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <b>Data documento: </b><input type="date" id="data_doc" class="form-control">
                            </div> 
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                            <b>Prodotto:</b>
                            {!! Form::select('prodotto_doc',$products, null, ['class' => 'select2 ucc', 'data-fouc', 'style' => 'width:100%']) !!}

                            </div> 
                        </div>

                        <br>
                        <div class="alert alert-info alert-info-add-doc" style="display:none">
                            <strong>Attenzione!</strong> Documento non trovato!
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="closemodalAddDoc">Annulla</button>
                        <button type="button" class="btn btn-success" id="btnAddDoc">Inserisci</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="modalAddDocs" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Inserisci i dati del documento in cui aggiungere gli Items selezionati</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                            <b>Tipo di documento: </b>
                                <select class="form-control"  id="tipo_doc_m">
                                    <option value="R">Ricevuta</option>
                                    <option value="F">Fattura</option>
                                    <option value="D">Documento</option>
                                    <option value="A">Nota di accredito</option>
                                </select>
                            </div> 
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <b>N° documento: </b><input type="text" id="n_doc_m" class="form-control">
                            </div> 
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <b>Data documento: </b><input type="date" id="data_doc_m" class="form-control">
                            </div> 
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                            <b>Prodotto:</b>
                            {!! Form::select('prodotto_doc_m',$products, null, ['class' => 'select2 ucc', 'data-fouc', 'style' => 'width:100%']) !!}

                            </div> 
                        </div>

                        <br>
                        <div class="alert alert-info alert-info-add-doc" style="display:none">
                            <strong>Attenzione!</strong> Documento non trovato!
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="closemodalAddDocs">Annulla</button>
                        <button type="button" class="btn btn-success" id="btnAddDocs">Inserisci</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="modalOperazione" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Scegli operazione da effettuare</h5>
                        <h5 id="sel_totale" hidden></h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <h5>Vuoi fatturare tutto in una sola fattura o associare ad un documento già esistente?</h5> 
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="closemodalOperazione">Annulla</button>
                        <button type="button" class="btn btn-primary" onclick="fatturare()">Fattura</button>
                        <button type="button" class="btn btn-success" onclick="openModalDocs()">Aggiungi a Documento</button>
                    </div>
                </div>
            </div>
        </div>




@stop

@section('scripts')
<script>
    var listaSedi = <?php echo json_encode(Areaseb\Core\Models\Company::sedi($company->id)); ?>;
    
    $('.btn-update-sede-modal').on('click', function(){
        let dataId = $(this).attr("data-id");
       
        var obj;
        for (var key in listaSedi) {
            console.log(listaSedi[key]);
            if(listaSedi[key].id == dataId)
                obj = listaSedi[key];
        }

        console.log(obj.id);

        $('#sede_id').val(obj.id);
        $('#nome_update').val(obj.nome);
        $('#indirizzo_update').val(obj.indirizzo);
        $('#cap_update').val(obj.cap);
        $('#citta_update').val(obj.citta);
        $('#provincia_update').val(obj.provincia);
        $('#paese_update').val(obj.paese);
        $('#telefono_update').val(obj.telefono);

        $('#update-sede-modal').modal();
    });


    $('.btn-add-sede-modal').on('click', function(){
        $('#add-sede-modal').modal();
    });


    
    $('.nav-pills li a').on('click', function(){
        console.log($(this).attr('href'));
    });
    let currentUrl = window.location.href;
    if(currentUrl.includes('#'))
    {
        let arr = currentUrl.split('#');
		if(arr[1] != '')		
        	$('a[href="#'+arr[1]+'"]').click();
    }
    
    
    // fatturazione ore e collettivi aperti
    $('select[name="prodotto_doc"]').select2({width: '100%'});
    $('select[name="prodotto_doc_m"]').select2({width: '100%'});

    function clearFieldAddDoc(){
        $('#tipo_doc_m').val('');
        $('#n_doc_m').val('');
        $('#data_doc_m').val('');
        $('select[name="prodotto_doc_m"]').val('')

        $('#tipo_doc').val('');
        $('#n_doc').val('');
        $('#data_doc').val('');
        $('select[name="prodotto_doc"]').val('')
    }
    
    function openModalDoc(sede_id, ora_id, sede_lbl, invoice_id = null){
        $('#ccID').val(sede_id)
        $('#oracID').val(ora_id)
        $('#invoiceID').val(invoice_id)
        $('#lbl_cc').text(sede_lbl)
        clearFieldAddDoc();
        $('#modalAddDoc').modal('toggle');
    }

    function openModalDocs(){
        clearFieldAddDoc();
        $('#modalOperazione').modal('toggle');
        $('#modalAddDocs').modal('toggle');
    }


    $("#closemodalAddDoc").click(function() {
        $('#modalAddDoc').modal('toggle');
    });

    $("#closemodalAddDocs").click(function() {
	    $('#modalAddDocs').modal('toggle');
    });

    $("#closemodalOperazione").click(function() {
        $('#modalOperazione').modal('toggle');
    });


    $( "#btnAddDoc" ).click(function() {
            var ora_id = $('#oracID').val()
            var invoice_id = $('#invoiceID').val()
            jQuery.ajax('/planning/add-document-ora',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "tipo_doc": $('#tipo_doc').val(),
                    "n_doc": $('#n_doc').val(),
                    "data_doc": $('#data_doc').val(),
                    "prodotto_doc":$('select[name="prodotto_doc"]').val(),
                    "ora_id":ora_id,
                    "invoice_id":invoice_id
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        if(result.data != null){
                            //var tab = $('.tab-content .active').attr('id');
                            //sessionStorage.setItem("tab-customer", tab);
                            location.href = '/invoices/'+result.data+'/edit';
                            //location.reload();
                        }
                        else
                            $('.alert-info-add-doc').show();
                    }     
                }
            });
    })

    $( "#btnAddDocs" ).click(function() {
        var all = $('#sel_totale').val();
        var arrStr = "";
        var arrInvoices = '';
        var cb = $('.cbOp');

        if(all == 1){
            $.each(cb, function() {
                arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");
				arrInvoices = arrInvoices != '' ? arrInvoices + ',' + $(this).attr("data-invoiceid") : $(this).attr("data-invoiceid");
            }); 
        }
        else{
            $.each(cb, function() {
                var $this = $(this);
                if($this.is(":checked")) {
                    arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");
					arrInvoices = arrInvoices != '' ? arrInvoices + ',' + $(this).attr("data-invoiceid") : $(this).attr("data-invoiceid");
                }
            }); 
        }		
        
        jQuery.ajax('/planning/add-documents-item',
        {
            method: 'POST',
            data: {
                "_token": '{{ csrf_token() }}',
                "tipo_doc": $('#tipo_doc_m').val(),
                "n_doc": $('#n_doc_m').val(),
                "data_doc": $('#data_doc_m').val(),
                "prodotto_doc":$('select[name="prodotto_doc_m"]').val(),
                "ids_ore": arrStr,
				'ids_invoices': arrInvoices,
            },

            complete: function (resp) {
                $('.alert').hide();
                var result = JSON.parse(resp.responseText);
                if(result.code){
                    if(result.data != null){
                        //var tab = $('.tab-content .active').attr('id');
                        //sessionStorage.setItem("tab-customer", tab);
                    	location.href = '/invoices/'+result.data+'/edit';
                        //location.reload();
                    }
                    else
                        $('.alert-info-add-doc').show();
                }     
            }
        });
    })


    function scegliOperazione(all){
        var arrStr = "";
        var arrOre = '';
        var arrInvo = '';
        var cb = $('.cbOp');

        if(all == 1){
            $.each(cb, function() {
                arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("id") : $(this).attr("id");   
                arrOre = arrOre != '' ? arrOre + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");  
                arrInvo = arrInvo != '' ? arrInvo + ',' + $(this).attr("data-invoiceid") : $(this).attr("data-invoiceid");  
            }); 
        }
        else{
            $.each(cb, function() {
                var $this = $(this);
                if($this.is(":checked")) {
                    arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("id") : $(this).attr("id");
                    arrOre = arrOre != '' ? arrOre + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");  
                	arrInvo = arrInvo != '' ? arrInvo + ',' + $(this).attr("data-invoiceid") : $(this).attr("data-invoiceid"); 
                }
            }); 
        }

        console.log('array: ', arrOre);
/*        if(arrStr == ''){
            alert('Per proseguire devi selezionare almeno un Elemento nella tabella!')
            return;
        }*/
        if(arrOre == ''){
            alert('Per proseguire devi selezionare almeno un Elemento nella tabella!')
            return;
        }
        $('#sel_totale').val(all);
        $('#modalOperazione').modal('toggle');
    }




    function fatturare(){
        var all = $('#sel_totale').val();
        var arrStr = "";
        var arrOre = '';
        var arrInvo = '';
        var cb = $('.cbOp');

        if(all == 1){
            $.each(cb, function() {
                arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("id") : $(this).attr("id");   
                arrOre = arrOre != '' ? arrOre + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");  
                arrInvo = arrInvo != '' ? arrInvo + ',' + $(this).attr("data-invoiceid") : $(this).attr("data-invoiceid");              
            }); 
        }
        else{
            $.each(cb, function() {
                var $this = $(this);
                if($this.is(":checked")) {
                    arrStr = arrStr != '' ? arrStr + ',' + $(this).attr("id") : $(this).attr("id");
                    arrOre = arrOre != '' ? arrOre + ',' + $(this).attr("data-oraid") : $(this).attr("data-oraid");  
                	arrInvo = arrInvo != '' ? arrInvo + ',' + $(this).attr("data-invoiceid") : $(this).attr("data-invoiceid");  
                }
            }); 
        }
		
        console.log('array: ', arrOre);
/*        if(arrStr == ''){
            alert('Per proseguire devi selezionare almeno un Elemento nella tabella!')
            return;
        }*/
        if(arrOre == ''){
            alert('Per proseguire devi selezionare almeno un Elemento nella tabella!')
            return;
        }

        jQuery.ajax('/ore_aperte/fatturare',
        {
            method: 'POST',
            data: {
                "_token": '{{ csrf_token() }}',
                "ids_invoice": arrInvo,
                "ids_ore": arrOre,
                "ids_item": arrStr,
            },

            complete: function (resp) {
                var result = JSON.parse(resp.responseText);
                if(result.code){
                    
                    var tab = $('.tab-content .active').attr('id');
                    console.log(tab);
                    sessionStorage.setItem("tab-customer",tab);
                    location.href = '/invoices/'+result.id+'/edit';
                    //location.reload();
                }     
            }
        });
    }

</script>
@stop
