<?php

namespace Areaseb\Core\Models;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ContactExport implements FromView
{
    public $contacts;

    public function __construct($contacts)
    {
        $this->contacts = $contacts;
    }

    public function view(): View
    {
        return view('areaseb::csv.contacts', [
            'contacts' => $this->contacts
        ]);
    }
}
