<?php

namespace Areaseb\Core\Models;

class Template extends Primitive
{

    //owner of template
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function setContenutoAttribute($value)
    {
        $start = strpos($value, "<table");
        $end = strrpos($value, "table>")+6;
        $this->attributes['contenuto'] = substr($value, $start, ($end-$start));
    }

    public function getBuilderAttribute()
    {
        return url('template-builder/'.$this->id);
    }

    public function getColorAttribute()
    {
        if($this->contenuto)
        {
            $checker = stripos($this->contenuto, 'style="background:');
            if($checker)
            {
                $start =  strpos ( $this->contenuto , ' style="background:');
                $sub = substr($this->contenuto, $start, 40);
                $arr = explode(' style="background:', $sub);
                $lastOcc = strrpos($arr[1], ") ");
                $result = substr($arr[1], 0, 1+$lastOcc);
                return $result;
            }
        }
        return '';
    }

    public static function Default()
    {
        $d = self::where('nome', 'Default')->first();
        if($d)
        {
            return $d;
        }
        return self::create(['nome' => 'Default', 'owner_id' => 1]);
    }

    public static function makeDefault()
    {
        $opts = array('http' => array('header'=> 'Cookie: ' . $_SERVER['HTTP_COOKIE']."\r\n"));
        
        $context = stream_context_create($opts);
       
        $template = self::Default();
        //dd($template->contenuto_html);
        if($template->contenuto_html != null)
            $template->contenuto_html = file_get_contents(url('templates/'.$template->id), false, $context);
       
        $template->save();
    }

    public static function getLastDefaultNewsletter()
    {
        if( Newsletter::where('template_id', self::Default()->id)->exists() )
        {
            return Newsletter::where('template_id', self::Default()->id)->latest()->first()->descrizione;
        }
        return '<b>HTML </b> content';
    }


}
