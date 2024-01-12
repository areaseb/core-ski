<?php

namespace Areaseb\Core\Models;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ContactMasterExport implements FromView
{
    public $masters;

    public function __construct($masters)
    {
        $this->masters = $masters;
    }

    public function view(): View
    {
        return view('areaseb::csv.masters', [
            'masters' => $this->masters
        ]);
    }
}
