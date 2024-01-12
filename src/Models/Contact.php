<?php

namespace Areaseb\Core\Models;

use Carbon\Carbon;
use Areaseb\Core\Models\ContactDisabled;
use Areaseb\Core\Models\{Company, Branch, Invoice};
use Areaseb\Core\Models\ContactMaster;
use Areaseb\Core\Models\Specialization;
use Areaseb\Core\Models\Country;
class Contact extends Primitive
{

//ELOQUENT
    // $branch_id to force a branch
	public static function query($branch_id = null) {
        $query = parent::query();

        if(!$branch_id) {
            if(auth()->user()->hasRole('super'))
                return $query;

            $branch_id = auth()->user()->contact->branchContact()->branch_id;
        }        

        $contact_ids = \DB::table('contact_branch')->where('branch_id', $branch_id)->pluck('contact_id')->toArray();
        $query = $query->whereIn('id', $contact_ids);
        return $query;
    }
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function lists()
    {
        return $this->belongsToMany(NewsletterList::class, 'contact_list', 'contact_id', 'list_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_contact');
    }

    public function testimonial()
    {
        if(\Illuminate\Support\Facades\Schema::hasTable('testimonial_contact'))
        {
            return $this->belongsToMany(\Areaseb\Referrals\Models\Testimonial::class, 'testimonial_contact');
        }
        return false;
    }

    public function agent()
    {
        if(\Illuminate\Support\Facades\Schema::hasTable('agent_contact'))
        {
            return $this->belongsToMany(\Areaseb\Agents\Models\Agent::class, 'agent_contact');
        }
        return false;
    }

    public function master() {
        return $this->belongsTo(Master::class, 'id', 'contact_id');
    }

// GETTERS AND SETTERs
    public function getFullnameAttribute()
    {
        return $this->cognome . ' ' . $this->nome;
    }

    public function getRagSocAttribute()
    {
        if($this->company_id)
        {
            return $this->company->rag_soc;
        }
        return null;
    }
    
    public function getDisabilityAttribute()
    {
        $disability = \DB::table('contact_disabled')->where('contact_id', $this->id)->first();
        $disability_type = $disability->disabile_tipo_id ? \DB::table('disabile_tipo')->where('id', $disability->disabile_tipo_id)->first()->nome : null;
        $disability_tool = $disability->disabile_attrezzi_id ? \DB::table('disabile_attrezzi')->where('id', $disability->disabile_attrezzi_id)->first()->nome : null;
        $disability_sitting = $disability->disabile_sedute_id ? \DB::table('disabile_sedute')->where('id', $disability->disabile_sedute_id)->first()->nome : null;
        if($disability->sintesi){
        	$sintesi = 'Sì';
        } else {
        	$sintesi = 'No';
        }
        if($disability->catetere){
        	$catetere = 'Sì';
        } else {
        	$catetere = 'No';
        }
        
        
        return "<b>Tipologia:</b> $disability_type<br>
        		<b>Attrezzo:</b> $disability_tool<br>
        		<b>Altezza:</b> $disability->altezza cm<br>
        		<b>Bacino:</b> $disability->bacino cm<br>
        		<b>Peso:</b> $disability->peso kg<br>
        		<b>Seduta:</b> $disability_sitting<br>
        		<b>Mazzi di sintesi:</b> $sintesi<br>
        		<b>Catetere:</b> $catetere<br>
        		<b>Note:</b> $disability->note<br>";
    }

    public function setNomeAttribute($value)
    {
        $this->attributes['nome'] = ucwords(strtolower($value));
    }

    public function setCognomeAttribute($value)
    {
        $this->attributes['cognome'] = ucwords(strtolower($value));
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }


//SCOPES & FILTERS
    public function scopeUser($query)
    {
        return $query = $query->whereNotNull('user_id');
    }

    public function scopeSubscribed($query)
    {
        return $query = $query->where('subscribed', 1);
    }

    public function scopeIscritti($query, $value)
    {
        return $query = $query->where('subscribed', $value);
    }

    public function scopeOrigine($query, $value)
    {
        return $query = $query->where('origin', $value);
    }

    public function scopeBelongToList($query, $list_id)
    {
        $contactIds = NewsletterList::find($list_id)->contacts()->pluck('contact_id')->toArray();
        return $query = $query->whereIn('id', $contactIds );
    }


    public function dataMaster($contact_id)
    {
        $data = ContactMaster::where('contact_id', $contact_id)->first();

        if($data == null)
            return null;

        switch ($data->tipo_socio) {
            case 4:
                $data->tipo_socio_desc = 'Non socio';
                break;
            case 3:
                $data->tipo_socio_desc = 'Socio aspirante';
                break;
            case 1:
                $data->tipo_socio_desc = 'Socio partner';
                break;
            default:
                $data->tipo_socio_desc = 'Socio professionale';
        }


        switch ($data->disciplina) {
            case 1:
                $data->disciplina_desc = 'Discesa';
                break;
            case 2:
                $data->disciplina_desc = 'Fondo';
                break;
            case 4:
                $data->disciplina_desc = 'Snowboard';
                break;
            default:
                $data->disciplina_desc = 'Snowboard';
        }


        $listSpecialization =  \DB::table('specializations_masters')->select('specializations.nome')
                                                                    ->join('specializations','specializations.id','=','specializations_masters.specialization_id')
                                                                    ->join('masters','masters.id','=','specializations_masters.master_id')
                                                                    ->where('masters.contact_id',$contact_id)
                                                                    ->get();

        $data->specializations = "";
        foreach ($listSpecialization as $value) {
            $data->specializations = $data->specializations != "" ? $data->specializations.', '.$value->nome : $value->nome;
        }

        return $data;
    }

    public function specializations($contact_id)
    {
        return \DB::table('specializations_masters')->where('master_id', $contact_id)->pluck('specialization_id')->toArray();
    }

    public function branch($contact_id)
    {
        return \DB::table('contact_branch')->where('contact_id', $contact_id)->pluck('branch_id')->toArray();
    }

    public function branchContact()
    {
        return \DB::table('contact_branch')->where('contact_id', $this->id)->first();
    }


    public function branchName($contact_id)
    {
        $branch = \DB::table('contact_branch')->join('branches','branches.id','=','contact_branch.branch_id')
                                                ->where('contact_id', $contact_id)
                                                ->first();

        return $branch != null ? $branch->nome : '-';
    }

    public function listBranches()
    {
        return \DB::table('contact_branch')->where('contact_id', $this->id)->pluck('branch_id')->toArray();
    }

	public function isDisabledAttribute() {
		return (\DB::table('contact_disabled')->where('contact_id', $this->id)->first()) ? 'S' : 'N';
	}

    public function isDisabled($contact_id)
    {
        return \DB::table('contact_disabled')->where('contact_id', $contact_id)->first();
    }


    
    public function invoices()
    {
        return Invoice::where('contact_id', $this->id);
    }


    public function getNumberOrder($company_id)
    {
        return $this->table()::where('company_id', $company_id)->get();
    }


    public function getAge($contact_id)
    {
        $student = Contact::where('id', $contact_id)
                                                ->first();
                        
        
        $now = new \DateTime();
        $date = new \DateTime($student->data_nascita);
        return $date->diff($now)->format("%y");
    }



    public function getAvatarAttribute()
    {
        $arr = explode(' ', $this->fullname);
        $letters = '';$count = 0;
        foreach($arr as $value)
        {
            if($count < 2)
            {
                $letters .= trim(strtoupper(substr($value, 0, 1)));
            }
            $count++;
        }
        if( strlen($letters) == 1)
        {
            $letters = trim(strtoupper(substr($arr[0], 0, 2)));
        }

        return '<div class="avatar">'.$letters.'</div>';
    }

    public function getNewsletterInviateAttribute()
    {
        if($this->reports()->exists())
        {
            return $this->reports()->inviate()->count();
        }
        return 0;
    }

    public function getNewsletterAperteAttribute()
    {
        if($this->reports()->exists())
        {
            return $this->reports()->aperte()->count();
        }
        return 0;
    }

    public function getNewsletterClickedAttribute()
    {
        if($this->reports()->exists())
        {
            return $this->reports()->clicked()->count();
        }
        return 0;
    }

    public function getNewsletterStatsAttribute()
    {
        return (object) [
            'inviate' => $this->newsletter_inviate,
            'aperte' => $this->newsletter_aperte,
            'clicks' => $this->newsletter_clicked
        ];
    }

    public function getNewFromSiteAttribute()
    {
        if($this->origin == 'Sito')
        {
            if($this->created_at == $this->updated_at)
            {
                return 'class=table-info';
            }
        }
        return '';
    }

    public function getIntNumberAttribute()
    {
        if($this->cellulare)
        {
            $mobile = str_replace(" ", "", $this->cellulare);
            $mobile = str_replace("-", "", $mobile);
            $mobile = str_replace("/", "", $mobile);
            $mobile = str_replace(".", "", $mobile);
            if(substr($mobile,0,2) == '00')
            {
                $mobile = "+".substr($mobile, 2,-1);
            }
            if($this->nazione == 'IT')
            {
                if(strlen($mobile) == 10)
                {
                    if(substr($mobile,0,1) == '3')
                    {
                        return "+".Country::where('iso2', $this->nazione)->first()->phone_code.$mobile;
                    }
                }
                if(strlen($mobile) == 13)
                {
                    return $mobile;
                }
                return null;
            }
            if(substr($mobile,0,1) == '+')
            {
                return $mobile;
            }
            return "+".Country::where('iso2', $this->nazione)->first()->phone_code.$mobile;
        }
        return null;
    }

    public static function uniquePos()
    {
        return Contact::whereNotNull('pos')->distinct()->pluck('pos', 'pos')->toArray();
    }

    public static function uniqueOrigin()
    {
        $arr = ['Fiera' => 'Fiera', 'Telefono' => 'Telefono', 'Web' => 'Web', 'Visita' => 'Visita', 'Sito' => 'Sito', 'Whatsapp' => 'Whatsapp'];

        if(\Illuminate\Support\Facades\Schema::hasTable('agents'))
        {
            $arr['Agente'] = 'Agente';
        }

        if(\Illuminate\Support\Facades\Schema::hasTable('testimonials'))
        {
            $arr['Testimonial'] = 'Testimonial';
        }

        $dynamic = Contact::whereNotNull('origin')->distinct()->pluck('origin', 'origin')->toArray();
        foreach ($dynamic as $key => $value)
        {
            if(!in_array($value, $arr))
            {
                $arr[$value] = $value;
            }
        }
        return $arr;
    }

    public static function filter($data)
    {

        if($data->get('tipo'))
        {
            if(strpos($data['tipo'], '|'))
            {
                $types = explode('|', $data['tipo']);
                $query = self::whereHas('company', function($query) use($types) {
                                $query->whereIn('id', $types);
                            })->with('company');
            }
            else
            {
                $type = $data['tipo'];
                $query = self::whereHas('company', function($query) use($type){
                    $query->where('client_id', $type);
                })->with('company');
            }
        }
        else
        {
            $query = self::with('company');
        }


        if(auth()->user()->hasRole('testimonial'))
        {
            $ids = \DB::table('testimonial_contact')->where('testimonial_id', auth()->user()->testimonial->id)->pluck('contact_id')->toArray();
            $query = $query->whereIn('id', $ids);
        }

        if(auth()->user()->hasRole('agent'))
        {
            $ids = \DB::table('agent_contact')->where('agent_id', auth()->user()->agent->id)->pluck('contact_id')->toArray();
            $query = $query->whereIn('id', $ids);
        }

        if($data->get('sector'))
        {
            $sector = $data->get('sector');
            $query->whereHas('company', function($query) use($sector) {
                            $query->where('sector_id', $sector);
                        });
        }

        if($data->get('search'))
        {
            $like = '%'.$data['search'].'%';
            $query = $query->where('nome', 'like', $like )
                            ->orWhere('cognome', 'like', $like )
                            ->orWhere('email', 'like', $like )
                            ->orWhere('citta', 'like', $like );
        }

        if($data->get('region'))
        {
            if($data->get('region') != '')
            {
                if(strpos($data['region'], '|'))
                {
                    $regions = explode('|', $data['region']);
                    $query = $query->region( $regions );
                }
                else
                {
                    $query = $query->region( $data['region'] );
                }
            }
        }

        if($data->get('province'))
        {
            if($data->get('province') != '')
            {
                if(strpos($data['province'], '|'))
                {
                    $provinces = explode('|', $data['province']);
                    $query = $query->province( $provinces );
                }
                else
                {
                    $query = $query->province( $data['province'] );
                }
            }
        }

        if($data->get('created_at'))
        {
            if($data->get('created_at') != '')
            {
                $query = $query->created( $data['created_at'] );
            }
        }

        if($data->get('updated_at'))
        {
            if($data->get('updated_at') != '')
            {
                $query = $query->updated( $data['updated_at'] );
            }
        }

        if($data->get('origin'))
        {
            if($data->get('origin') != '')
            {
                $query = $query->origine($data->get('origin'));
            }
        }

        if($data->get('branch'))
        {
            if($data->get('branch') != '' && $data->get('branch') != 'Sede')
            {
                $ids = \DB::table('contact_branch')->where('branch_id', intval($data->get('branch')))->pluck('contact_id')->toArray();
                $query = $query->whereIn('id', $ids);
            }
        }
        
        if($data->get('master'))
        {
            if($data->get('master') != '')
            {
                $query = $query->where('cognome', 'like', '%'.$data->get('master').'%')->where('contact_type_id', 3);
            }
        }



        if(!is_null($data->get('subscribed')))
        {
            $query = $query->iscritti($data->get('subscribed'));
        }

        if($data->get('range'))
        {
            $range = explode(' - ', $data->range);
            $da = Carbon::createFromFormat('d/m/Y', $range[0])->format('Y-m-d');
            $a =  Carbon::createFromFormat('d/m/Y', $range[1])->format('Y-m-d');
            $query = $query->whereBetween('created_at', [$da, $a]);
        }

        if($data->get('list'))
        {
            if($data->get('list') != '')
            {
                $query = $query->belongToList( $data['list'] );
            }
        }


            if(!is_null($data->get('supplier')))
            {
                if(intval($data->get('supplier')))
                {
                    $query->whereHas('company', function($query) {
                                $query->where('supplier', true);
                            });
                }
                else
                {
                    $query->whereHas('company', function($query) {
                                $query->where('supplier', false);
                            });
                }
            }


        if($data->get('sort'))
        {
            $arr = explode('|', $data->sort);
            $field = $arr[0];
            $value = $arr[1];
            $query = $query->orderBy($field, $value);
        }

        return $query;
    }



    public static function createOrUpdate($contact, $data, $user_id = null)
    {
        if(is_null($user_id))
        {
            $user_id = $data['user_id'];
        }
        		
/*		if($data['company_id']){
			$contact = Contact::find($data['company_id'][0])->first();
		}*/
		
		if(!isset($data['nazione'])){
			$data['nazione'] = 'IT';
		}
        $nazione = strtoupper($data['nazione']);
        $provincia = $data['provincia'];
        if(isset($data['provincia']))
        {
            if(strlen($data['provincia']) == 2)
            {
                $provincia = City::provinciaFromSigla( strtoupper($data['provincia']) );
            }
        }
        if(isset($data['pos']))
        {
            $contact->pos = $data['pos'];
        }

        if(isset($data['subscribed']))
        {
            $contact->subscribed = $data['subscribed'];
        }

        $contact->nome = $data['nome'];
        $contact->cognome = $data['cognome'];
        $contact->cellulare = (substr($data['cellulare'], 0, 1) != '+' && $data['cellulare'] != '') ? '+'.Country::getCountryPhone($data['nazione']).$data['cellulare'] : $data['cellulare'];
        $contact->nazione = $data['nazione'];
        $contact->email = $data['email'];
        if(isset($data['contact_type_id']))
            $contact->contact_type_id = $data['contact_type_id'];
		
       	$cf = '';
        if(!isset($data['cod_fiscale']) && isset($data['sesso']) && isset($data['data_nascita']) && isset($data['luogo_nascita'])){
        	
	        list($a, $m, $g) = explode("-", $data['data_nascita']);
			$data_nascita = "$g/$m/$a";

	        //$data_nascita = $request->data_nascita;
	        $nome_url = urlencode($data['nome']);
	        $cognome_url = urlencode($data['cognome']);
	        $luogo_nascita_url = urlencode($data['luogo_nascita']);
        
            $stuff = file("http://webservices.dotnethell.it/codicefiscale.asmx/CalcolaCodiceFiscale?Nome=$nome_url&Cognome=$cognome_url&ComuneNascita=$luogo_nascita_url&DataNascita=$data_nascita&Sesso=".$data['sesso']);
            //dd($cf,$sesso,$data_nascita,$nome_url,$cognome_url,$luogo_nascita_url, $stuff);
                      
            $l = 1;
            foreach ($stuff as $line) {
                $cf = $line;
                if($l == 2){
                    $cf = str_replace("<string xmlns=\"http://webservices.dotnethell.it/CodiceFiscale\">", "", $cf);
                    $cf = str_replace("</string>", "", $cf);
                }
                $l++;
            }
            
        }
		
		
        $contact->cod_fiscale = isset($data['cod_fiscale']) ? $data['cod_fiscale'] : $cf;
        $contact->luogo_nascita = isset($data['luogo_nascita']) ? $data['luogo_nascita'] : null;
        $contact->data_nascita = isset($data['data_nascita']) ? $data['data_nascita'] : null;
        $contact->nickname = isset($data['nickname']) ? $data['nickname'] : null;
        $contact->sesso = isset($data['sesso']) ? $data['sesso'] : null;
        $contact->livello = isset($data['livello']) ? $data['livello'] : null;
        $contact->note = isset($data['note_contact']) ? $data['note_contact'] : null;
        $contact->note_segreteria = isset($data['note_segreteria']) ? $data['note_segreteria'] : null;

        if(isset($data['privacy']) && $data['privacy'] == 1)
            $contact->privacy = 1;
        else
            $contact->privacy = 0;

        $parent = Company::where('id', $data['company_id'])->first();
        if($parent != null && $parent->contacts->first() != null)
            $contact->parent_id = $parent->contacts->first()->id;

        if(isset($data['indirizzo']))
        {
            $contact->indirizzo = $data['indirizzo'];
        }
        if(isset($data['cap']))
        {
            $contact->cap = $data['cap'];
        }
        if(isset($data['citta']))
        {
            $contact->citta = $data['citta'];
            $contact->city_id = City::getCityIdFromData($provincia, $nazione, $data['citta']);
        }
        else
        {
            $contact->city_id = City::getCityIdFromData($provincia, $nazione);
        }
        $contact->provincia = $provincia;

        $contact->nazione = $nazione;
        if($data['nazione'] != 'IT' )
        {
            if(isset($data['lingua']))
            {
                $contact->lingua = $data['lingua'];
            }
            else
            {
                $contact->lingua = 'en';
            }
        }
        $contact->user_id = $user_id;
        $contact->attivo = isset($data['attivo']) ? $data['attivo'] : 'S';
        //dd($data['company_id']);
        $contact->company_id = is_array($data['company_id']) ? $data['company_id'][0] : $data['company_id'];
        $contact->origin = $data['origin'];

        if(isset($data['contact_id']) || $data['_method'] == 'PATCH'){
            $contact->update();         
        } else {
        	$contact->save();
        }
        

        // CASO DISABILE
        if(isset($data['is_disabile']) && $data['is_disabile'] == 1)
        {
            if(ContactDisabled::where('contact_id',$contact->id)->count()==  0){
                $contact_disabled = new ContactDisabled();
                $contact_disabled->disabile_tipo_id = $data['disabile_tipo_id'];
                $contact_disabled->disabile_attrezzi_id = $data['disabile_attrezzi_id'];
                $contact_disabled->altezza = $data['altezza'];
                $contact_disabled->peso = $data['peso'];
                $contact_disabled->bacino = $data['bacino'];
                $contact_disabled->disabile_sedute_id = $data['disabile_sedute_id'];
                $contact_disabled->sintesi = $data['sintesi'];
                $contact_disabled->catetere = $data['catetere'];
                $contact_disabled->note = $data['note'];
                $contact_disabled->contact_id = $contact->id;
                $contact_disabled->save();
            }
            else{
                $contact_disabled = ContactDisabled::where('contact_id',$contact->id)->first();
                $contact_disabled->disabile_tipo_id = $data['disabile_tipo_id'];
                $contact_disabled->disabile_attrezzi_id = $data['disabile_attrezzi_id'];
                $contact_disabled->altezza = $data['altezza'];
                $contact_disabled->peso = $data['peso'];
                $contact_disabled->bacino = $data['bacino'];
                $contact_disabled->disabile_sedute_id = $data['disabile_sedute_id'];
                $contact_disabled->sintesi = $data['sintesi'];
                $contact_disabled->catetere = $data['catetere'];
                $contact_disabled->note = $data['note'];
                $contact_disabled->contact_id = $contact->id;
                $contact_disabled->update();
            }
        }
        else{
            $disabled = ContactDisabled::where('contact_id',$contact->id);
            if($disabled->count() > 0)
                $disabled->delete();
        }
        // CASO MAESTRO
        if(isset($data['contact_type_id']) && $data['contact_type_id'] == 3)
        {
            
            if(ContactMaster::where('contact_id',$contact->id)->count()==  0){
                $contact_master = new ContactMaster();
                $contact_master->color = $data['color'];
                $contact_master->tipo_socio = $data['tipo_socio'];
                $contact_master->disciplina = $data['disciplina'];
                $contact_master->collegio = $data['collegio'];
                $contact_master->contact_id = $contact->id;
                $contact_master->ordine = $data['ordine'];
                $contact_master->save();
            }
            else{
                $contact_master = ContactMaster::where('contact_id',$contact->id)->first();
                $contact_master->color = $data['color'];
                $contact_master->tipo_socio = $data['tipo_socio'];
                $contact_master->disciplina = $data['disciplina'];
                $contact_master->collegio = $data['collegio'];
                $contact_master->contact_id = $contact->id;
                $contact_master->ordine = $data['ordine'];
                $contact_master->update();

            }
                
            if(isset($data['specializzazioni']))
            {
                \DB::table('specializations_masters')->where('master_id', $contact_master->id)->delete();
                foreach ($data['specializzazioni'] as $value) {
                    \DB::table('specializations_masters')->insert(
                        ['master_id' => $contact_master->id, 'specialization_id' => $value]
                    );
                }
            }

        }

        
        if(isset($data['branch_id']))
        {
            \DB::table('contact_branch')->where('contact_id', $contact->id)->delete();
            foreach ($data['branch_id'] as $value) {
                \DB::table('contact_branch')->insert(
                    ['contact_id' => $contact->id, 'branch_id' => $value]
                );
            }
        }

        return $contact;
    }


    public static function cleanDelete($contact)
    {
        if(\DB::table('event_contact')->where('contact_id', $contact->id)->exists())
        {
            \DB::table('event_contact')->where('contact_id', $contact->id)->delete();
        }

        if(\DB::table('contact_list')->where('contact_id', $contact->id)->exists())
        {
            \DB::table('contact_list')->where('contact_id', $contact->id)->delete();
        }

        if(\DB::table('reports')->where('contact_id', $contact->id)->exists())
        {
            \DB::table('reports')->where('contact_id', $contact->id)->delete();
        }

        $contact->delete();

    }
}
