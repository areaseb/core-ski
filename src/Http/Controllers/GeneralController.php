<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Calendar, City, Company, Contact, Country, Client, Editor, Event, Invoice, Item, Media, Newsletter, NewsletterList, Notification, Note, Product, Report, Role, Template, User};
use \Carbon\Carbon;
use \Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionRegistrar;

class GeneralController extends Controller
{
    public function updateField(Request $request)
    {
        $model = $this->findModel($request);
        if($model)
        {
            $model->update([$request->field => $this->cleanDescription($request->value)]);

            if($request->model === 'Item')
            {
                $this->updateInvoice($model);
            }
            return 'done';
        }

        return 'not found';
    }

    public function findModel($data)
    {
        if ( class_exists('Areaseb\\Core\\Models\\'.$data->model) )
		{
            $class = 'Areaseb\\Core\\Models\\'.$data->model;
			return $class::findOrFail($data->id);
		}
        return null;
    }

    public function updateInvoice($model)
    {
        $invoice = $model->invoice;
        $invoice_iva = 0;
        $invoice_imponibile = 0;
        foreach($invoice->items as $item)
        {
            $qta = $item->qta;
            $importo = $item->importo;
            $sconto = (1-($item->sconto)/100);
            $perc_iva = $item->perc_iva/100;

            $invoice_iva += ($importo*$qta)*$sconto*$perc_iva;//ivato totale riga con sconto
            $invoice_imponibile += $importo*$qta*$sconto;//senza iva

            $item->update([
                'iva' => ($importo*$qta)*$sconto*$perc_iva,
            ]);

        }

        $invoice->update([
            'imponibile' => $invoice_imponibile,
            'iva' => $invoice_iva
        ]);
    }

//api/countries - POST
    public function prefix()
    {
        return Country::getCountryPhone(request('iso'));
    }

//api/city
    public function zip()
    {
        $city = City::where('comune', request('citta'))->first();
        if($city)
        {
            return ['cap' => $city->cap, 'provincia' => $city->provincia];
        }
        return '';
    }


//api/clear-cache - POST
    public function clearCache()
    {
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return 'done';
    }


    public function cleanDescription($str)
    {
        $str = str_replace('€', 'EUR', $str);
        $str = str_replace('£', 'GBP', $str);
        $str = str_replace('$', 'USD', $str);
        $str = str_replace('©',' Copyright', $str);
        $str = str_replace('®', ' Registered', $str);
        $str = str_replace('™',' Trademark', $str);
        return $str;
    }

    public function citiesOfProvince(Request $request, $province)
    {
        $arr = City::where('provincia', $province)->where('italia', true)->pluck('comune', 'id')->toArray();
        return City::select2Mutate($arr);
    }

}
