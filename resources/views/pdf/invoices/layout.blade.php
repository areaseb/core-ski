<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$title}}</title>
    <link rel="stylesheet" href="{{asset('css/adminlte.css')}}">
    <link rel="stylesheet" href="{{asset('css/style.css')}}">
    <link rel="stylesheet" href="{{asset('css/pdf/b3.css')}}">
    <link rel="stylesheet" href="{{asset('css/pdf/pdf.css')}}">
    <style>
    	
        @page {
            margin: 0;
        }

        body {
            margin-top: 0cm;
            margin-left: 0cm;
            margin-right: 0cm;
            margin-bottom: 0cm;
            font-family: Helvetica;
            font-size: 13px;
            color: #006b9c;
        }
        
        .corpo {
        	padding-top: 0.5cm !important;
        	display: block !important;
        }
        
        th {
        	font-size: 13px;
        	font-weight: bold;
        }
        
        td {
        	font-size: 13px;
        }
		
		/*header {
            height: 3.7cm;
            padding-top: 4cm !important;
        }*/
        
		#blue {
			background-color: #006b9c;
			color:#fff;
			text-align: center;
			padding: 30px 10px 10px 10px;
		}
        
        footer {
        	display: block;
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 3cm;
            font-size: 11px;
        }

    </style>
</head>

<body>

{{--<footer>
	<div class="row">
        <div class="col-md-12" style="width:100%;color:red; text-align:center;">
            <b>FATTURA NON VALIDA AI FINI FISCALI AI SENSI DELL'ART. 21 DPR 933/72<br>
                L'ORIGINALE E' DISPONIBILE ALL'INDIRIZZO DA VOI FORNITO OPPURE NELL'AREA A VOI RISERVATA DELL'AGENZIA DELLE ENTRATE.
            </b>
        </div>
        
        @if(Areaseb\Core\Models\Setting::FatturaFooterImg() != '')
            <img class="img-responsive" src="{{Areaseb\Core\Models\Setting::FatturaFooterImg()}}">
        @endif
	</div>
	<div class="row">
        <div class="col-md-6 text-left" style="width: 50%; text-align: left; float: left;">
            <p style="margin:10px 0 0 20px;"><b>{{$base->rag_soc}}</b><br>
                {{$base->indirizzo}}<br>
                {{$base->cap}} {{$base->citta}} ({{$base->provincia}})<br>
                Telefono {{$base->telefono}}
            </p>
        </div>
        <div class="col-md-6 text-right" style="width: 50%; text-align: right; float: right;">
            <p style="margin:10px 20px 0 0;">{{$base->banca}}<br>
            {{$base->IBAN}}<br>
            PIVA {{$base->piva}}<br>
            {{$base->email}} - {{$base->sitoweb}}</p>
        </div>
    </div>	
</footer>--}}


@yield('content')



</body>
</html>
