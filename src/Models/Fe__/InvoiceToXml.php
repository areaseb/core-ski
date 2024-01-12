<?php

namespace Areaseb\Core\Models\Fe;

use \Log;
use \File;
use \Exception;
use \Storage;
use \SimpleXMLElement;
use Areaseb\Core\Models\Fe\{Primitive, Xml};
use Areaseb\Core\Models\{Company, Invoice, Item, Exemption};

class InvoiceToXml extends Primitive
{

    public function __construct(Invoice $invoice, $setting)
    {
        $this->invoice = $invoice;
        $this->client = $invoice->company;
        $this->items = $invoice->items;
        $this->cedente = $setting;
        $this->types = config('fe.types');
        $this->payment_methods = config('fe.payment_methods');
        $this->xml = new Xml($setting);
    }

    public function init()
    {
        $template = $this->xml->createXml();
        $header = $template->FatturaElettronicaHeader;
            $this->datiTrasmissione($header);
            $this->datiCedente($header);
            $this->datiCommittente($header);

        $body = $template->FatturaElettronicaBody;
            $this->datiGeneraliDocumento($body);
            if($this->invoice->tipo_doc == 'Pu')
            {
                $this->datiPu($body);
            }
            $this->datiBeniServizi($body);
            $this->datiPagamento($body);
// return $template;
        return $this->xml->saveXml($template, 'inviate', $this->invoice);

    }

    public function datiPu($body)
    {
        $DatiGenerali = $body->DatiGenerali;
        $DatiOrdineAcquisto = $DatiGenerali->addChild('DatiOrdineAcquisto');
        $DatiOrdineAcquisto->addChild('IdDocumento', $this->invoice->pa_n_doc);
        $DatiOrdineAcquisto->addChild('NumItem', 1);
        $DatiOrdineAcquisto->addChild('CodiceCUP', $this->invoice->pa_cup);
        $DatiOrdineAcquisto->addChild('CodiceCIG', $this->invoice->pa_cig);
        $DatiOrdineAcquisto->addChild('Data', $this->invoice->pa_data_doc->format('Y-m-d'));

    }

    public function datiTrasmissione($header)
    {
        $DatiTrasmissione = $header->DatiTrasmissione;
        $DatiTrasmissione->ProgressivoInvio = $this->invoice->id;
        if($this->invoice->company->private)
        {
            $DatiTrasmissione->CodiceDestinatario = ($this->invoice->company->nation == 'IT') ? '0000000' : 'XXXXXXX';

            if($this->invoice->company->pec)
            {
            	$DatiTrasmissione->PECDestinatario = $this->invoice->company->pec;
            }
        }
        else
        {
            $DatiTrasmissione->CodiceDestinatario = $this->invoice->company->sdi;

            if($this->invoice->company->sdi == '0000000' && $this->invoice->company->pec)
            {
            	$DatiTrasmissione->PECDestinatario = $this->invoice->company->pec;
            }
        }
        if($this->invoice->tipo_doc == 'Pu')
        {
            $DatiTrasmissione->FormatoTrasmissione = 'FPA12';
        }

        return true;
    }

    public function datiCedente($header)
    {
        $DatiAnagrafici = $header->CedentePrestatore->DatiAnagrafici;
        $Sede = $header->CedentePrestatore->Sede;

        $DatiAnagrafici->IdFiscaleIVA->IdPaese = $this->cedente->nazione;
        $DatiAnagrafici->IdFiscaleIVA->IdCodice = $this->cedente->piva;
        $DatiAnagrafici->CodiceFiscale = $this->cedente->piva;
        $DatiAnagrafici->Anagrafica->Denominazione = $this->cedente->rag_soc;
        $DatiAnagrafici->RegimeFiscale = $this->cedente->regime;

        $Sede->Indirizzo = $this->cedente->indirizzo;
        $Sede->CAP = $this->cedente->cap;
        $Sede->Comune = $this->cedente->citta;
        $Sede->Provincia = $this->cedente->prov;
        $Sede->Nazione = $this->cedente->nazione;

        return true;
    }


    public function datiCommittente($header)
    {
        $DatiAnagrafici = $header->CessionarioCommittente->DatiAnagrafici;
        $Sede = $header->CessionarioCommittente->Sede;

        if($this->client->piva != "")
        {
            if(!$this->client->private)
            {
                $IdFiscaleIVA = $DatiAnagrafici->addChild('IdFiscaleIVA');
                $IdFiscaleIVA->addChild('IdPaese', $this->client->nation);
                $IdFiscaleIVA->addChild('IdCodice', $this->client->clean_piva);
            }
        }

        if($this->client->private)
        {
            $DatiAnagrafici->addChild('CodiceFiscale', $this->client->cf);
        }

        $Anagrafica = $DatiAnagrafici->addChild('Anagrafica');
        $Anagrafica->addChild('Denominazione', $this->client->rag_soc);

        $Sede->Indirizzo = $this->client->address;
        $Sede->CAP = $this->client->zip;
        $Sede->Comune = $this->client->city;
        if ($this->client->nazione == 'IT')
        {
            $Sede->addChild('Provincia', $this->client->prov);
        }
        $Sede->addChild('Nazione', $this->client->nation);
    }

    public function datiGeneraliDocumento($body)
    {
        $DatiGeneraliDocumento = $body->DatiGenerali->DatiGeneraliDocumento;

        if (!array_key_exists($this->invoice->tipo, $this->types))
        {
            $this->notify($this->invoice, "SEND: datiGeneraliDocumento(): tipo documento non gestito: ".$this->invoice->tipo);
            return false;
        }

        $DatiGeneraliDocumento->TipoDocumento = $this->types[$this->invoice->tipo];
        $DatiGeneraliDocumento->Data = $this->invoice->data->format('Y-m-d');

        if(intval($this->invoice->numero))
        {
            if($this->invoice->tipo_doc == 'Pu')
            {
                $DatiGeneraliDocumento->Numero = 'FPA '.$this->invoice->numero.'/'.$this->invoice->data->format('y');
            }
            else
            {
                $DatiGeneraliDocumento->Numero = 'FPR '.$this->invoice->numero.'/'.$this->invoice->data->format('y');
            }

        }
        else
        {
            $DatiGeneraliDocumento->Numero = $this->invoice->numero;
        }


        if( ($this->invoice->bollo > 0) )
        {
            $linea = $DatiGeneraliDocumento->addChild('DatiBollo');
            $linea->addChild('BolloVirtuale', "SI");
            $linea->addChild('ImportoBollo', $this->decimal($this->invoice->bollo));
        }

        if( ($this->invoice->ritenuta > 0) )
        {
            $linea = $DatiGeneraliDocumento->addChild('DatiRitenuta');
            $linea->addChild('TipoRitenuta', "RT01");
            $linea->addChild('ImportoRitenuta', $this->decimal(($this->invoice->imponibile - $this->invoice->bollo) * ($this->invoice->ritenuta / 100)));
            $linea->addChild('AliquotaRitenuta', $this->decimal($this->invoice->ritenuta));
            $linea->addChild('CausalePagamento', 'A');
        }

        if($this->invoice->split_payment)
        {
            $DatiGeneraliDocumento->ImportoTotaleDocumento = $this->decimal($this->invoice->imponibile);
        }
        else
        {
            $DatiGeneraliDocumento->ImportoTotaleDocumento = $this->decimal($this->invoice->total);
        }


        if(!is_null($this->invoice->rounding))
        {
            $DatiGeneraliDocumento->addChild('Arrotondamento', $this->invoice->rounding);
        }

    }


    public function datiBeniServizi($body)
    {
        $DatiBeniServizi = $body->DatiBeniServizi;

        foreach($this->items as $n => $item)
        {
            $descrizione = $item->descrizione;
            if(is_null($item->descrizione))
            {
                $descrizione =  $item->product->nome;
            }
            if($item->descrizione =='')
            {
                $descrizione = $item->product->nome;
            }

            $descrizione = $this->cleanDescription($descrizione);

            $linea = $DatiBeniServizi->addChild('DettaglioLinee');
            $linea->addChild('NumeroLinea', ($n+1));
            $linea->addChild('Descrizione', $descrizione);
            $linea->addChild('Quantita', $this->decimal($item->qta));
            $linea->addChild('PrezzoUnitario', $this->decimal($item->importo));

            if ($item->sconto > 0)
            {
                $scmag = $linea->addChild('ScontoMaggiorazione');
                $scmag->addChild('Tipo', "SC");
                $scmag->addChild('Percentuale', $this->decimal($item->sconto));
            }

            $linea->addChild('PrezzoTotale', $this->decimal($item->totale_riga));
            $linea->addChild('AliquotaIVA', $this->decimal($item->perc_iva));
            if (!is_null($item->exemption_id))
            {
                if($item->exemption_id > 0)
                {
                    $linea->addChild('Natura', $item->exemption->codice);
                }
            }

            if( $this->invoice->ritenuta > 0 )
            {
                $linea->addChild('Ritenuta', 'SI');
            }

        }

        foreach ($this->invoice->items_grouped_by_ex as $n => $group)
        {
            $linea = $DatiBeniServizi->addChild('DatiRiepilogo');


            if($this->invoice->split_payment)
            {
                $linea->addChild('AliquotaIVA', $this->decimal($group->perc_iva));
                $linea->addChild('ImponibileImporto', $this->decimal($group->imponibile));
                $linea->addChild('Imposta', $this->decimal($group->iva));
                $linea->addChild('EsigibilitaIVA', "S");
                $linea->addChild('RiferimentoNormativo', "Scissione dei pagamenti art. 17 ter DPR 633/72");
            }
            else
            {
                if(is_null($group->exemption_id))
                {
                    $linea->addChild('AliquotaIVA', $this->decimal($group->perc_iva));
                    $linea->addChild('ImponibileImporto', $this->decimal($group->imponibile));
                    $linea->addChild('Imposta', $this->decimal($group->iva));
                    $linea->addChild('EsigibilitaIVA', "I");
                }
                else
                {
                    $linea->addChild('AliquotaIVA', $this->decimal($group->perc_iva));
                    $linea->addChild('Natura', $group->natura);
                    $linea->addChild('ImponibileImporto', $this->decimal($group->imponibile));
                    $linea->addChild('Imposta', $this->decimal($group->iva));
                    $linea->addChild('RiferimentoNormativo', $group->riferimento_normativo);
                }
            }

        }
    }


    public function datiPagamento($body)
    {
        $DatiPagamento = $body->DatiPagamento;
        if($this->invoice->rate)
        {
            $rate = explode(',', $this->invoice->rate);
            $n_rate = count($rate);

            if($this->invoice->split_payment)
            {
                $total = $this->decimal($this->invoice->imponibile);
            }
            else
            {
                $total = $this->decimal($this->invoice->total);
            }

            $amount_rata = $this->decimal($total / $n_rate);
            $amount_payed = 0;

            $DatiPagamento->CondizioniPagamento = 'TP01';
            for($nr=0;$nr<$n_rate;$nr++)
            {
                $scadenza_rata = \Carbon\Carbon::createFromFormat('d/m/Y', trim($rate[$nr]))->format('Y-m-d');

                $linea = $DatiPagamento->addChild('DettaglioPagamento');
                $linea->addChild('ModalitaPagamento', $this->payment_methods[$this->invoice->pagamento]);
                $linea->addChild('DataScadenzaPagamento', $scadenza_rata);

                if($nr == ($n_rate -1))
                {
                    $linea->addChild('ImportoPagamento', $this->decimal( $total - $amount_payed));
                }
                else
                {
                    $linea->addChild('ImportoPagamento', $amount_rata);
                }
                $amount_payed +=  $amount_rata;
            }
        }
        else
        {
            $DatiPagamento->CondizioniPagamento = 'TP02';
            $linea = $DatiPagamento->addChild('DettaglioPagamento');
            $linea->addChild('ModalitaPagamento', $this->payment_methods[$this->invoice->pagamento]);
            $linea->addChild('DataScadenzaPagamento', $this->invoice->data_scadenza->format('Y-m-d'));

            if($this->invoice->split_payment)
            {
                $linea->addChild('ImportoPagamento', $this->decimal($this->invoice->imponibile));
            }
            else
            {
                $linea->addChild('ImportoPagamento', $this->decimal($this->invoice->total+$this->invoice->rounding));
            }


            if ($this->cedente->IBAN != '')
            {
                $linea->addChild('IBAN', str_replace(" ", "", $this->cedente->IBAN));
            }
        }
    }



    public function cleanDescription($str)
    {
        $str = str_replace('€', 'EUR', $str);
        $str = str_replace('£', 'GBP', $str);
        $str = str_replace('$', 'USD', $str);
        $str = str_replace('©',' Copyright', $str);
        $str = str_replace('®', ' Registered', $str);
        $str = str_replace('™',' Trademark', $str);
        $str = str_replace('&',' e ', $str);
        $str = str_replace('&',' e ', $str);
        $str = str_replace('’', "'", $str);
        return $str;
    }




}
