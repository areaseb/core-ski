<?php

namespace Areaseb\Core\Models;

use App\User;
use Areaseb\Core\Models\{Template, Contact};

class Newsletter extends Primitive
{
	
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
    	
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function lists()
    {
        return $this->belongsToMany(NewsletterList::class, 'list_newsletter', 'newsletter_id', 'list_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function getTemplateAttribute()
    {
        return Template::find($this->template_id);
    }

    public function addTrackingAndPersonalize($identifier, $contact)
    {

        if($this->template->id == Template::Default()->id)
        {
            $content = str_replace('%%%contact.fullname%%%', $contact->fullname, $this->template->contenuto_html);
        }
        else
        {
            $content = str_replace('%%%contact.fullname%%%', $contact->fullname, $this->template->contenuto);
        }

        $unsub = config('app.url').'unsubscribe?r='.$identifier;
        $content = str_replace('%%%unsubscribe%%%', $unsub, $content);

        if(strpos($content, 'class="trackable"') !== false)
        {
            $content = $this->addLinkTrackers($identifier, $content);
        }

        return $content.'<img src="'.url('tracker?r='.$identifier).'"/>';
    }

    public function addLinkTrackers($identifier, $content)
    {
        $dom = new \DOMDocument;
        $dom->loadHTML($content);

        $xpath = new \DOMXPath($dom);$count = 0;
        foreach ($xpath->evaluate("//a[@class='trackable']") as $node) {
            $originalLink = $node->getAttribute('href');
            $fullLink = $originalLink;

            if(strpos($originalLink, '../') === 0)
            {
                $fullLink = str_replace('../',config('app.url'), $originalLink);
            }

            $links[$count]['original'] = $originalLink;
            $links[$count]['with_tracker'] = config('app.url') . 'track?redirect=' . $fullLink . '&tracker=' . $identifier . '&link=' . $count;
            $count++;
        }

        foreach($links as $link)
        {
            $content = str_replace($link['original'], $link['with_tracker'], $content);
        }

        return $content;
    }

}
