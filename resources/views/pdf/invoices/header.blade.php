<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>header</title>
    <link rel="stylesheet" href="{{asset('public/css/b3.css')}}">
    <link rel="stylesheet" href="{{asset('public/css/pdf.css')}}">
	<style>
        
		#blue {
			background-color: #006b9c;
			color:#fff;
			text-align: center;
			padding:10px;
			font-size: 13px;
		}
		
		header {
			height: 4.2cm !important;
		}

    </style>
</head>
<body>

@php
	$base = Areaseb\Core\Models\Setting::base();
@endphp

<header>
    <div class="row mt-4" id="blue">
        <b>{{$base->rag_soc}}</b>
        <br>
        <p>
        {{$base->indirizzo}} - {{$base->cap}} {{$base->citta}} ({{$base->provincia}})
        <br>
        P.IVA / C.F.: {{$base->piva}} / {{$base->cod_fiscale}}
        <br>
        {{$base->banca}} - IBAN: {{$base->IBAN}}
        <br>
        Telefono {{$base->telefono}} - {{$base->email}} - {{$base->sitoweb}}</p>
    </div>
    
</header>

</body>
</html>
