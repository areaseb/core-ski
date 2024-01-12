<?php

namespace Areaseb\Core\Models;
use Areaseb\Core\Models\Company;
use Areaseb\Core\Models\Contact;
use Illuminate\Database\Eloquent\Model;

class Housing extends Model
{
    protected $guarded = array();
    protected $table='alloggi';
    public $timestamps = true;
    protected $fillable = ['*'];


    public function getBranchDesc()
    {
        $alloggio = Housing::where('id', $this->id)->first();
        $sedi = Sede::whereIn('id', explode(",",$alloggio->centro_costo))->get();

        $branchDesc = "";
        foreach ($sedi as $value) {
            $branchDesc = $branchDesc != '' ? $branchDesc.'<br>'.$value->nome : $value->nome;
        }

        return $branchDesc;

    }


}
