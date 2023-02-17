<?php

namespace Areaseb\Core\Http\Controllers;

use App\User;
use Areaseb\Core\Models\{Calendar, City, Client, Company, Contact, Country, Setting};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PhonecallController extends Controller
{

//webhook/calls - GET
    public function test()
    {
        return 'demo webhook test.';
    }

//webhook/calls - POST
    public function testPost(Request $request)
    {
        //return $request->input();
        return response('It works', 200);
    }

    public function company($phone)
    {
        $company = $this->getCompany($phone);
        return view('areaseb::core.phonecalls.show', compact('company'));
    }

    public function webhook()
    {
        $tel = request('tel');
        $answered = request('ansewered');
        $terminated = request('terminated');
    }

    private function getCompany($tel)
    {
        $contact = Contact::where('cellulare', $tel)->first();
        if($contact)
        {
            if($contact->company)
            {
                return $contact->company;
            }
        }

        $company = Company::where('phone', $tel)->orWhere('mobile', $tel)->first();
        if($company)
        {
            return $company;
        }

        return null;
    }

}
