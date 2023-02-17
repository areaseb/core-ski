<?php

namespace Areaseb\Core\Models;

use App\User;

class NewsletterList extends Primitive
{
    protected $table = 'lists';

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
