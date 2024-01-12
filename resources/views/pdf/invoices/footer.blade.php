<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>footer</title>
    <link rel="stylesheet" href="{{asset('public/css/b3.css')}}">
    <link rel="stylesheet" href="{{asset('public/css/pdf.css')}}">
	<style>
		
		footer {
            font-family: Helvetica;
            font-size: 14pt;
            line-height:24px;
            color: #006b9c;
            margin-top: 0;
            margin-bottom: 0;
		}
		
	</style>
</head>
<body>

@php
	$base = Areaseb\Core\Models\Setting::base();
@endphp

<footer>
    <div class="row">
        <div class="col-md-12" style="width:100%;color:red; text-align:center;font-size: 13pt;">
            <b>FATTURA NON VALIDA AI FINI FISCALI AI SENSI DELL'ART. 21 DPR 933/72<br>
                L'ORIGINALE E' DISPONIBILE AL VOSTRO SDI OPPURE NELL'AREA A VOI RISERVATA DELL'AGENZIA DELLE ENTRATE.
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
</footer>
</body>
</html>
