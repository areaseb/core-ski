<?php

namespace Areaseb\Core\Models;

use Areaseb\Core\Models\{Sede, ContactDisabled, CompanyBranches, ContactBranches};
use Areaseb\Core\Models\Fe\EuVat;
use \Carbon\Carbon;

class Company extends Primitive
{
    protected $casts = [
        'note' => 'array'
    ];
    
    public static function query() {
        $query = parent::query();
        
        if(!auth()->user()->hasRole('super')){
        	$user_branch = auth()->user()->contact->branchContact()->branch_id;
        	$contact_ids = \DB::table('company_branch')->where('branch_id', $user_branch)->pluck('company_id')->toArray();
        	$query = $query->whereIn('id', $contact_ids);
        	return $query;
        }		
        
		return $query;
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class)->orderBy('cognome');
    }

    public function listContacts()
    {
        $listContacts = $this->hasMany(Contact::class);
        foreach ($listContacts as $value) {
            $value->isDisabile = ContactDisabled::where('contact_id', $value->id)->count() > 0;
        }
        return $listContacts;
    }

    public function branches()
    {
        return $this->hasMany( CompanyBranches::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function costs()
    {
        return $this->hasMany(Cost::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_company');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public static function sedi($company_id)
    {
        return Sede::where('company_id', $company_id)->get();
    }

    public static function branch($company_id)
    {
        return Sede::where('company_id', $company_id)->pluck('id')->toArray();
    }


    public function listBranches()
    {
        return CompanyBranches::where('company_id', $this->id)->pluck('branch_id')->toArray();
    }


    public function exemption()
    {
        if($this->exemption_id)
        {
            return $this->belongsTo(Exemption::class);
        }
        return false;
    }

    public function testimonial()
    {
        if(\Illuminate\Support\Facades\Schema::hasTable('testimonial_company'))
        {
            return $this->belongsToMany(\Areaseb\Referrals\Models\Testimonial::class, 'testimonial_company');
        }
        return false;
    }

    public function agent()
    {
        if(\Illuminate\Support\Facades\Schema::hasTable('agent_company'))
        {
            return $this->belongsToMany(\Areaseb\Agents\Models\Agent::class, 'agent_company');
        }
        return false;
    }



// RECURSIVE
    public function children()
    {
        return $this->hasMany(Company::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Company::class, 'parent_id', 'id');
    }

    public function getMotherAttribute()
    {
        if ($this->parent_id)
        {
            return $this->parent->mother;
        }
        return $this;
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function allChildrenIds()
    {
        $children_companies = collect();
        foreach ($this->children as $child_company)
        {
            $children_companies->push($child_company->id);
            $children_companies = $children_companies->merge($child_company->allChildrenIds());
        }

        return $children_companies->toArray();
    }

    public function allChildrenStudentIds()
    {
        $arr = [];
        $companiesIds = $this->allChildrenIds();
        $companies = self::whereIn('id', $companiesIds)->get();
        foreach($companies as $company)
        {
            $contacts = $company->contacts()->whereHas('clients', function($query){
                $query->where('id', Client::Student()->id);
            })->pluck('id')->toArray();

            $arr = array_merge($arr, $contacts);
        }

        return $arr;
    }


    public function getNumOrder($branch_id)
    {
        //dd($this->id);
        return Contact::select('contact_branch.*')
                        ->Join('contact_branch', 'contact_branch.contact_id', '=', 'contacts.id')
                        ->where('contacts.contact_type_id', 3)
                        ->where('contact_branch.branch_id',$branch_id)
                        ->count() + 1;  

    }


    public function getCountChildrenAttribute()
    {
        $count = 0;
        foreach($this->children as $c1)
        {
            $count++;
            if($c1->children()->exists())
            {
                foreach($c1->children as $c2)
                {
                    $count++;
                    if($c2->children()->exists())
                    {
                        foreach($c2->children as $c3)
                        {
                            $count++;
                        }
                    }
                }
            }
        }
        return $count;
    }



    public function setNationAttribute($value)
    {
        $this->attributes['nation'] = $value;
        if($value == 'IT')
        {
            $this->attributes['lang'] = strtolower($value);
        }
        else
        {
            $this->attributes['lang'] = 'en';
        }
    }

    public function setAddressAttribute($value)
    {
        $this->attributes['address'] = ucfirst($value);
    }


    public function setCfAttribute($value)
    {
        $this->attributes['cf'] = strtoupper($value);
    }

    public function setLatAttribute($value)
    {
        if($value)
        {
            $this->attributes['lat'] = floatval($value);
        }
    }

    public function setLngAttribute($value)
    {
        if($value)
        {
            $this->attributes['lng'] = floatval($value);
        }
    }

    public function setPivaAttribute($value)
    {
        $sub = substr($value, 0, 2);
        if(!is_numeric($sub))
        {
            $this->attributes['piva'] = filter_var($value,FILTER_SANITIZE_NUMBER_INT);
        }
        $this->attributes['piva'] = $value;
    }

    public function getNoteListAttribute()
    {
        if($this->notes()->exists())
        {
            $list = '';
            foreach($this->notes as $note)
            {
                $list .= "<p class='mb-0'>".$note->created_at->format('d/m/Y')."<br>".$note->description."<p>";
            }
            return $list;
        }
        return null;
    }

    public function setZipAttribute($value)
    {
        $this->attributes['zip'] = str_pad(trim($value), 5, '0', STR_PAD_LEFT);
    }

    public function getAvatarAttribute()
    {
        $arr = explode(' ', $this->rag_soc);
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

    public function getProvAttribute()
    {
        return City::siglaFromProvincia($this->province);
    }


    public function getAllMailsAttribute()
    {
        $arr[0] = $this->email;
        $arr[1] = $this->email_ordini;
        $arr[2] = $this->email_fatture;

        $emails = [];
        foreach($arr as $key => $value)
        {
            if(is_null($value))
            {
                unset($arr[$key]);
            }
            else
            {
                $emails[] = trim($value);
            }
        }

        $emails = array_unique($emails);

        return implode('|', $emails);
    }

    public function getCleanPivaAttribute()
    {
        if($this->private)
        {
            return '00000000000';
        }
        if(strpos($this->piva, 'IT') !== false)
        {
            return str_replace('IT','', $this->attributes['piva']);
        }
        return $this->attributes['piva'];
    }

    public function getSdiAttribute()
    {
        if($this->private)
        {
            return '0000000';
        }

        if($this->nation != 'IT')
        {
            return 'XXXXXXX';
        }

        return $this->attributes['sdi'];
    }

    public function getNewFromSiteAttribute()
    {
        if($this->contacts()->exists())
        {
            $contact = $this->contacts()->first();

            if($contact->origin == 'Sito')
            {
                if($contact->created_at == $contact->updated_at)
                {
                    return 'class=table-info';
                }
            }
        }
        return '';
    }

    public function getSiglaProvinciaAttribute()
    {
        if($this->city_id)
        {
            return City::find($this->city_id)->sigla_provincia;
        }
        return $this->provincia;
    }

    public function getIsItalianAttribute()
    {
        if($this->nation == 'IT')
        {
            return true;
        }
        return false;
    }

    public function getIsEuAttribute()
    {
        $c = Country::where('iso2', $this->nation)->first();
        if($c)
        {
            if($c->is_eu)
            {
                return true;
            }
            return false;
        }
        return false;
    }


    public function getInvoiceEmailAttribute()
    {
        if($this->email_fatture)
        {
            return $this->email_fatture;
        }
        return $this->email;
    }

    public function getScontoAttribute()
    {
        return (1-((1-($this->s1/100))*(1-($this->s2/100))*(1-($this->s3/100))))*100;
    }

    public function getIntMobileAttribute()
    {
        if($this->mobile)
        {
            $mobile = str_replace(" ", "", $this->mobile);
            $mobile = str_replace("-", "", $mobile);
            $mobile = str_replace("/", "", $mobile);
            $mobile = str_replace(".", "", $mobile);
            if(substr($mobile,0,2) == '00')
            {
                $mobile = "+".substr($mobile, 2,-1);
            }
            if($this->nation == 'IT')
            {
                if(strlen($mobile) == 10)
                {
                    if(substr($mobile,0,1) == '3')
                    {
                        return "+".Country::where('iso2', $this->nation)->first()->phone_code.$mobile;
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
            return "+".Country::where('iso2', $this->nation)->first()->phone_code.$mobile;
        }
        return null;
    }

    public function getIntPhoneAttribute()
    {
        if($this->phone)
        {
            if($this->nation != "" || !is_null($this->nation))
            {
                if($this->nation == 'IT')
                {
                    return '+39'.$this->phone;
                }
                else
                {
                    $prefix = '';
                    $c = Country::where('iso2', $this->nation)->first();
                    if($c)
                    {
                        $prefix = "+".$c->phone_code;
                    }
                    return $prefix.$this->phone;
                }
            }
            else
            {
                return $this->phone;
            }
        }
        return null;
    }


    public static function uniqueOrigin()
    {
        $arr = ['Email'=>'Email', 'Fiera' => 'Fiera', 'Telefono' => 'Telefono', 'Web' => 'Web', 'Visita' => 'Visita', 'Sito' => 'Sito', 'Whatsapp' => 'Whatsapp'];
        if(\Illuminate\Support\Facades\Schema::hasTable('agents'))
        {
            $arr['Agente'] = 'Agente';
        }

        if(\Illuminate\Support\Facades\Schema::hasTable('testimonials'))
        {
            $arr['Testimonial'] = 'Testimonial';
        }

        $dynamic = Company::whereNotNull('origin')->distinct()->pluck('origin', 'origin')->toArray();
        foreach ($dynamic as $key => $value)
        {
            if(!in_array($value, $arr))
            {
                $arr[$value] = $value;
            }
        }
        return $arr;
    }


// SCOPES
    public function scopeSupplier($query)
    {
        $query = $query->where('supplier', true);
    }

    public function scopeSettore($query, $value)
    {
        $query = $query->where('sector_id', $value);
    }

//FILTERS
    public static function filter($data)
    {
        if($data->has('tipo') && $data->get('tipo') && $data->get('tipo') != 'Tipo')
        {
            $query = self::where('client_id', $data['tipo'])->with('client', 'sector');
        }
        else
        {
            $query = self::with('client', 'sector');
        }

        //FILTRO IN BASE ALLO STATO
        if($data->has('status') && $data->get('status') != 'Stato')
        {
            $query = $query->where('active', intval($data->get('status')));
        }

        if(auth()->user()->hasRole('testimonial'))
        {
            $ids = \DB::table('testimonial_company')->where('testimonial_id', auth()->user()->testimonial->id)->pluck('company_id')->toArray();
            $query = $query->whereIn('id', $ids);
        }

        if((auth()->user()->hasRole('agent')) && (!auth()->user()->hasRole('super'))  && (!auth()->user()->hasRole('fatturazione')) )
        {
            $ids = \DB::table('agent_company')->where('agent_id', auth()->user()->agent->id)->pluck('company_id')->toArray();
            $query = $query->whereIn('id', $ids);
        }


        if(request()->has('sector') && request()->get('sector') && $data->get('sector') != 'Categoria')
        {
            $query = $query->settore(request('sector'));
        }

        //dd($data->get('region'));
        if($data->get('region'))
        {
            if($data->get('region') != '' && $data->get('region') != 'Regione')
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
            if($data->get('province') != '' && $data->get('province') != 'Provincia')
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


        if(!is_null($data->get('supplier')))
        {
            if($data->get('supplier') == '1')
            {
                $query = $query->where('supplier', 1);
            }
            elseif($data->get('supplier') == '0')
            {
                $query = $query->where('supplier', 0);
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


        if($data->get('id'))
        {
            if($data->get('id') != '')
            {
                $query = $query->where('id', $data['id']);
            }
        }


        if($data->get('from'))
        {
            $da = Carbon::parse($data->from);
            $a = Carbon::parse($data->to);
            $query = $query->whereBetween('created_at', [$da, $a]);
        }


        if($data->get('sort'))
        {
            $arr = explode('|', $data->sort);
            $field = $arr[0];
            $value = $arr[1];
            $query = $query->orderBy($field, $value);
        }
        
        if($data->get('search') && $data->get('search') != '')
        {   
        	if(auth()->user()->contact->branchContact()){
        		$user_branch = auth()->user()->contact->branchContact()->branch_id;
		    	$company_ids = \DB::table('company_branch')->where('branch_id', $user_branch)->pluck('company_id')->toArray();
		    	$contact_ids = \DB::table('contact_branch')->where('branch_id', $user_branch)->pluck('contact_id')->toArray();
        	} else {
        		$company_ids = Company::where('active', 1)->pluck('id')->toArray();
		    	$contact_ids = Contact::where('attivo', 1)->pluck('id')->toArray();
        	}  	
			
	    	
            $query = $query->where(function($qu) use ($data, $company_ids){
	            				$qu->where(function($que) use ($data){
	            					$que->where('rag_soc', 'like', '%'.$data->get('search').'%')
			            				->orWhere('email', 'like', '%'.$data->get('search').'%')
			            				->orWhere('phone', 'like', '%'.$data->get('search').'%')
			            				->orWhere('mobile', 'like', '%'.$data->get('search').'%')
			            				->orWhere('nickname', 'like', '%'.$data->get('search').'%');
	            				})
	            				->whereIn('id', $company_ids);            				
            				})            				
            				->orWhereHas('contacts', function($qu) use($data, $contact_ids) {
            					$qu->where(function($que) use ($data){
            						$que->where('nome', 'like', '%'.$data->get('search').'%')
											->orWhere('cognome', 'like', '%'.$data->get('search').'%')
											->orWhere('email', 'like', '%'.$data->get('search').'%')
				            				->orWhere('cellulare', 'like', '%'.$data->get('search').'%')
				            				->orWhere('nickname', 'like', '%'.$data->get('search').'%');
            					})
            					->whereIn('id', $contact_ids);							
							});            						
        }
        
        if($data->get('disabled') && $data->get('disabled') != 'Disabile')
        {
        	$contact_ids = ContactDisabled::pluck('contact_id')->toArray();
        	$company_ids = array();
        	
        	foreach($contact_ids as $cid){
        		$company_ids[] = Contact::findOrFail($cid)->company->id;
        	}
        
        	if($data->get('disabled') == 1){
        		$query = $query->whereIn('id', $company_ids);
        	} elseif($data->get('disabled') == 2) {
        		$query = $query->whereNotIn('id', $company_ids);
        	}
        }
        
	    	
        return $query;


    }

}
