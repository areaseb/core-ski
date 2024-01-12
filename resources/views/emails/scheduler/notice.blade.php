@component('mail::message')
# Fattura di cortesia

Gentile {{$invoice->company->rag_soc}},<br>
da un controllo contabile ci risulta che la<br><br>

<b>FATTURA n.{{$invoice->numero}} del {{$invoice->data->format('d/m/Y')}}, per un totale di {{$invoice->total_formatted}} - scadenza {{$invoice->data_scadenza->format('d/m/Y')}}</b><br><br>
alla data odierna non risulta ancora da Voi pagata.<br>Vi preghiamo pertanto di regolarizzare al più presto la Vostra posizione contabile.
Nel caso in cui aveste già provveduto al saldo dei sospesi in oggetto, Vi invitiamo ad inviarci la contabile di pagamento al fine di evitare ulteriori solleciti da parte nostra.<br><br>
Il pagamento potrà essere effettuato mediante:<br><br>


@php
    $setting = \Areaseb\Core\Models\Setting::Base();
@endphp

<div class="table" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box;">
	<table style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; margin: 30px auto; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
		<tbody style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box;">
			<tr>
				<td style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; margin: 0;">Banca</td>
				<td align="right" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; margin: 0;"><b>{{$setting->banca}}</b></td>
			</tr>
			<tr>
				<td style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; margin: 0;">Intestazione</td>
				<td align="right" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; margin: 0;"><b>{{$setting->rag_soc}}</b></td>
			</tr>
            <tr>
                <td style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; margin: 0;">IBAN</td>
                <td align="right" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; margin: 0;"><b>{{$setting->IBAN}}</b></td>
            </tr>
            <tr>
                <td style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; margin: 0;">Importo</td>
                <td align="right" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; margin: 0;"><b>{{$invoice->total_formatted}}</b></td>
            </tr>
            <tr>
                <td style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; margin: 0;">Causale</td>
                <td align="right" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 18px; padding: 10px 0; margin: 0;"><b>FATTURA n.{{$invoice->numero}} del {{$invoice->data->format('d/m/Y')}}</b></td>
            </tr>
		</tbody>
	</table>
</div>

Rimanendo a disposizione per ogni eventuale necessità o chiarimento, Vi ringraziamo per l'attenzione e inviamo distinti saluti.

Grazie,<br>
{{ config('app.name') }}
@endcomponent
