<html>
	<header>
        <title>{{$title}}</title>
    	{{-- <style type="text/css">
    		h3 {font-family: Arial;}
    		.strumenti {font-family: Arial;background-color: grey;overflow: auto;padding: 20px;float: left;margin-top: 20px;min-height: 200px;width: 40%;display: inline;margin-left: 0.5%;margin-right: 0.5%;clear: none;}
    		.dett_voce_fatt {border-left: solid 2px #000000;border-right: solid 2px #000000;}
    		.intestazione {background-color: #000000;}
    		table{border: 1px solid #000000;}
    	</style> --}}
    </header>
<body>

    <table width="100%" cellspacing="0" align="left">
    <tr>
    	<td width="33%" align="left" valign="top">
    	<h2>DATI CENDENTE</h2>
    		{{$xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->Anagrafica->Denominazione}}<br>
    		P.IVA: IT{{$xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->CodiceFiscale}}<br>
    		{{$xml->FatturaElettronicaHeader->CedentePrestatore->Sede->Indirizzo}}, {{$xml->FatturaElettronicaHeader->CedentePrestatore->Sede->NumeroCivico}}<br>
    		{{$xml->FatturaElettronicaHeader->CedentePrestatore->Sede->CAP}} {{$xml->FatturaElettronicaHeader->CedentePrestatore->Sede->Comune}} ({{$xml->FatturaElettronicaHeader->CedentePrestatore->Sede->Provincia}})
    	</td>
    	<td width="34%" class="testo" align="left" valign="top">
    	<h2>DATI DOCUMENTO</h2>
        	Tipo: {{$xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->TipoDocumento}}<br>
        	Numero: {{$xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero}}<br>
        	Data documento: {{\Carbon\Carbon::parse($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Data)->format('d/m/Y')}}<br>
    	</td>
    	<td width="33%" class="testo" align="left" valign="top">
    	<h2>DATI CESSIONARIO</h2>
        	{{$xml->FatturaElettronicaHeader->CessionarioCommittente->DatiAnagrafici->Anagrafica->Denominazione}}<br>
        	P.IVA: IT{{$xml->FatturaElettronicaHeader->CessionarioCommittente->DatiAnagrafici->CodiceFiscale}}<br>
        	{{$xml->FatturaElettronicaHeader->CessionarioCommittente->Sede->Indirizzo}}, {{$xml->FatturaElettronicaHeader->CessionarioCommittente->Sede->NumeroCivico}}<br>
        	{{$xml->FatturaElettronicaHeader->CessionarioCommittente->Sede->CAP}} {{$xml->FatturaElettronicaHeader->CessionarioCommittente->Sede->Comune}} ({{$xml->FatturaElettronicaHeader->CessionarioCommittente->Sede->Provincia}})<br>
        	Codice destinatario: {{$xml->FatturaElettronicaHeader->DatiTrasmissione->CodiceDestinatario}}
    	</td>
    </tr>
    </table>

    <br>
    <hr>
    <h2>PRODOTTI E SERVIZI</h2>
    <table width="100%" cellspacing="0" align="left">
        <tr style="background-color: #CCCCCC;">
            <td align="center" width="5%"><b>Nr</b></td>
            <td align="left" width="30%"><b>Descrizione</b></td>
            <td align="center" width="10%"><b>Quantit&agrave;</b></td>
            <td align="center" width="15%"><b>Prezzo</b></td>
            <td align="center" width="5%"><b>Sc/Mg</b></td>
            <td align="center" width="15%"><b>Importo</b></td>
            <td align="center" width="10%"><b>IVA</b></td>
            <td align="center" width="10%"><b>Natura IVA</b></td>
        </tr>
        @php
            $tot_imponibile = 0;
            $tot_non_imponibile = 0;
        @endphp
        @foreach ($xml->FatturaElettronicaBody->DatiBeniServizi->DettaglioLinee as $riga)

            @php
                if((string) $riga->Quantita == "")
                {
                    $quantita = 1;
                }
                else
                {
                    $quantita = (string) $riga->Quantita;
                }

                if((string) $riga->ScontoMaggiorazione == "")
                {
                    $sconto = 0;
                }
                else
                {
                    $sconto = (string) $riga->ScontoMaggiorazione;
                }

                if((string) $riga->AliquotaIVA != 0)
                {
                    $tot_imponibile += (string) $riga->PrezzoTotale;
                }
                else
                {
                    $tot_non_imponibile += (string) $riga->PrezzoTotale;
                }
            @endphp

            <tr>
                <td align="center">{{$riga->NumeroLinea}}</td>
                <td align="left">{{$riga->Descrizione}}</td>
                <td align="center">{{$quantita}}</td>
                <td align="center">{{(string) $riga->PrezzoUnitario}}</td>
                <td align="center">{{$sconto}}</td>
                <td align="center">{{(string) $riga->PrezzoTotale}}</td>
                <td align="center">{{(string) $riga->AliquotaIVA}}</td>
                <td align="center">{{$riga->Natura}}</td>
            </tr>

        @endforeach
    </table>

    <br>
    <hr>
    <br>
    <h2>DATI PAGAMENTO</h2>
		@if(isset($xml->FatturaElettronicaBody->DatiPagamento))
	        Metodo: {{$xml->FatturaElettronicaBody->DatiPagamento->DettaglioPagamento->ModalitaPagamento}}<br>
	        Pagamento: {{$xml->FatturaElettronicaBody->DatiPagamento->CondizioniPagamento}}<br>
	        Banca: {{$xml->FatturaElettronicaBody->DatiPagamento->DettaglioPagamento->IstitutoFinanziario}}<br>
	        IBAN: {{$xml->FatturaElettronicaBody->DatiPagamento->DettaglioPagamento->IBAN}}<br>
	        BIC/SWIFT: {{$xml->FatturaElettronicaBody->DatiPagamento->DettaglioPagamento->BIC}}<br>
	        Scadenza: {{$xml->FatturaElettronicaBody->DatiPagamento->DettaglioPagamento->DataScadenzaPagamento}}<br>
	        Importo: &euro; {{number_format((string) $xml->FatturaElettronicaBody->DatiPagamento->DettaglioPagamento->ImportoPagamento, 2, ',', '.')}}<br>
		@endif
	<br>
    <hr>
    <br>
    <h2>DATI AGGIUNTIVI</h2>
    Causale: {{$xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Causale}}<br>
    <br>
    <hr>
    <br>
    <table width="100%" cellspacing="0" align="left">
        <tr>
            <td width="50%" align="center" valign="top">
                <br>
                <h2>RIEPILOGO IVA</h2>
                <table width="100%" cellspacing="0" align="left">
                    <tr style="background-color: #CCCCCC;">
                        <td align="center"><b>IVA</b></td>
                        <td align="center"><b>Natura</b></td>
                        <td align="center"><b>Normativa</b></td>
                        <td align="center"><b>Esigibilit&agrave;</b></td>
                        <td align="center"><b>Imponibile</b></td>
                        <td align="center"><b>Imposta</b></td>
                    </tr>
                    @php
                        $totale_iva = 0;
                    @endphp
                    @foreach ($xml->FatturaElettronicaBody->DatiBeniServizi->DatiRiepilogo as $iva)

                        @php
                            $totale_iva += (string) $iva->Imposta;
                        @endphp

                        <tr>
                            <td align="center">{{$iva->AliquotaIVA}}</td>
                            <td align="center">{{$iva->Natura}}</td>
                            <td align="center">{{$iva->RiferimentoNormativo}}</td>
                            <td align="center">{{$iva->EsigibilitaIVA}}</td>
                            <td align="center">{{$iva->ImponibileImporto}}</td>
                            <td align="center">{{$iva->Imposta}}</td>
                        </tr>

                    @endforeach
                </table>
            </td>
            <td width="50%" align="center" valign="top">
                <h2>CALCOLO FATTURA</h2>
                    <table width="100%" cellspacing="0" align="right">
                    <tr>
                        <td width="10%" align="left" valign="top">
                            &nbsp;
                        </td>
                        <td width="45%" align="left" valign="top">
                            Importo prodotti e servizi<br>
                            Totale imponibile<br>
                            Totale non imponibile<br>
                            Totale IVA<br>
                            <b>Totale documento</b><br><br>
                            Netto a pagare
                        </td>
                        <td align="right" valign="top">
                            &euro; {{number_format(($tot_imponibile + $tot_non_imponibile), 2, ',', '.')}}<br>
                            &euro; {{number_format($tot_imponibile, 2, ',', '.')}}<br>
                            &euro; {{number_format($tot_non_imponibile, 2, ',', '.')}}<br>
                            &euro; {{number_format($totale_iva, 2, ',', '.')}}<br>
                            <b>&euro; {{number_format(($tot_imponibile + $tot_non_imponibile + $totale_iva), 2, ',', '.')}}</b><br><br>
                            &euro; {{number_format(($tot_imponibile + $tot_non_imponibile + $totale_iva), 2, ',', '.')}}<br>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
