@extends('areaseb::layouts.app')


@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}calendars">Calendari</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Collega Calendari'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">I tuoi calendari</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Privato</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($calendars as $calendar)
                                @php
                                    $isAdmin = null;
                                    if( ($calendar->user->id == 1) && ($calendar->nome == 'global'))
                                    {
                                        $isAdmin = '(Admin) ';
                                    }
                                    $nome = $isAdmin.strtolower($calendar->user->contact->nome);
                                    $cognome = strtolower($calendar->user->contact->cognome);
                                    // $name = $calendar->ics_name;
                                    $link = asset('storage/calendars/'.$calendar->ics_name.'.ics');
                                    if(is_null($calendar->token))
                                    {
                                        $calendar->update(['token' => \Str::random(33)]);
                                    }
                                @endphp
                                <tr id="row-{{$calendar->id}}" data-model="{{$calendar->class}}" data-id="{{$calendar->id}}">
                                    <td class="editable" data-field="nome">{{$calendar->nome}} {{ucfirst($nome)}} {{ucfirst($cognome)}}</td>
                                    <td>@if($calendar->privato) Sì @else No @endif</td>
                                    <td class="text-center">
                                        <a href="{{$link}}" target="_BLANK">{{$link}}</a><br>
                                        {{ url('addevent/'.$calendar->token) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Collega Calendari</h3>
                </div>
                <div class="card-body">
                    <p class="mb-0">Collegare il calendario del CRM al proprio cellulare è un'operazione molto semplice. Dividiamo però la casistica in due procedure, in base al fatto che il telefono a disposizione sia di tipo Android oppure iPhone.<br>
                        Al termine dell'operazione gli impegni / appuntamenti che avete inserito nel CRM saranno visibili anche nel vostro telefono e vi avviseranno tramite la solita suoneria 10 minuti prima che l'impegno / appuntamento abbia luogo.</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Android</h3>
                </div>
                <div class="card-body">
                    <p class="mb-0">Se si possiede un telefono Android è necessario installare un'apposita applicazione per poter collegare il calendario del CRM a quello del telefono. L'applicazione si chiama ICSx5 ed è scaricabile dal seguente link (il nome utente e la password richiesti sono riportati alla fine di questa pagina):</p>
                    <a href="https://play.google.com/store/apps/details?id=at.bitfire.icsdroid">ICSx5 – sync Webcal & .ics calendars</a>
                    <br>
                    <p>Si consiglia di scaricare il file direttamente con il telefono, in questo modo partirà automaticamente l'installazione dell'app.
                        Una volta scaricata ed installata l'applicazione, avviatela e collegate il calendario o i calendari (è possibile collegarne più di uno) toccando il pallino rosso che trovate in basso a destra ed inserendo l'indirizzo web del calendario (gli indirizzi sono riportati di seguito).<br>
                        E' necessario fornire anche un nome utente e password per poter avere accesso ai calendari, dunque attivate 'Requires authentication' e inserite il nome utente e password che trovate a fondo pagina. Toccate ora la freccia in alto a destra, nella schermata successiva scegliete un nome e un colore per il vostro calendario e convalidate il tutto toccando la spunta in alto a destra.<br>
                        Impostate la frequenza di sincronizzazione che preferite (consigliato 15 minuti) toccando i 3 pallini in alto adestra e scegliendo 'Set sync interval'.<br>
                        Fatto ! Ora i vostri appuntamenti appariranno anche nel vostro telefono e vi avviseranno tramite la solita suoneria 10 minuti prima che l'impegno / appuntamento abbia luogo.</p>
                </div>
            </div>
        </div>


        <div class="col-12 col-sm-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">iPhone</h3>
                </div>
                <div class="card-body">

                    <p>iPhone non necessita di applicazioni terze per collegare un calendario web, è sufficiente entrare nelle Impostazioni del telefono e seguire i seguenti passaggi:</p>
                <ol>
                	<li>nelle Impostazioni cercare ed entrare in Account e password</li>
                	<li>scendere verso il basso e toccare su Aggiungi account</li>
                	<li>dalla lista che esce, scegliere Altro</li>
                	<li>dalla lista proposta, scegliere Aggiungi calendario</li>
                	<li>compilare il Server con uno degli indirizzi sopra proposti (scriverli per intero) e premere Avanti</li>
                	<li>comparirà un riquadro informativo che vi dirà "Impossibile verificare informazioni account". Premete OK</li>
                	{{-- <li>nella schermata che vi si propone compilare Nome utente e Password con i dati sotto riportati</li> --}}
                	<li>premete Avanti (in alto a destra)</li>
                	<li>premete Salva</li>
                </ol>
                </div>
            </div>
        </div>


    </div>
@stop
