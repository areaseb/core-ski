<?php

namespace Areaseb\Core\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ClientAnnualRevenueExport implements FromView
{

    public $companies;

    public function __construct($companies)
    {
        $this->companies = $companies;
    }

    public function view(): View
    {
        return view('areaseb::core.accounting.stats.exports.clients',
            [
                'companies' => $this->companies
            ]
        );
    }
}
