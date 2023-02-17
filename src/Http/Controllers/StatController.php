<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Category, Company, Primitive, Product, Invoice, Item, Stat};
use Areaseb\Core\Exports\ClientAnnualRevenueExport as Export;

class StatController extends Controller
{
//stats/aziende
    public function companies()
    {
        $year = date('Y')-3;
        $query = Company::has('invoices')
                        ->with('invoices')
                        ->whereHas('invoices', function($query) use($year) {
                            $query->whereYear('data', '>=', $year);
                        })
                         ->orderBy('rag_soc', 'ASC');
        $companiesId = (clone $query)->pluck('rag_soc', 'id')->toArray();

        if(request()->get('ids'))
        {
            $arr = explode('-', request('ids'));
            $companies = Company::whereIn('id', $arr)->get();
            $companiesIdSelected = $arr;
            $annualStats = Stat::annualStatInvoicesQuery(Company::whereIn('id', $arr));
        }
        else
        {
            $companies = $query->get();
            $companiesIdSelected = [];
            $annualStats = Stat::annualStatInvoices();
        }
// dd($annualStats, intval($annualStats[(date('Y')-3)]), $annualStats[(date('Y')-3)]);


        return view('areaseb::core.accounting.stats.companies', compact('companies', 'annualStats','companiesId', 'companiesIdSelected'));
    }

//stats/categorie
    public function categories()
    {
        $data = []; $graphData = []; $labels = ''; $totali = ''; $fatturato = '';
        foreach(Stat::groupProductsByCategory() as $cat => $products)
        {
            if(request()->get('year'))
            {
                $invoiceIds = Invoice::anno(request('year'))->pluck('id')->toArray();
                $query = Item::whereIn('invoice_id', $invoiceIds)->whereIn('product_id', $products);
                $tot = $query->count();
                $fatt = $query->sum(\DB::raw('iva + importo'));
            }
            else
            {
                $query = Item::whereIn('product_id', $products);
                $tot = $query->count();
                $fatt = $query->sum(\DB::raw('iva + importo'));
            }

            $totali .= '"'.$tot.'",';
            $fatturato .= '"'.$fatt.'",';
            $labels .= '"'.Category::find($cat)->nome.'",';
            $data[$cat]['totali'] = $tot;
            $data[$cat]['fatturato'] = Primitive::NF($fatt);
        }


        $graphData['labels'] = substr($labels, 0, -1);
        $graphData['fatturato'] = substr($fatturato, 0, -1);
        $graphData['totali'] = substr($totali, 0, -1);

        return view('areaseb::core.accounting.stats.categories', compact('graphData', 'data'));
    }


//stats/categorie/{id}
    public function category($id)
    {
        $category = Category::find($id);

        $data = []; $graphData = []; $labels = ''; $totali = ''; $fatturato = '';
        $produtIds = $category->products()->pluck('id')->toArray();
        foreach($category->products as $product)
        {
            if(request()->get('year'))
            {
                $invoiceIds = Invoice::anno(request('year'))->pluck('id')->toArray();
                $query = Item::whereIn('invoice_id', $invoiceIds)->where('product_id', $product->id);
                $tot = $query->count();
                $fatt = $query->sum(\DB::raw('iva + importo'));
            }
            else
            {
                $invoiceIds = Invoice::anno(date('Y'))->pluck('id')->toArray();
                $query = Item::where('product_id', $product->id);
                $tot = $query->count();
                $fatt = $query->sum(\DB::raw('iva + importo'));
            }

            $totali .= '"'.$tot.'",';
            $fatturato .= '"'.$fatt.'",';
            $labels .= '"'.$product->nome.'",';
            $data[$product->id]['totali'] = $tot;
            $data[$product->id]['fatturato'] = Primitive::NF($fatt);
        }


        $filteredInvoiceIds = Item::whereIn('product_id', $produtIds)->whereIn('invoice_id', $invoiceIds)->pluck('invoice_id');
        $companyIds = Invoice::whereIn('id', $filteredInvoiceIds)->pluck('company_id');
        $sectorIds = Company::whereIn('id', $companyIds)->pluck('sector_id');
        $sectors = [];
        foreach($sectorIds as $sector_id)
        {
            if(intval($sector_id))
            {
                if(!isset($sectors[$sector_id]))
                {
                    $sectors[$sector_id] = 1;
                }
                else
                {
                    $sectors[$sector_id] += 1;
                }
            }
        }

        $totalAmountSectors = [];
        foreach($sectors as $sector_id => $count)
        {
            $companyIds = Company::where('sector_id', $sector_id)->pluck('id');
            $totalAmountSectors[$sector_id] = Invoice::whereIn('id', $filteredInvoiceIds)->whereIn('company_id', $companyIds)->sum('imponibile');
        }

        $graphData['labels'] = substr($labels, 0, -1);
        $graphData['fatturato'] = substr($fatturato, 0, -1);
        $graphData['totali'] = substr($totali, 0, -1);

        return view('areaseb::core.accounting.stats.category', compact('category', 'graphData', 'data','invoiceIds', 'sectors', 'totalAmountSectors'));
    }

//stats/balance
    public function balance()
    {
        $graphData = Stat::monthlyAnnualGraph();
        return view('areaseb::core.accounting.stats.balance', compact('graphData'));
    }

//stats/exports
    public function export()
    {
        $year = date('Y')-3;
        $query = Company::has('invoices')
                        ->with('invoices')
                        ->whereHas('invoices', function($query) use($year) {
                            $query->whereYear('data', '>=', $year);
                        })->orderBy('rag_soc', 'ASC');

        if(request()->get('ids'))
        {
            $arr = explode('-', request('ids'));
            $companies = Company::whereIn('id', $arr)->get();
        }
        else
        {
            $companies = $query->get();
        }
        return \Excel::download(new Export($companies), 'stats-clienti.xlsx');
    }


//stats/expenses/{category}
    public function expense(Category $category)
    {

        $query = Cost::query();

        $query = $query->whereIn('expense_id', $category->expenses()->pluck('id'));

        $companies = ( clone $query )->distinct('company_id')->pluck('company_id')->toArray();
        $groupedCosts = [];
        foreach ($companies as $company_id) {
            $groupedCosts[$company_id] = ( clone $query )->where('company_id', $company_id)->get();
        }

        return view('areaseb::core.accounting.stats.expenses', compact('groupedCosts', 'category'));
    }



}
