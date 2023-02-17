<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $guarded = array();

    public $timestamps = false;

    protected $casts = [
        'fields' => 'array'
    ];

    public function getUrlAttribute()
    {
        return config('app.url').'settings/'.$this->id;
    }

    public function getModelAttribute()
    {
        $emails = ['Trattative', 'Fatture', 'Preventivi'];

        if(in_array($this->attributes['model'], $emails) )
        {
            return 'Email '.$this->attributes['model'];
        }
        return $this->attributes['model'];
    }

    public function getCountFieldsAttribute()
    {
        if($this->fields)
        {
            return count($this->fields);
        }
        return 0;
    }

    public static function defaultTestEmail($emails = null)
    {
        if(is_null($emails))
        {
            $settings = self::where('model', 'Newsletter')->first()->fields;
            if($settings)
            {
                if(isset($settings['default_test_email']))
                {
                    return explode(';', $settings['default_test_email']);
                }
            }
            return null;
        }
        return explode(';', $emails);
    }

    public static function defaultSendFrom()
    {
        $settings = self::where('model', 'Newsletter')->first()->fields;
        if($settings)
        {
            if(isset($settings['invia_da_email']))
            {
                if(isset($settings['invia_da_nome']))
                {
                    return [
                        'name' => $settings['invia_da_nome'],
                        'address' => $settings['invia_da_email'],
                    ];
                }

                return [
                    'name' => config('app.name'),
                    'address' => $settings['invia_da_email'],
                ];
            }
        }
        return [
            'name' => config('app.name'),
            'address' => auth()-user()->email,
        ];
    }


    public static function newsletter()
    {
        $newsletter = Cache::remember('newsletter', 60*10, function () {
            return (object) self::where('model', 'Newsletter')->first()['fields'];
        });
        return $newsletter;
    }

    public static function fe()
    {
        $fe = Cache::remember('fe', 60*10, function () {
            return (object) self::where('model', 'Fe')->first()['fields'];
        });
        return $fe;
    }

    public static function feIsSet()
    {
        if(self::fe()->connettore != '')
        {
            return true;
        }
        return false;
    }

    public static function base()
    {
        $base = Cache::remember('base', 60*24*7, function () {
            return (object) self::where('model', 'Base')->first()['fields'];
        });
        return $base;
    }

    public static function socials()
    {
        $social = Cache::remember('social', 60*24*7, function () {
            return (object) self::where('model', 'Social')->first()['fields'];
        });
        return $social;
    }


    public static function emailTrattative()
    {
        $trattativeSetting = Cache::remember('trattativeSetting', 60*24*7, function () {
            if(Schema::hasTable('deals'))
            {
                if(self::where('model', 'Trattative')->exists())
                {
                    return self::where('model', 'Trattative')->first()['fields'];
                }
                $locale = [];
                foreach(self::ActiveLangsArray() as $iso => $text)
                {
                    $locale[$iso] = config('deals.'.$iso).config('app.name');
                    $locale[$iso.'_title'] = config('deals.'.$iso.'_title');
                    $locale[$iso.'_subject'] = config('deals.'.$iso.'_subject');
                }
                Setting::create(['model' => 'Trattative', 'fields' => $locale]);
                return $locale;
            }
            return null;
        });
        return $trattativeSetting;
    }

    public static function emailPreventivi()
    {
        $preventiviSetting = Cache::remember('preventiviSetting', 60*24*7, function () {
            if(Schema::hasTable('killer_quotes'))
            {
                if(self::where('model', 'Preventivi')->exists())
                {
                    return self::where('model', 'Preventivi')->first()['fields'];
                }
                $locale = [];
                foreach(self::ActiveLangsArray() as $iso => $text)
                {
                    $locale[$iso] = config('killerquote.'.$iso);
                    $locale[$iso.'_subject'] = config('killerquote.'.$iso.'_subject');
                }
                self::create(['model' => 'Preventivi', 'fields' => $locale]);
                return $locale;
            }
            return null;
        });
        return $preventiviSetting;
    }



    public static function emailFatture()
    {
        $fattureSetting = Cache::remember('fattureSetting', 60*24*7, function () {
            if(self::where('model', 'Fatture')->exists())
            {
                return self::where('model', 'Fatture')->first()['fields'];
            }
            $locale = [];
            foreach(self::ActiveLangsArray() as $iso => $text)
            {
                $locale[$iso] = config('core.email.'.$iso);
                $locale[$iso.'_title'] = config('core.email.'.$iso.'_title');
                $locale[$iso.'_subject'] = config('core.email.'.$iso.'_subject');
            }
            Setting::create(['model' => 'Fatture', 'fields' => $locale]);
            return $locale;
        });
        return $fattureSetting;
    }


    public static function langs()
    {
        $lingue = Cache::remember('lingue', 60*24*7, function () {
            $l = self::where('model', 'Lingue')->first();
            if($l)
            {
                return $l['fields'];
            }
            return [];
        });
        return $lingue;
    }

    public static function lang($locale)
    {
        $langs = self::langs();
        foreach($langs as $lang)
        {
            if($lang['LANG_ISO'] === $locale)
            {
                return $lang['LANG_NAME'];
            }
        }
        return '';
    }

    public static function ActiveLangs()
    {
        $arr = [];
        foreach(self::langs() as $lang)
        {
            if($lang['LANG_ACTIVE'])
            {
                $arr[] = $lang['LANG_ISO'];
            }
        }

        if(count($arr) > 1)
        {
            unset($arr[0]);
            return $arr;
        }
        return [];
    }

    public static function ActiveLangsArray()
    {
        $arr = [];
        foreach(self::langs() as $lang)
        {
            if($lang['LANG_ACTIVE'])
            {
                $arr[$lang['LANG_ISO']] = $lang['LANG_NAME'];
            }
        }
        return $arr;
    }

/**
 * return one or all istances of SMTP configuration
 * @param  [int] $id [id smtp]
 * @return [arr]     [single or multiple instance of smtp configurations]
 */
    public static function smtp($id = null)
    {
        if(is_null($id))
        {
            return (object) self::where('model', 'SMTP')->first()['fields'];
        }

        $count = 0;
        foreach(self::where('model', 'SMTP')->first()['fields'] as $smtp)
        {
            if($count == $id)
            {
                return $smtp;
            }
            $count++;
        }
    }


    public static function validSmtp($id)
    {
        return self::smtp($id)['MAIL_HOST'];
    }

    public static function smtpPluck()
    {
        $arr = [];
        foreach(self::where('model', 'SMTP')->first()['fields'] as $smtp)
        {
            if($smtp['MAIL_FROM_NAME'] != '')
            {
                $arr[] = $smtp['MAIL_FROM_NAME'] .' - ' .  $smtp['MAIL_FROM_ADDRESS'] . ' - ' .  $smtp['MAIL_HOST'];
            }
        }

        if(count($arr) == 0)
        {
            return [0 => 'Default'];
        }

        return $arr;
    }


//DASHBOARD
    public static function dashboard()
    {
        if(self::where('model', 'Dashboard')->exists())
        {
            $homeDashboard = Cache::remember('homeDashboard', 60*24*7, function () {
                return (object) self::where('model', 'Dashboard')->first()['fields'];
            });
            return $homeDashboard;
        }
        (new \Areaseb\Core\Http\Controllers\SettingController)->updateDashboardSetting();
        return (object) self::where('model', 'Dashboard')->first()['fields'];
    }


    public function areFieldsFilled()
    {
        foreach($this->fields as $key => $value)
        {
            if($value != '')
            {
                return true;
            }
        }
        return false;
    }

    public function getAddress()
    {
        if($this->areFieldsFilled())
        {
            $html = $this->fields['rag_soc'].'<br>';
            $html .= $this->fields['indirizzo'].'<br>';
            $html .= $this->fields['cap'].', '.$this->fields['citta'].' '.$this->fields['provincia'].' '.$this->fields['nazione'].'<br>';
            return $html;
        }
        return 'My Company, <br> Via indirizzo 7, 12345 Città (AA) IT';
    }

    public static function DefaultLogo()
    {
        $logo = Cache::remember('logo', 60*24*7, function () {
            if(self::base()->logo_img != "")
            {
                return asset('storage/settings/'.self::base()->logo_img);
            }
            return asset('img/AdminLTELogo.png');
        });
        return $logo;
    }

    public static function FatturaLogo()
    {
        $fattura_logo = Cache::remember('fattura_logo', 60*24*7, function () {
            if(self::base()->logo_fattura_img != "")
            {
                return asset('storage/settings/'.self::base()->logo_fattura_img);
            }
            return '';
        });
        return $fattura_logo;
    }

    public static function FatturaFooterImg()
    {
        $fattura_footer_img = Cache::remember('fattura_footer_img', 60*24*7, function () {
            if(self::base()->footer_img != "")
            {
                return asset('storage/settings/'.self::base()->footer_img);
            }
            return '';
        });
        return $fattura_footer_img;
    }


    public static function NewsletterLogo()
    {
        $newsletter_logo_img = Cache::remember('newsletter_logo_img', 60*24*7, function () {
            if(self::newsletter()->default_img_logo != "")
            {
                return asset('storage/settings/'.self::newsletter()->default_img_logo);
            }
            return '';
        });
        return $newsletter_logo_img;
    }



}
