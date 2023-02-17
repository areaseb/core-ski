<?php

namespace Areaseb\Core\Http\Controllers;

use Areaseb\Core\Models\Exemption;

class ExemptionController extends Controller
{
    public function getIva(Exemption $exemption)
    {
        return $exemption->perc;
    }
}
