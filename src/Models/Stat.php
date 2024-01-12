<?php

namespace Areaseb\Core\Models;

use Illuminate\Support\Facades\Cache;
use \Carbon\Carbon;
use \DB;

class Stat
{
    
    public static function TotaleQueryInsoluti($query = null)
    {
        $imponibile = self::ImponibileQuery((clone $query), true);

        $perc = 0;
        $imponibileClean = self::ImponibileClean();
        if($imponibileClean)
        {
            $perc =  round( ($imponibile / $imponibileClean)*10000)/100;
        }

        return (object) [
            'imponibile' => Primitive::NF($imponibile),
            'totale' => self::Imponibile(),
            'perc' => $perc
        ];
    }


    public static function TotaleQueryBoxes($data)
    {
        $arr_id_invoice = [];
        $arr_data = [];

        foreach ($data->get() as $d) {
            array_push($arr_id_invoice, $d->id);
        }
        
        foreach (array_keys(config('invoice.payment_modes')) as $pt) {
                if($pt != ''){

/*                    $item = Invoice::select(\DB::raw('SUM(imponibile) as imponibile'), \DB::raw('SUM(ritenuta) as ritenuta'))
                    ->whereIn("id", $arr_id_invoice)
                    ->where("tipo_saldo", $pt)
                    ->where('numero', '<>', '')
                    ->where('numero', '<>', 0)
                    ->whereNotNull('numero')
                    ->first();
                    //dd($item);
                    $totale = $item->imponibile - $item->ritenuta;	*/

                    
                    $totale = (clone $data)
		                    ->whereIn("id", $arr_id_invoice)
		                    ->where("tipo_saldo", $pt)
		                    ->entrate()
		                    ->sum('imponibile') 
		                    - 
		                    (clone $data)
		                    ->whereIn("id", $arr_id_invoice)
		                    ->where("tipo_saldo", $pt)
		                    ->entrate()
		                    ->sum('ritenuta');
                    
                    $totale -= abs((clone $data)
		                    ->whereIn("id", $arr_id_invoice)
		                    ->where("tipo_saldo", $pt)
		                    ->notediaccredito()
		                    ->sum('imponibile')) 
		                    - 
		                    abs((clone $data)
		                    ->whereIn("id", $arr_id_invoice)
		                    ->where("tipo_saldo", $pt)
		                    ->notediaccredito()
		                    ->sum('ritenuta'));
		                    
		        	
                    $record = (object) [
                        'label' => config('invoice.payment_modes')[$pt],
                        'totale' => $totale,
                    ];
                    array_push($arr_data, $record);
                    
                }
                
        }



        return $arr_data;
    }


    public static function ImponibileClean($anno = null)
    {
        if(is_null($anno))
        {
            $sum = Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->entrate()->sum('imponibile') - Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->entrate()->sum('ritenuta');
            $sum -= abs(Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->notediaccredito()->sum('imponibile') - Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->notediaccredito()->sum('ritenuta'));
            return $sum;
        }

        $imponibile = Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->entrate()->sum('imponibile') - Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->entrate()->sum('ritenuta') - abs(Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->notediaccredito()->sum('imponibile') - Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->notediaccredito()->sum('ritenuta')) ;

        return $imponibile;
    }


    public static function ImposteQuery($query)
    {
        $sum = (clone $query)->entrate()->sum('iva');
        $sum -= abs((clone $query)->notediaccredito()->sum('iva'));
        return $sum;
    }

    public static function ImponibileQuery($query, $unpaid = null)
    {
        if(is_null($unpaid))
        {
            $sum = (clone $query)->entrate()->sum('imponibile') - (clone $query)->entrate()->sum('ritenuta');
            $sum -= abs((clone $query)->notediaccredito()->sum('imponibile')) - abs((clone $query)->notediaccredito()->sum('ritenuta'));
            // $sum += (clone $query)->unpaid()->with('payments')->get()->sum(function ($invoice) {
            //         return $invoice->payments->sum('amount');
            //     });
            
            return $sum;
        }

        return $query->entrate()->sum('imponibile') - $query->entrate()->sum('ritenuta');

    }

    public static function TotaleQuery($query = null)
    {
        if(!is_null($query))
        {
            $imposte = self::ImposteQuery(clone $query);
            $imponibile = self::ImponibileQuery(clone $query);
            return (object) [
                'imposte' => Primitive::NF($imposte),
                'imponibile' => Primitive::NF($imponibile),
                'totale' => Primitive::NF($imposte + $imponibile),
            ];
        }
        return (object) [
            'imposte' => self::Imposte(),
            'imponibile' => self::Imponibile(),
        ];
    }


    public static function Imposte($anno = null)
    {
        if(is_null($anno))
        {
            $imposte = Cache::remember('imposte', 120, function () {
                $sum = Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->entrate()->sum('iva');
                $sum -= abs(Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->notediaccredito()->sum('iva'));
                return Primitive::NF( $sum );
            });
        }
        else
        {
            $sum = Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->entrate()->sum('iva');
            $sum -= abs(Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->notediaccredito()->sum('iva'));
            $imposte = Primitive::NF( $sum );
        }

        return $imposte;
    }

    public static function ImposteBilancio($anno)
    {
        $sum = Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->entrate()->sum('iva');
        $sum -= abs(Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->notediaccredito()->sum('iva'));
        $ivaCosti = Cost::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->sum('totale') - Cost::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->sum('imponibile');
        return Primitive::NF( $sum - $ivaCosti);
    }

    public static function Imponibile($year = null)
    {
        if(is_null($year))
        {
            $imponibile = Cache::remember('imponibile', 120, function () {
                $sum = Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->entrate()->sum('imponibile') - Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->entrate()->sum('ritenuta');
                // $sum += Invoice::anno(date('Y'))->unpaid()->with('payments')->get()->sum(function ($invoice) {
                //                 return $invoice->payments->sum('amount');
                //             });
                $sum -= abs(Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->notediaccredito()->sum('imponibile') - Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->notediaccredito()->sum('ritenuta'));
                return Primitive::NF(( $sum ));
            });
        }
        else
        {
            $sum = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->entrate()->sum('imponibile') - Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->entrate()->sum('ritenuta');
            // $sum += Invoice::anno($year)->unpaid()->with('payments')->get()->sum(function ($invoice) {
            //                 return $invoice->payments->sum('amount');
            //             });
            $sum -= abs(Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->notediaccredito()->sum('imponibile') - Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->notediaccredito()->sum('ritenuta'));
            $imponibile = Primitive::NF(( $sum ));
        }
        return $imponibile;
    }

    public static function Totale($year = null)
    {
        if(is_null($year))
        {
            $imponibile = Cache::remember('imponibile', 120, function () {
                $sum = Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->entrate()->sum(DB::raw('iva + imponibile - ritenuta'));	//->saldate()
/*                $sum += Invoice::anno(date('Y'))->unpaid()->with('payments')->get()->sum(function ($invoice) {
                                return $invoice->payments->sum('amount');
                            });*/
                $sum -= abs(Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->notediaccredito()->sum(DB::raw('iva + imponibile - ritenuta')));	//$sum -= Invoice::anno(date('Y'))->saldate()->notediaccredito()->sum(DB::raw('iva + imponibile'));
                return Primitive::NF( $sum );
            });
        }
        else
        {
            if($year > 2018)
            {
                $sum = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->entrate()->sum(DB::raw('iva + imponibile - ritenuta'));	//->saldate()
/*                $sum += Invoice::anno($year)->unpaid()->with('payments')->get()->sum(function ($invoice) {
                                return $invoice->payments->sum('amount');
                            });*/
                $sum -= abs(Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->notediaccredito()->sum(DB::raw('iva + imponibile - ritenuta')));	//$sum -= Invoice::anno($year)->saldate()->notediaccredito()->sum(DB::raw('iva + imponibile'));
            }
            else
            {
                $sum = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->entrate()->sum(DB::raw('iva + imponibile - ritenuta'));
                $sum -= abs(Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->notediaccredito()->sum(DB::raw('iva + imponibile - ritenuta')));	//$sum -= Invoice::anno($year)->notediaccredito()->sum(DB::raw('iva + imponibile'));
            }

            $imponibile = Primitive::NF( $sum );
        }
        return $imponibile;
    }


    public static function TotaleImponibile($year = null)
    {
        if(is_null($year))
        {
            $imponibile = Cache::remember('imponibile', 120, function () {
                $sum = Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->entrate()->sum(DB::raw('imponibile - ritenuta'));
                $sum -= abs(Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->notediaccredito()->sum(DB::raw('imponibile - ritenuta')));
                return Primitive::NF( $sum );
            });
        }
        else
        {
            if($year > 2018)
            {
                $sum = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->entrate()->sum(DB::raw('imponibile - ritenuta'));
                $sum -= abs(Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->notediaccredito()->sum(DB::raw('imponibile - ritenuta')));
            }
            else
            {
                $sum = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->entrate()->sum(DB::raw('imponibile - ritenuta'));
                $sum -= abs(Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->notediaccredito()->sum(DB::raw('imponibile - ritenuta')));
            }

            $imponibile = Primitive::NF( $sum );
        }
        return $imponibile;
    }


    public static function TotaleMese($year = null)
    {
        if(!is_null($year))
        {
            $years = range($year, ($year-3));
            $arr = [];
            foreach($years as $year)
            {
                for($x=1;$x<=12;$x++)
                {
                    $arr[$year][$x] = self::totMonth($year, str_pad($x, 2, '0', STR_PAD_LEFT));
                }
            }
            return $arr;

        }
        else
        {
            $totale_mese = Cache::remember('totale_mese', 120, function () {
                $arr = [];
                $years = [(date('Y') -1), date('Y')];
                foreach($years as $year)
                {
                    for($x=1;$x<=12;$x++)
                    {
                        $arr[$year][$x] = self::totMonth($year, str_pad($x, 2, '0', STR_PAD_LEFT));
                    }
                }
                return $arr;
            });
        }

        return $totale_mese;
    }



    public static function TotaleMeseImponibile($year = null)
    {
        if(!is_null($year))
        {
            $years = range($year, ($year-3));
            $arr = [];
            foreach($years as $year)
            {
                for($x=1;$x<=12;$x++)
                {
                    $arr[$year][$x] = self::totMonthImponibile($year, str_pad($x, 2, '0', STR_PAD_LEFT));
                }
            }
            return $arr;

        }
        else
        {
            $totale_mese = Cache::remember('totale_mese', 120, function () {
                $arr = [];
                $years = [(date('Y') -1), date('Y')];
                foreach($years as $year)
                {
                    for($x=1;$x<=12;$x++)
                    {
                        $arr[$year][$x] = self::totMonthImponibile($year, str_pad($x, 2, '0', STR_PAD_LEFT));
                    }
                }
                return $arr;
            });
        }

        return $totale_mese;
    }


    public static function TotaleMeseVat($year = null)
    {
        if(!is_null($year))
        {
            $years = range($year, ($year-3));
            $arr = [];
            foreach($years as $year)
            {
                for($x=1;$x<=12;$x++)
                {
                    $arr[$year][$x] = self::totMonthVat($year, str_pad($x, 2, '0', STR_PAD_LEFT));
                }
            }
            return $arr;

        }
        else
        {
            $totale_mese_iva = Cache::remember('totale_mese_iva', 120, function () {
                $arr = [];
                $years = [(date('Y') -1), date('Y')];
                foreach($years as $year)
                {
                    for($x=1;$x<=12;$x++)
                    {
                        $arr[$year][$x] = self::totMonthVat($year, str_pad($x, 2, '0', STR_PAD_LEFT));
                    }
                }
                return $arr;
            });
        }
        return $totale_mese_iva;
    }


    public static function totMonthVat($year, $month, $to = null)
    {
        if(is_null($to))
        {
            if($year > 2018)
            {
                $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->entrate();
                $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->notediaccredito();
            }
            else
            {
                $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->entrate();
                $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->notediaccredito();
            }
            $add  = $entrate->sum('iva');
            $add -= abs($uscite->sum('iva'));
        }
        else
        {
            $add = 0;
            $month = intval($month);
            $end = $month + $to;

            for($month;$month < $end; $month++)
            {
                if($year > 2018)
                {
                    $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->entrate();
                    $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->notediaccredito();
                }
                else
                {
                    $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->entrate();
                    $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->notediaccredito();
                }
                $add  = $entrate->sum('iva');
                $add -= abs($uscite->sum('iva'));
            }
        }
        return $add;
    }

    public static function invoicePageGraph()
    {
        $invoice_page_graph = Cache::remember('invoice_page_graph', 120, function () {
            $arr = [];$labels = '';$data = '';
            for($x=12;$x>=0;$x--)
            {
                $month = Carbon::today()->subMonths($x)->format('m');
                $year = Carbon::today()->subMonths($x)->format('Y');

                $add  = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->entrate()->sum('imponibile');
                $add -= abs(Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->notediaccredito()->sum('imponibile'));

                $labels .= '"'.trans('dates.ms'.$month).'",';
                $data .= $add.',';

            }

            $labels = rtrim($labels,',');
            $data = rtrim($data,',');

            $arr = ['labels' => $labels, 'data' => $data];
            return $arr;
        });
        return $invoice_page_graph;
    }


    public static function monthlyAnnualGraph()
    {
        $data = []; $min = [];
        foreach(range(date('Y')-3, date('Y')) as $year)
        {
            $data_set = '';
            for($x=12;$x>=1;$x--)
            {
                $month = sprintf('%02d', $x);
                $add  = self::totMonth($year, $month);
                $min[] = $add;
                $data_set .= $add.',';
            }
            $data[$year] = rtrim($data_set,',');
        }
        $data['min'] = min($min);
        return $data;
    }


    public static function totMonth($year, $month, $to = null)
    {
        if(is_null($to))
        {
            if($year > 2018)
            {
                $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->entrate();
                $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->notediaccredito();
            }
            else
            {
                $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->entrate();
                $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->notediaccredito();
            }
            
            $add  = $entrate->sum(DB::raw('iva + imponibile - ritenuta'));
            $add -= abs($uscite->sum(DB::raw('iva + imponibile - ritenuta')));

            //calcolo vecchio
            //$add  = $entrate->sum(DB::raw('iva + imponibile'));
            //$add -= abs($uscite->sum(DB::raw('iva + imponibile')));


            // $add += Invoice::mese(['year' => $year, 'month' => $month])->unpaid()->with('payments')->get()->sum(function ($invoice) {
            //                 return $invoice->payments->sum('amount');
            //             });

        }
        else
        {
            $add = 0;
            $month = intval($month);
            $end = $month + $to;

            for($month;$month < $end; $month++)
            {
                if($year > 2018)
                {
                    $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->entrate();
                    $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->notediaccredito();
                }
                else
                {
                    $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->entrate();
                    $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->notediaccredito();
                }

                $add  = $entrate->sum(DB::raw('iva + imponibile - ritenuta'));
                $add -= abs($uscite->sum(DB::raw('iva + imponibile - ritenuta')));
                
                //calcolo vecchio
                //$add  += $entrate->sum(DB::raw('iva + imponibile'));
                //$add -= abs($uscite->sum(DB::raw('iva + imponibile')));


                // $add += Invoice::mese(['year' => $year, 'month' => $month])->unpaid()->with('payments')->get()->sum(function ($invoice) {
                //                 return $invoice->payments->sum('amount');
                //             });
            }
        }
        return $add;
    }

    public static function totMonthImponibile($year, $month, $to = null)
    {
        $add = 0;
        if(is_null($to))
        {
            if($year > 2018)
            {
                $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->entrate();
                $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->notediaccredito();
            }
            else
            {
                $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->entrate();
                $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', $month)->notediaccredito();
            }
            $add  += $entrate->sum('imponibile') - $entrate->sum('ritenuta');
            $add -= abs($uscite->sum('imponibile') - $uscite->sum('ritenuta'));
        }
        else
        {
            $add = 0;
            $month = intval($month);
            $end = $month + $to;

            for($month;$month < $end; $month++)
            {
                if($year > 2018)
                {
                    $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->entrate();
                    $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->notediaccredito();
                }
                else
                {
                    $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->entrate();
                    $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->notediaccredito();
                }
                $add  += $entrate->sum('imponibile') - $entrate->sum('ritenuta');
                $add -= abs($uscite->sum('imponibile') - $uscite->sum('ritenuta'));
            }
        }
        return $add;
    }


    public static function totMonthIva($from, $to)
    {
        $addVendite = 0;
        $addAcquisti = 0;
        $month = intval($from);
        $end = $month + $to;

        for($month;$month <= $end; $month++)
        {
            $entrateV = Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->entrate();
            $usciteV = Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->whereMonth('data', sprintf('%02d', $month))->notediaccredito();
            $addVendite += $entrateV->sum('iva') - abs($usciteV->sum('iva'));

            $entrateA = Cost::mese(['year' => date('Y'), 'month' => sprintf('%02d', $month)]);
            $addAcquisti += $entrateA->sum(DB::raw('totale - imponibile'));
        }

        return [
            'Vendite' => Primitive::NF($addVendite),
            'Acquisti' => Primitive::NF($addAcquisti),
            'Bilancio' => Primitive::NF($addVendite - $addAcquisti),
        ];
    }


    public static function annualStatInvoices()
    {
        $annual_stats = Cache::remember('annual_stats', 360, function () {
            $arr = [];
            foreach (range(date('Y'), (date('Y')-3), -1) as $year)
            {
                if($year > 2018)
                {
                    $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->entrate()->sum(DB::raw('iva + imponibile - ritenuta'));
                    $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->notediaccredito()->sum(DB::raw('iva + imponibile - ritenuta'));
                    $arr[$year] = Primitive::NF( $entrate-abs($uscite) );
                }
                else
                {
                    $entrate = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->entrate()->sum(DB::raw('iva + imponibile - ritenuta'));
                    $uscite = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->notediaccredito()->sum(DB::raw('iva + imponibile - ritenuta'));
                    $arr[$year] = Primitive::NF( $entrate-abs($uscite) );
                }
            }
            return $arr;
        });
        return $annual_stats;
    }

    public static function annualStatInvoicesQuery($query)
    {
        $arr = [];
        foreach (range(date('Y'), (date('Y')-3), -1) as $year)
        {
            $entrate = 0; $uscite = 0;
            if($year > 2018)
            {
                foreach($query->get() as $company)
                {
                    $entrate += $company->invoices()->whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->entrate()->sum(DB::raw('iva + imponibile - ritenuta'));
                    $uscite += abs($company->invoices()->whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->notediaccredito()->sum(DB::raw('iva + imponibile - ritenuta')));
                }
                $arr[$year] = Primitive::NF( $entrate-$uscite );
            }
            else
            {
                foreach($query->get() as $company)
                {
                    $entrate += $company->invoices()->whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->entrate()->sum(DB::raw('iva + imponibile - ritenuta'));
                    $uscite += abs($company->invoices()->whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->notediaccredito()->sum(DB::raw('iva + imponibile - ritenuta')));
                }
                $arr[$year] = Primitive::NF( $entrate-$uscite );
            }
        }
        return $arr;
    }

    public static function Utili($anno = null)
    {

        if(is_null($anno))
        {
            $utili = Cache::remember('imponibile', 120, function () {
                $sum = Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->entrate()->sum(DB::raw('iva + imponibile - ritenuta'));
                $sum -= abs(Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->notediaccredito()->sum(DB::raw('iva + imponibile - ritenuta')));
                $sum -= Cost::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->sum('totale');
                return Primitive::NF( $sum );
            });
            return $utili;
        }

        if($anno > 2018)
        {
            $sum = Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->entrate()->sum(DB::raw('iva + imponibile - ritenuta'));
            $sum -= abs(Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->notediaccredito()->sum(DB::raw('iva + imponibile - ritenuta')));
            $sum -= Cost::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->sum('totale');
            return Primitive::NF( $sum );
        }
        $sum = Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->entrate()->sum(DB::raw('iva + imponibile - ritenuta'));
        $sum -= abs(Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->notediaccredito()->sum(DB::raw('iva + imponibile - ritenuta')));
        $sum -= Cost::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->sum('totale');
        return Primitive::NF( $sum );
    }


    public static function UtiliImponibile($anno = null)
    {

        if(is_null($anno))
        {
            $utili = Cache::remember('imponibile', 120, function () {
                $sum = Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->entrate()->sum(DB::raw('imponibile - ritenuta'));
                $sum -= Invoice::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->notediaccredito()->sum(DB::raw('imponibile - ritenuta'));
                $sum -= Cost::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->sum(DB::raw('imponibile - ritenuta'));
                return Primitive::NF( $sum );
            });
            return $utili;
        }

        if($anno > 2018)
        {
            $sum = Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->entrate()->sum(DB::raw('imponibile - ritenuta'));
            $sum -= Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->notediaccredito()->sum(DB::raw('imponibile - ritenuta'));
            $sum -= Cost::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->sum(DB::raw('imponibile'));
            return Primitive::NF( $sum );
        }
        $sum = Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->entrate()->sum(DB::raw('imponibile - ritenuta'));
        $sum -= Invoice::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->notediaccredito()->sum(DB::raw('imponibile - ritenuta'));
        $sum -= Cost::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->sum(DB::raw('imponibile'));
        return Primitive::NF( $sum );
    }

//COSTI

    public static function TotaleQueryCosti($query)
    {
        return Primitive::NF($query->sum('totale'));
    }

    public static function ImponibileQueryCosti($query)
    {
        return Primitive::NF($query->sum('imponibile'));
    }

    public static function ImposteQueryCosti($query)
    {
        return Primitive::NF($query->sum(DB::raw('totale - imponibile')));
    }

    public static function TotaleQueryCostiDaPagare($query)
    {
        $saldati = (clone $query->where('saldato', 0))->sum('totale');
        $costIds = (clone $query->where('saldato', 0))->pluck('id')->toArray();
        $pagamenti = 0;
        if(count($costIds))
        {
            $pagamenti = \Areaseb\Core\Models\CostPayment::whereIn('cost_id', $costIds)->sum('amount');
        }
        return Primitive::NF( $saldati - $pagamenti );
    }



    public static function CategoriaQueryCosti($query)
    {
        $default = Expense::default();
        $arr = [];
        $categories = Category::categoryOf('Expense')->orderBy('nome', 'asc')->get();

        foreach(Category::categoryOf('Expense')->orderBy('nome', 'asc')->get() as $category)
        {
            $q = clone $query;
            $ids = $category->expenses()->pluck('id')->toArray();
            $arr[$category->nome] = Primitive::NF($q->whereIn('expense_id', $ids)->sum('totale'));
        }
        $q = clone $query;
        $arr[$default->nome] = Primitive::NF($q->where('expense_id', $default->id)->sum('totale'));
        return $arr;
    }


    public static function ImposteCosti($anno = null)
    {
        if(is_null($anno))
        {
            $imposte_costi = Cache::remember('imposte_costi', 120, function () {
                $t = Cost::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->sum('totale');
                $t -=  Cost::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->sum('imponibile');
                return Primitive::NF( $t );
            });
        }
        else
        {
            $t = Cost::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->sum('totale');
            $t -=  Cost::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->sum('imponibile');
            return Primitive::NF( $t );
        }

        return $imposte_costi;
    }


    public static function TotaleCosti($anno = null)
    {
        if(is_null($anno))
        {
            $totale_costi = Cache::remember('totale_costi', 120, function () {
                return Primitive::NF( Cost::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->sum('totale') );
            });
            return $totale_costi;
        }

        return Primitive::NF( Cost::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->sum('totale') );
    }

    public static function TotaleCostiImponibile($anno = null)
    {
        if(is_null($anno))
        {
            $totale_costi = Cache::remember('totale_costi', 120, function () {
                return Primitive::NF( Cost::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->sum('imponibile') );
            });
            return $totale_costi;
        }

        return Primitive::NF( Cost::whereBetween('data', [$anno.'-06-01', ($anno+1).'-05-31'])->sum('imponibile') );
    }


    public static function CategoriaCosti($anno = null)
    {
        $default = Expense::default();
        $arr = [];
        $categories = Category::categoryOf('Expense')->orderBy('nome', 'asc')->get();

        foreach(Category::categoryOf('Expense')->orderBy('nome', 'asc')->get() as $category)
        {
            $ids = $category->expenses()->pluck('id')->toArray();
            $arr[$category->nome] = Primitive::NF(Cost::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->whereIn('expense_id', $ids)->sum('totale'));
        }

        $arr[$default->nome] = Primitive::NF(Cost::whereBetween('data', [date('Y').'-06-01', (date('Y')+1).'-05-31'])->where('expense_id', $default->id)->sum('totale'));
        return $arr;
    }


//IVA
    // public static function IvaCosti($year)
    // {
    //     $costs = Cost::where('anno', $year)->whereRaw('totale-imponibile > 0')
    // }


//PRODUCTS

    /**
     * @return [arr] k: cat_id v: [prod_id]
     */
    public static function groupProductsByCategory()
    {
        $grouped = Cache::remember('grouped', 120, function () {
            $grouped = [];
            foreach(Category::categoryOf("Product")->get() as $category)
            {
                foreach($category->products()->pluck('id')->toArray() as $id)
                {
                    $grouped[intval($category->id)][] = $id;
                }
            }
            return $grouped;
        });

        return $grouped;
    }


	public static function groupProductsByCategoryFromProducts($products)
    {        
        //$grouped = Cache::remember('groupedProducts', 120, function ($products) {
            $grouped = [];
            
            foreach($products as $product){
            	$cat = \DB::table('categorizables')->where('categorizable_id', $product)->where('categorizable_type', 'Areaseb\Core\Models\Product')->pluck('category_id')->first();
            	
            	$grouped[intval($cat)][] = $product;
            }
            
            ksort($grouped);
            
            return $grouped;
        //});

        return $grouped;
    }



}
