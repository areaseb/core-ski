<?php

namespace Areaseb\Core\Models;

use Illuminate\Support\Facades\Cache;

class Product extends Primitive
{
    protected $casts = [
        'children' => 'array',
    ];

    public function categories()
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }

    public static function groupedOpt()
    {
        $arr = [];
        if(self::hasManyCategories())
        {
            $categories = Category::categoryOf('Product')->where('id', '!=', 1)->orderBy('nome', 'asc')->get();
        }
        else
        {
            $categories = Category::categoryOf('Product')->orderBy('nome', 'asc')->get();
        }

        foreach($categories as $category)
        {
            $data = [];
            foreach($category->products()->select('codice', 'nome', 'id')->get() as $p)
            {

                if($p->codice)
                {
                    $text = $p->codice;
                    if($p->nome)
                    {
                        $text .= " - ".$p->nome;
                    }
                }
                else
                {
                    $text = $p->nome;
                }

                $data[$p->id] = $text;
            }
            $arr[$category->nome] = $data;
        }
        return $arr;
    }

    public static function default()
    {
        $default = self::where('codice', 'VUOTO')->first();
        if($default)
        {
            return $default->id;
        }
        return self::create(['codice' => 'VUOTO'])->id;
    }

    public static function preventivo()
    {
        $quote_product = self::where('codice', 'PREV')->first();
        if($quote_product)
        {
            return $quote_product->id;
        }
        return self::create(['codice' => 'PREV'])->id;
    }

    public static function bollo()
    {
        $bollo = self::where('codice', 'BOL')->first();
        if(is_null($bollo))
        {
            $bollo = self::where('nome', 'Bollo')->first();
        }
        if(is_null($bollo))
        {
            $bollo = self::create(['codice' => 'BOL', 'nome' => 'Bollo']);
        }
        $category = Category::where('nome', 'Da Categorizzare')->first();
        if(is_null($category))
        {
            $category = Category::create(['nome' => 'Da Categorizzare']);
        }
        $bollo->categories()->save($category);

        return $bollo->id;
    }


    public static function spese()
    {
        $spesa = self::where('codice', 'SPE')->first();
        if(is_null($bollo))
        {
            $spesa = self::where('nome', 'Spese')->first();
        }
        if(is_null($bollo))
        {
            $spesa = self::create(['codice' => 'SPE', 'nome' => 'Spese']);
        }
        $category = Category::where('nome', 'Da Categorizzare')->first();
        if(is_null($category))
        {
            $category = Category::create(['nome', 'Da Categorizzare']);
        }
        $product->categories()->save($category);

        return $spesa->id;
    }



    public static function filter($data)
    {

        if($data->get('category_id'))
        {
            $query = Category::find($data->get('category_id'))->products();
        }
        else
        {
            $query = self::with('categories');
        }


        if($data->get('search'))
        {
            $like = '%'.$data['search'].'%';
            $query = $query->orWhere('nome', 'like', $like )->orWhere('descrizione', 'like', $like )->orWhere('codice', 'like', $like );
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

    public function getTextPeriodAttribute()
    {
        if($this->periodo == 12)
        {
            return 'annuale';
        }
        elseif($this->periodo == 6)
        {
            return 'semestrale';
        }
        elseif($this->periodo == 2)
        {
            return 'bimestrale';
        }
        elseif($this->periodo == 3)
        {
            return 'trimestrale';
        }
    }

    public static function hasManyCategories()
    {
        $hasManyCategories = Cache::remember('hasManyCategories', 60*10, function () {
            if(Category::categoryOf('Product')->count() > 4)
            {
                return true;
            }
            return false;
        });
        return $hasManyCategories;
    }

    public static function hasRecursiveProducts()
    {
        $hasRecursiveProducts = Cache::remember('hasRecursiveProducts', 60*10, function () {
            if(self::whereNotNull('periodo')->count() > 4)
            {
                return true;
            }
            return false;
        });
        return $hasRecursiveProducts;
    }


    public static function haveMonthlyRenewal()
    {
        $haveMonthlyRenewal = Cache::remember('haveMonthlyRenewal', 60*10, function () {
            return self::where('periodo', 1)->pluck('id')->toArray();
        });
        return $haveMonthlyRenewal;
    }

    public static function haveQuarterlyRenewal()
    {
        $haveQuarterlyRenewal = Cache::remember('haveQuarterlyRenewal', 60*10, function () {
            return self::where('periodo', 3)->pluck('id')->toArray();
        });
        return $haveQuarterlyRenewal;
    }

    public static function haveSemsterlyRenewal()
    {
        $haveSemsterlyRenewal = Cache::remember('haveSemsterlyRenewal', 60*10, function () {
            return self::where('periodo', 6)->pluck('id')->toArray();
        });
        return $haveSemsterlyRenewal;
    }

    public static function haveAnnualRenewal()
    {
        $haveAnnualRenewal = Cache::remember('haveAnnualRenewal', 60*10, function () {
            return self::where('periodo', 12)->pluck('id')->toArray();
        });
        return $haveAnnualRenewal;
    }

    public static function haveBiAnnualRenewal()
    {
        $haveBiAnnualRenewal = Cache::remember('haveBiAnnualRenewal', 60*10, function () {
            return self::where('periodo', 24)->pluck('id')->toArray();
        });
        return $haveBiAnnualRenewal;
    }

    public static function haveTriAnnualRenewal()
    {
        $haveTriAnnualRenewal = Cache::remember('haveTriAnnualRenewal', 60*10, function () {
            return self::where('periodo', 36)->pluck('id')->toArray();
        });
        return $haveTriAnnualRenewal;
    }

    public function getNameAttribute()
    {
        if(app()->getLocale() == 'it')
        {
            return $this->nome;
        }
        else
        {
            $locale = app()->getLocale();
            $name = 'name_'.$locale;
            return $this->$name;
        }
    }

    public function getDescriptionAttribute()
    {
        if(app()->getLocale() == 'it')
        {
            return $this->descrizione;
        }
        else
        {
            $locale = app()->getLocale();
            $name = 'description_'.$locale;
            return $this->$name;
        }
    }


}
