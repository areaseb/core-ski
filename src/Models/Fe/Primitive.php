<?php

namespace Areaseb\Core\Models\Fe;

use \Carbon\Carbon;
use \Storage;
use Illuminate\Database\Eloquent\Model;

use Areaseb\Core\Models\{Cost, Exemption, Expense, Invoice, Media, Product, Setting, Item};

class Primitive extends Model
{

    public function decimal($n)
    {
        return str_replace("-","", number_format(floatval($n), 2, '.', ''));
    }

    public function dataFromXml($xml)
    {
        return Carbon::parse($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Data);
    }

    public function getCodeFromFilename($str)
    {
        if(strpos($str, '.p7m'))
        {
            $str = str_replace('.p7m', '', $str);
        }
        $arr = explode('.',$str);
        return substr($arr[0],-5,5);
    }

    /**
     * transform ARUBA numero progressivo fattura in numero per il CRM
     * @param  [string] $str
     * @return [string]
     */
    public function getCrmId($str)
    {
        $str = preg_replace("/[^0-9]/", "", $str );
        return substr($str, 0, -2);
    }

    public function getTotale($xml)
    {
        $imponibile = 0;
        $iva = 0;
        $DatiRiepilogo = $xml->FatturaElettronicaBody->DatiBeniServizi->DatiRiepilogo;
        foreach($DatiRiepilogo as $dr)
        {
            $imponibile += floatval($dr->ImponibileImporto);
            $iva += floatval($dr->Imposta);
        }

        return (object) [
            'imponibile' => $this->decimal($imponibile),
            'iva' => $this->decimal($iva),
            'totale' => $this->decimal($iva) + $this->decimal($imponibile)
        ];
    }


        public function getDatiPA($xml)
        {
            $pa_n_doc = null;
            $pa_cup = null;
            $pa_cig = null;
            $pa_data = null;
            if (isset($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->DatiOrdineAcquisto))
            {
                $doa = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->DatiOrdineAcquisto;
                if (isset($doa->IdDocumento))
                {
                    $pa_n_doc = $doa->IdDocumento;
                }
                if (isset($doa->CodiceCUP))
                {
                    $pa_cup = $doa->CodiceCUP;
                }
                if (isset($doa->CodiceCIG))
                {
                    $pa_cig = $doa->CodiceCIG;
                }
                if (isset($doa->Data))
                {
                    $pa_data = Carbon::parse($doa->Data)->format('d/m/Y');
                }
            }

            return (object) [
                'numero' => $pa_n_doc,
                'cup' => $pa_cup,
                'cig' => $pa_cig,
                'data' => $pa_data
            ];
        }

        public function getDatiDDT($xml)
        {
            $n_ddt = null;
            $data_ddt = null;
            $dgd = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento;
            if (isset($dgd))
            {
                foreach ($dgd->DatiDDT as $dati)
                {
                    if(is_null($n_ddt))
                    {
                        $n_ddt = $dati->NumeroDDT;
                    }
                    if(is_null($data_ddt))
                    {
                        $data_ddt = Carbon::parse($dati->DataDDT)->format('d/m/Y');
                    }
                }
            }

            return (object) [
                'numero' => $n_ddt,
                'data' => $data_ddt
            ];
        }


        public function getFormatoTrasmissione($xml)
        {
            return ucfirst(strtolower(substr($xml->FatturaElettronicaHeader->DatiTrasmissione->FormatoTrasmissione, 1, 2)));
        }

        public function getTipoDocumento($xml)
        {
            $result = array_search($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->TipoDocumento, config('fe.types'));
            if(!$result)
            {
                return 'F';
            }
            return $result;
        }

        public function getMetodoPagamento($xml)
        {
            if (isset($xml->FatturaElettronicaBody->DatiPagamento))
            {
                foreach ($xml->FatturaElettronicaBody->DatiPagamento as $dati)
                {
                    foreach ($dati->DettaglioPagamento as $pagamento)
                    {
                        if (isset($pagamento->ModalitaPagamento))
                        {
                            return array_search($pagamento->ModalitaPagamento, config('fe.payment_methods'));
                        }
                    }
                }
            }
            return "";
        }

        public function getTipoSaldo($xml)
        {
            if (isset($xml->FatturaElettronicaBody->DatiPagamento))
            {
                foreach ($xml->FatturaElettronicaBody->DatiPagamento as $dati)
                {
                    foreach ($dati->DettaglioPagamento as $pagamento)
                    {
                        if (isset($pagamento->ModalitaPagamento))
                        {
                            if(is_object($pagamento->ModalitaPagamento))
                            {
                                $str = array( (string) $pagamento->ModalitaPagamento )[0];
                                return config('fe.payment_modes')[$str];
                            }
                            return config('fe.payment_modes')[$pagamento->ModalitaPagamento];

                        }
                    }
                }
            }
            return "";
        }

        public function getScadenza($xml)
        {
            if (isset($xml->FatturaElettronicaBody->DatiPagamento))
            {
                $date_pays = [];
                foreach ($xml->FatturaElettronicaBody->DatiPagamento as $dati)
                {
                    foreach ($dati->DettaglioPagamento as $pagamento)
                    {
                        if (isset($pagamento->DataScadenzaPagamento))
                        {
                            $date_pays[] = $pagamento->DataScadenzaPagamento;
                        }
                    }
                }

                sort($date_pays);
                return Carbon::parse(end($date_pays));
            }
            return Carbon::parse($xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Data);
        }

        public function getRate()
        {
            if (isset($xml->FatturaElettronicaBody->DatiPagamento))
            {
                $date_pays = array();
                foreach ($xml->FatturaElettronicaBody->DatiPagamento as $dati)
                {
                    foreach ($dati->DettaglioPagamento as $pagamento)
                    {
                        if (isset($pagamento->DataScadenzaPagamento))
                        {
                            $date_pays[] = $pagamento->DataScadenzaPagamento;
                        }
                    }
                }
                if (count($date_pays) > 1)
                {
                    sort($date_pays);
                    for ($i=0; $i<count($date_pays); $i++)
                    {
                        $date_pays[$i] = Carbon::parse($date_pays[$i])->format('d/m/Y');

                    }
                    return implode(';', $date_pays);
                }
            }
            return null;
        }


        public function getBollo($xml)
        {
            $datiGeneraliDocumento = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento;
            if (isset($datiGeneraliDocumento->DatiBollo->ImportoBollo))
            {
                return floatval($datiGeneraliDocumento->DatiBollo->ImportoBollo);
            }
            return '0.00';
        }

        /**
         * [addItems read DettaglioLinee loop and load item in invoice]
         * @param [eloquent] $invoice [description]
         * @param [obj]      $xml
         * @return [boolean]
         */
        public function addItems($invoice, $xml)
        {
            $DettaglioLinee = $xml->FatturaElettronicaBody->DatiBeniServizi->DettaglioLinee;
            if (isset($DettaglioLinee))
    		{
                foreach($DettaglioLinee as $dl)
                {
                    $iva = 0;
                    $iva_perc = intval($dl->AliquotaIVA);
                    if ($iva_perc > 0)
                    {
                        $iva = floatval(floatval($dl->PrezzoUnitario) * $iva_perc / 100);
                    }
                    $sconto = 0;
                    if (isset($dl->ScontoMaggiorazione->Percentuale))
                    {
                        $sconto = floatval($dl->ScontoMaggiorazione->Percentuale);
                    }

                    $exemption_id = null;
                    if (isset($dl->Natura))
                    {
                        $exemption_id = Exemption::getIdByCode($dl->Natura);
                    }

                    $item = new Item;
                        $item->invoice_id = $invoice->id;
                        $item->product_id = Product::default();
                        $item->descrizione = $dl->Descrizione;
                        $item->qta = $this->decimal($dl->Quantita);
                        $item->importo = $this->decimal($dl->PrezzoUnitario);
                        $item->perc_iva = intval($dl->AliquotaIVA);
                        $item->iva = $iva;
                        $item->sconto = $sconto;
                        $item->exemption_id = $exemption_id;
                    $item->save();

                }
            }
        }



}
