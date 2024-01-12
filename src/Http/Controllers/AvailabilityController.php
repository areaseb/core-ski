<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{City, Client, Company, Contact, Country, Availability, Sede, ContactMaster};
use App\User;


class AvailabilityController extends Controller
{
    public function index()
    {
        $availabilities = Availability::where('contact_id', auth()->user()->contact->id)->paginate(30);
        return view('areaseb::core.availabilities.index', compact('availabilities'));
    }
    
    public function avaiList()
    {
    	$id = request()->id;
        $availabilities = Availability::where('contact_id', $id)->get();
        return json_encode($availabilities);
    }


    public function getListByContact($contact_id)
    {
        $availabilities = Availability::where('contact_id', $contact_id)->paginate(30);
        $branches = [''=>'']+Sede::pluck('nome', 'id')->toArray();
        return view('areaseb::core.companies.index', compact('availabilities','branches'));
    }

    public function store(Request $request)
    {
        $res = array();
        try
        {
            $this->validate(request(),[
                    'data_start' => "required",
                    'data_end' => "required",
                    'branch_id' => "required",
                    'contact_id' => "required"
            ]);


            $contact = ContactMaster::where('contact_id', $request->contact_id)->first();

            $isDisponibile = $contact->isDisponibile($request->contact_id, $request->data_start, $request->data_end);
            if(!$isDisponibile){
                $res['code'] = false;
                $res['message'] = 'Maestro già impegnato in questo intervallo temporale!';
                $res['data'] = null;
                echo json_encode($res);
                return;
            }
            $availability = new Availability;
            $availability->data_start = $request->data_start;
            $availability->data_end = $request->data_end;
            $availability->branch_id = $request->branch_id;
            $availability->contact_id = $request->contact_id;
            
            $availability->save();

            $availability->branch_desc = Sede::where('id',  $availability->branch_id)->first()->nome;
            $availability->data_start = date('d/m/Y', strtotime($availability->data_start));
            $availability->data_end = date('d/m/Y', strtotime($availability->data_end));

            $res['code'] = true;
            $res['message'] = 'OK';
            $res['data'] = $availability;
            echo json_encode($res);   
        }
        catch(\Exception $e)
        {
            $res['code'] = false;
            $res['message'] = 'Operazione fallita!';
            $res['data'] = $e->getMessage();
            echo json_encode($res);
        }
       
    }


    public function destroy(Availability $availability)
    {
        try
        {
            $availability->delete();
        }
        catch(\Exception $e)
        {
            return "Questo elemento è usato da un'altro modulo";
        }
        return 'done';
    }
}
