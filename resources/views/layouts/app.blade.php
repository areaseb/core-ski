<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="baseURL" content="{{config('app.url')}}">
    <meta name="iva" content="{{config('app.iva')}}">
    <meta name="token" content="{{csrf_token()}}">

    @yield('meta_title')

    <link rel="stylesheet" href="{{asset('plugins/fontawesome-free/css/all.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/ionic/ionicons.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/daterangepicker/daterangepicker.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/noty/noty.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/adminlte.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/style.css')}}">
    <link rel="stylesheet" href="{{asset('css/colors.css')}}">

    @yield('css')

</head>
<body class="hold-transition sidebar-mini layout-fixed">
	
	@php
		$base = Areaseb\Core\Models\Setting::base();
	@endphp

	<header class="print">
	    <div class="row" id="blue">
	    	<p style="width: 100%;">
		    	<b style="display: block;">{{$base->rag_soc}}</b>
		        {{$base->indirizzo}} - {{$base->cap}} {{$base->citta}} ({{$base->provincia}})
		        <br>
		        P.IVA / C.F.: {{$base->piva}} / {{$base->cod_fiscale}}
		        <br>
		        {{$base->banca}} - IBAN: {{$base->IBAN}}
		        <br>
		        Telefono {{$base->telefono}} - {{$base->email}} - {{$base->sitoweb}}
		    </p>
	    </div>
	    
	</header>
	
    <div class="wrapper">
        @auth
            @include('areaseb::layouts.elements.top-nav')
            @include('areaseb::layouts.elements.side-nav')
        @endauth

        <div class="content-wrapper">

        @php
            $url = explode('/',Illuminate\Support\Facades\URL::current());
            
            if(isset($url[3])){
            	$currenturl = $url[3];
            	if($currenturl == 'events'){
            		$currenturl = 'calendars';
            	}
            	if($currenturl == 'payments'){
            		$currenturl = 'invoices';
            	}
            	if($url[3] == 'api' && $url[4] == 'companies'){
            		$currenturl = 'companies';
            	}
            } else {
            	$currenturl = '*';
            }
        @endphp
        @if($user->can($currenturl.'.read') || !isset($url[3]))
            @yield('title')

            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
            @else
            <section class="content">
                <div class="container-fluid text-center pt-5" >
                    <strong>Non hai i permessi necessari per accedere a questa sezione.</strong>
                </div>
            </section>
           
        @endif

        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 3.0
            </div>
            <strong>Copyright &copy; 2020 - @if(date('Y') != '2020') {{date('Y')}} | @endif
                <a href="https://www.areaseb.it">Areaseb srl</a>.
            </strong> Tutti i diritti riservati.
        </footer>

    </div>

<div class="modal" tabindex="-1" role="dialog" id="global-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                <button type="submit" class="btn btn-primary btn-save-modal">Salva</button>
            </div>
        </div>
    </div>
</div>

    <script src="{{asset('plugins/axios/axios.min.js')}}"></script>
    <script src="{{asset('plugins/jquery/jquery.min.js')}}"></script>
    <script src="{{asset('plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

    <script src="{{asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
    <script src="{{asset('plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
    <script src="{{asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>

    <script src="{{asset('plugins/select2/js/select2.full.min.js')}}"></script>
    <script src="{{asset('plugins/moment/moment-with-locales.min.js')}}"></script>
    <script src="{{asset('plugins/inputmask/min/jquery.inputmask.bundle.min.js')}}"></script>
    <script src="{{asset('plugins/daterangepicker/daterangepicker.js')}}"></script>
    <script src="{{asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.js')}}"></script>
    <script src="{{asset('plugins/bootstrap-switch/js/bootstrap-switch.min.js')}}"></script>
    <script src="{{asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js')}}"></script>
    <script src="{{asset('plugins/noty/noty.min.js')}}"></script>
    <script src="{{asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>
        <script src="{{asset('plugins/sweetalert2/sw2.min.js')}}"></script>

    <script src="{{asset('js/adminlte.min.js')}}"></script>
    <script src="{{asset('js/global.js')}}"></script>
    @yield('scripts')
    @stack('scripts')
    @include('areaseb::components.session')

</body>
