<?php

namespace Areaseb\Core\Models;

use \Carbon\Carbon;
use Areaseb\Renewals\Models\RenewalItem;

class Item extends Primitive
{
    public $timestamps = false;

//an item belongs to an invoice
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

//an item has one product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

//an item might have an exemption
    public function exemption()
    {
        return $this->belongsTo(Exemption::class);
    }

//an item might have an exemption
    public function renewalItem()
    {
        if(class_exists('Areaseb\Renewals\Models\RenewalItem'))
        {
            return $this->hasOne(RenewalItem::class);
        }
        return false;
    }


//GETTER

    public function getDateInAttribute()
    {
        if($this->data_in)
        {
            return Carbon::parse($this->data_in);
        }
    }

    public function getDateOutAttribute()
    {
        if($this->data_out)
        {
            return Carbon::parse($this->data_out);
        }
    }


    public function getImportoFormattedAttribute()
    {
        return $this->fmt($this->importo);
    }

    public function getImportoDecimalAttribute()
    {
        return $this->decimal($this->importo);
    }

    public function getTotaleRigaAttribute()
    {
        return $this->importo*$this->qta * (1-($this->sconto)/100);
    }

    public function getTotaleRigaFormattedAttribute()
    {
        return $this->fmt($this->totale_riga);
    }

    public function getTotaleRigaDecimalAttribute()
    {
        return $this->decimal($this->totale_riga);
    }

    public function getIvaFormattedAttribute()
    {
        return $this->fmt($this->iva);
    }

    public function getIvaDecimalAttribute()
    {
        return $this->decimal($this->iva);
    }

    public function getIsSpesaAttribute()
    {
        $spese_id = Category::where('nome', 'Spese')->first()->id;
        return $this->product->categories()->where('category_id', $spese_id)->exists();
    }

    public function getIsBolloAttribute()
    {
        if($this->product_id === Product::bollo())
        {
            return true;
        }
        return false;
    }


//SCOPES

    public function scopeDefault($query)
    {
        $query = $query->where('product_id', Product::default());
    }

    public function scopeAnno($query, $value)
    {
        $query = $query->whereYear('data', $value);
    }

}
