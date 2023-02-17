<?php

namespace Areaseb\Core\Http\Controllers;

use Areaseb\Core\Models\{Client, Company, CompanyExport, Contact, ContactExport, Csv, Product};
use Illuminate\Http\Request;

class ExportController extends Controller
{

//exports/{model} - GET
    public function export(Request $request, $model)
    {
        if($model == 'contacts')
        {
            $contacts = $this->contacts($request);
            $filename = 'contatti-'.date('Y-m-d').'.xlsx';
            return \Excel::download(new ContactExport($contacts), $filename);
        }
        elseif($model == 'companies')
        {
            $companies = $this->companies($request);
            $filename = 'aziende-'.date('Y-m-d').'.xlsx';
            return \Excel::download(new CompanyExport($companies), $filename);
        }

        abort(404);
    }

    public function contacts($data)
    {
        $arr = [];$count = 0;
        if($data->input())
        {
            $contacts = Contact::filter($data);
        }
        else
        {
            $contacts = Contact::with('company');
        }

        return $contacts->get();
    }

    public function companies($data)
    {
        $arr = [];$count = 0;
        if($data->input())
        {
            $companies = Company::filter($data);
        }
        else
        {
            $companies = Company::query();
        }

        return $companies->get();
    }


}
