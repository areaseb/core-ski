<?php

namespace Areaseb\Core\Models;

use App\User;

class NewsletterList extends Primitive
{
    protected $table = 'lists';
	
	public static function query() {
        $query = parent::query();
        
        if(!auth()->user()->hasRole('super')){
        	$user_branch = auth()->user()->contact->branchContact()->branch_id;
        	$contact_ids = \DB::table('contact_branch')->where('branch_id', $user_branch)->pluck('contact_id')->toArray();
        	$users = Contact::whereIn('id', $contact_ids)->pluck('user_id')->toArray();
        	$query = $query->whereIn('owner_id', $users);
        	return $query;
        }		
        
		return $query;
    }
    
    //a list might have many Contact
    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_list', 'list_id', 'contact_id');
    }

    //owner of list
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function getDirectoryAttribute()
    {
        return 'lists';
    }

    public function getCountContactsAttribute()
    {
        return $this->contacts()->count();
    }


}
