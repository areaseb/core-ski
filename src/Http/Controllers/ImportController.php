<?php

namespace Areaseb\Core\Http\Controllers;

use Areaseb\Core\Models\{City, Client, Company, Contact, Csv, Product, Sector , Country};
use Illuminate\Http\Request;

class ImportController extends Controller
{

//imports/{model} - GET
    public function importForm($model)
    {
        if($model == 'contacts')
        {
            $fields = ['cap', 'cellulare', 'citta', 'cognome', 'email', 'indirizzo','iscritto','piva','posizione','provincia', 'nazione','nome','tipo'];
            $desc = ['','meglio con prefisso nazionale e senza spazi',"è il campo univoco del contatto, se già presente verrà aggornato e non creato","iscritto o meno alle newsletter, i valori accettati sono 1|0 o Si|No", "per associare un contatto ad un'azienda (se l'azienda esiste)",'in sigla o testo (VI o Vicenza)','due caratteri o nome nazione in inglese (DE o Germany)','','Lead, Prospect o Client'];
            $type = 'Contatti';
            $formUrl = 'imports/contacts';
            //dd($fields, $desc);
            return view('areaseb::core.imports.form', compact('type', 'fields', 'formUrl', 'desc'));
        }
        elseif($model == 'companies')
        {
            $fields = ['rag_soc', 'telefono', 'email', 'pec', 'indirizzo', 'cap', 'citta', 'provincia', 'nazione', 'tipo', 'piva', 'cf', 'sdi', 'categoria', 'fornitore', 'sconto', 'note'];
            $desc = ['', 'meglio con prefisso nazionale e senza spazi', '', '',"", '', '', 'in sigla o testo (VI o Vicenza)', 'due caratteri o nome nazione in inglese (DE o Germany)', 'Lead, Prospect o Client', "piva facoltativa. Campo univoco per l'azienda. Se presente nel database i dati verranno aggiornati, se non esistono verrà creata una nuova azienda", "cf obbligatorio. Campo univoco per l'azienda. Se presente nel database i dati verranno aggiornati, se non esistono verrà creata una nuova azienda", "", "se la categoria non è presente verrà creata. Attenzione a maiuscole e minuscole", "i valori accettati sono 1|0 o Si|No", "solo numero intero o decimale", "" ];
            $type = 'Aziende';
            $formUrl = 'imports/companies';
            //dd($fields, $desc);
            return view('areaseb::core.imports.form', compact('type', 'fields', 'formUrl', 'desc'));
        }
        elseif($model == 'products')
        {
            dd('todo');
        }

        abort(404);
    }

//imports/{model} - POST
    public function importUpload(Request $request, $model)
    {

        $dataCsv = Csv::read($request->file);
        $header = explode(',', $request->header);

        if($model == 'contacts')
        {
            foreach($dataCsv as $row)
            {
                $data['user_id'] = null;
                $data['company_id'] = null;
                foreach($header as $key => $field)
                {
                    if(strpos($field, "--") === false)
                    {
                        $data[$field] = trim($row[$key]);
                    }
                }
                $data['origin'] = 'Csv';

                if(isset($data['cellulare']))
                {
                    $data['cellulare'] = preg_replace('/[^0-9+]/', '', $data['cellulare']);
                }

                $data['subscribed'] = 0;
                if(isset($data['iscritto']))
                {
                    $isc = strtolower($data['iscritto']);
                    if( ($isc == 'sì') || ($isc == 'si') || ($isc == '1') || ($isc == 1))
                    {
                        $data['subscribed'] = 1;
                    }
                    else
                    {
                        $data['subscribed'] = 0;
                    }
                }
                else
                {
                    $data['subscribed'] = 1;
                }
                unset($data['iscritto']);


                if(isset($data['nazione']))
                {
                    $country = Country::where('name', $data['nazione'])->orWhere('nome', $data['nazione'])->first();
                    if(!is_null($country))
                    {
                        $data['nazione'] = $country->iso2;
                    }
                    else
                    {
                        $data['nazione'] = 'IT';
                    }
                }
                else
                {
                    $data['nazione'] = 'IT';
                }

                if(isset($data['lingua']))
                {
                    $data['lingua'] = strtolower($data['lingua']);
                }
                else
                {
                    $data['lingua'] = 'it';
                }


                $contact = Contact::where('email', $data['email'])->first();
                if(is_null($contact))
                {
                    $contact = new Contact;
                }

                Contact::createOrUpdate($contact, $data);

                $contact->update(['origin' => 'Csv']);

                if(isset($data['piva']))
                {
                    if($data['piva'] != '')
                    {
                        $identifier = filter_var( $data['piva'], FILTER_SANITIZE_NUMBER_INT );
                        if(strlen($identifier) < 8)
                        {
                            $cf = $data['piva'];
                            $company = Company::where('cf', $data['piva'])->first();
                        }
                        else
                        {
                            $company = Company::where('piva', $identifier)->first();
                        }

                        if(!is_null($company))
                        {
                            $contact->update(['company_id' => $company->id]);
                        }
                    }
                }
                unset($data);
            }
            return 'done';
        }
        elseif($model == 'companies')
        {
            foreach($dataCsv as $row)
            {

                $tipo = null;
                foreach($header as $key => $field)
                {
                    if(strpos($field, "--") === false)
                    {
                        $data[$field] = trim($row[$key]);
                    }
                }

                if(isset($data['note']))
                {
                    if($data['note'] != "")
                    {
                        $arrN = [];
                        $arrN[0]['data'] = date('d/m/Y');
                        $arrN[0]['user'] = 'Admin';
                        $arrN[0]['note'] = $data['note'];
                    }
                    $data['note'] = $arrN;
                }

                if(isset($data['tipo']))
                {
                    $tipo = $data['tipo'];
                }
                unset($data['tipo']);

                $data['sector_id'] = $this->addSector($data);
                unset($data['categoria']);

                if(isset($data['fornitore']))
                {
                    $forn = strtolower($data['fornitore']);
                    if( ($forn == 'sì') || ($forn == 'si') || ($forn == '1') || ($forn == 1))
                    {
                        $data['fornitore'] = 1;
                    }
                    else
                    {
                        $data['fornitore'] = 0;
                    }
                }
                else
                {
                    $data['fornitore'] = 0;
                }

                if(isset($data['sconto']))
                {
                    $data['s1'] = str_replace("%", "", $data['sconto']);
                    $data['s1'] = trim($data['sconto']);
                    $data['s1'] = str_replace(",", ".", $data['sconto']);
                }
                else
                {
                    $data['s1'] = 0;
                }
                unset($data['sconto']);

                if(isset($data['nazione']))
                {
                    if(strlen($data['nazione']) > 2)
                    {
                        $country = Country::where('name', $data['nazione'])->orWhere('nome', $data['nazione'])->first();
                        if(!is_null($country))
                        {
                            $data['nazione'] = $country->iso2;
                        }
                        else
                        {
                            $data['nazione'] = 'IT';
                        }
                    }
                    $data['nazione'] = strtoupper($data['nazione']);
                }
                else
                {
                    $data['nazione'] = 'IT';
                }


                if($data['nazione'] == 'IT')
                {
                    if(isset($data['cf']))
                    {
                        if($data['cf'] != '')
                        {
                            $identifier = filter_var( $data['cf'], FILTER_SANITIZE_NUMBER_INT );
                            if(strlen($identifier) > 8)
                            {
                                $data['piva'] = $this->normalizePiva($identifier);
                            }
                        }
                        else
                        {
                            if($data['piva'] != '')
                            {
                                $identifier = filter_var( $data['piva'], FILTER_SANITIZE_NUMBER_INT );
                                $data['piva'] = $this->normalizePiva($identifier);
                                $data['cf'] = $this->normalizePiva($identifier);
                            }
                        }
                    }
                    else
                    {
                        $data['cf'] = false;
                        if(isset($data['piva']))
                        {
                            if($data['piva'] != '')
                            {
                                $identifier = filter_var( $data['piva'], FILTER_SANITIZE_NUMBER_INT );
                                $data['piva'] = $this->normalizePiva($identifier);
                                $data['cf'] = $this->normalizePiva($identifier);
                            }
                        }
                    }
                }
                else
                {
                    if(isset($data['cf']))
                    {
                        if($data['cf'] == '')
                        {
                            $data['cf'] = null;
                        }

                    }
                    else
                    {
                        $data['cf'] = null;
                    }

                    if(isset($data['piva']))
                    {
                        if($data['piva'] == '')
                        {
                            $data['piva'] = null;
                        }

                    }
                    else
                    {
                        $data['piva'] = null;
                    }
                    $data['lingua'] = 'en';
                }




                if(isset($data['provincia']))
                {
                    if(strlen($data['provincia']) == 2)
                    {
                        $data['provincia'] = City::provinciaFromSigla( strtoupper($data['provincia']) );
                    }
                    if($data['nazione'] == 'IT')
                    {
                        $city = City::getCityFromData($data['provincia'], $data['nazione']);
                    }
                    else
                    {
                        $city = City::cityFromCountry($data['nazione']);
                    }
                    if($city)
                    {
                        $data['city_id'] = $city->id;
                    }


                }

                $this->addOrUpdatedCompany($data, $tipo);
                unset($data);
            }
            return 'done';
        }

        return 'error';
    }

//imports/peek - POST
    public function peek(Request $request)
    {
        return Csv::peek($request->file);
    }

    public function addTipo($company, $tipo)
    {
        if($tipo)
        {
            if($tipo != '')
            {
                $client_id = Client::where('nome', ucfirst($tipo))->first()->id;
                $company->clients()->sync($client_id);
            }
        }
        else
        {
            $company->clients()->sync(Client::Client()->id);
        }
    }

    public function addSector($data)
    {
        $sector_id = null;
        if(isset($data['categoria']))
        {
            if(!is_null($data['categoria']) && ($data['categoria'] != '') )
            {
                return Sector::firstOrCreate(['nome' => $data['categoria']])->id;
            }
            else
            {
                return 'no passed';
            }
        }
        else
        {
            return null;
        }
        return $sector_id;
    }

    public function addOrUpdatedCompany($data, $tipo)
    {
        if( $data['cf'] )
        {
            if( !is_null($data['piva']) || !is_null($data['cf']) )
            {
                $company = null;
                if(is_null($company))
                {
                    $company = Company::where('piva', $data['piva'])->first();
                }

                if(is_null($company))
                {
                    $company = Company::where('cf', $data['cf'])->first();
                }

                if(is_null($company))
                {
                    $company = Company::create($data);
                }
                else
                {
                    $company->update($data);
                }

                if(strlen($data['piva']) < 8)
                {
                    $company->update(['privato' => 1]);
                }


                $this->addTipo($company, $tipo);
            }

        }
        else
        {
            $company = Company::create($data);
            $this->addTipo($company, $tipo);
        }

    }

    public function normalizePiva($str)
    {
        if(strlen($str) === 10)
        {
            return '0'.$str;
        }
        if(strlen($str) === 9)
        {
            return '00'.$str;
        }
        return $str;
    }


}
