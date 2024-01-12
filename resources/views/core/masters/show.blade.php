@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}masters">Maestro</a></li>
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
					<div class="row">
                        <div class="col-6">
                            <strong><i class="fas fa-map-marker-alt mr-1"></i> Lista Sedi</strong>
                        </div>
                        <div class="col-6">
                            <a href="#" data-title="Aggiungi Sede" class="btn btn-primary btn-block btn-add-sede-modal"> Aggiungi</a>
                        </div>
                    </div>
                    <br>
                    @foreach(Areaseb\Core\Models\Company::sedi($company->id) as $sede)
                        <div class="row">
                            <div class="col-6">
                                <p class="text-muted">{{$sede->indirizzo}} <br>
                                    {{$sede->cap}}, {{$sede->citta}} ({{$sede->provincia}}) {{$sede->paese}}
                                </p>
                            </div>
                            <div class="col-6">
                                {!! Form::open(['method' => 'delete', 'url' => 'companies-sede/'.$sede->id, 'id' => "form-".$sede->id]) !!}
                                    <a class="btn btn-sm btn-primary btn-update-sede-modal" data-id="{{$sede->id}}" href="#"><i class="fas fa-edit"></i></a>
                                    <button type="submit" id="{{$sede->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                    
                                {!! Form::close() !!}
                            </div>
                        </div>
                        <hr>
                    @endforeach
                    <strong><i class="fas fa-euro-sign mr-1"></i> Fatturazione</strong>
                    @if($company->pec)<p class="text-muted"><b>PEC:</b> {{$company->pec}}</p>@endif
                    @if($company->piva)<p class="text-muted"><b>P.IVA:</b> {{$company->piva}}</p>@endif
                    @if($company->cf)<p class="text-muted"><b>CF:</b> {{$company->cf}}</p>@endif
                    @if($company->sdi)<p class="text-muted"><b>SDI:</b> {{$company->sdi}}</p>@endif
                    <hr>
                    <strong><i class="fas fa-at mr-1"></i> Contatti</strong>
                    @if($company->phone)<p class="text-muted"><b>Tel:</b> {{$company->phone}}</p>@endif
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
                        @if($company->invoices()->exists())
                            @can('invoices.read')
                                <li class="nav-item"><a class="nav-link" href="#fatture" data-toggle="tab">Fatture</a></li>
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
                            @can('products.read')
                                @include('areaseb::core.masters.components.contacts')
                            @endcan
                        </div>
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
    

</script>
@stop
