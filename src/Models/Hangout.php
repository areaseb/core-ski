<?php

namespace Areaseb\Core\Models;
use Areaseb\Core\Models\Company;
use Areaseb\Core\Models\Contact;
use Illuminate\Database\Eloquent\Model;

class Hangout extends Model
{
    protected $guarded = array();
    protected $table='ritrovi';
    public $timestamps = true;
    protected $fillable = ['*'];

    public function getBranchDesc()
    {
        $ritrovo = Hangout::where('id', $this->id)->first();
        $sedi = Sede::whereIn('id', explode(",",$ritrovo->centro_costo))->get();

        $branchDesc = "";
        foreach ($sedi as $value) {
            $branchDesc = $branchDesc != '' ? $branchDesc.'<br>'.$value->nome : $value->nome;
        }

        return $branchDesc;

    }

}
