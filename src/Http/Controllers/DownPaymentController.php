<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{City, Client, Company, Contact, Country, DownPayment};
use App\User;


class DownPaymentController extends Controller
{
    public function index()
    {
        $records = DownPayment::paginate(30);


        $contacts = Contact::select('contacts.nome', 'contacts.cognome','contacts.id')->join('masters','masters.contact_id','=','contacts.id')->get();
        $total = 0;
        foreach ($records as $value) {
            $total = $total + doubleval($value->amount);
        }
        //dd($total);
        $payment_type_id = ['' => '','0' =>'Bonifico', '1' =>'Contanti', '2' =>'Assegno', ];
        return view('areaseb::core.down_payments.index', compact('records', 'contacts','payment_type_id', 'total'));
    }


    public function store(Request $request)
    {
        $res = array();
        try
        {
            $this->validate(request(),[
                    'payment_type_id' => "required",
                    'amount' => "required",
                    'created_at' => "required",
                    'contact_id' => "required"
            ]);

            $dp = new DownPayment;
            $dp->payment_type_id = $request->payment_type_id;
            $dp->amount = $request->amount;
            $dp->created_at = $request->created_at;
            $dp->contact_id = $request->contact_id;
            
            $dp->save();

            $date = date_create(explode('T',$request->created_at)[0]);
            $dp->date =  date_format($date,"d/m/Y");
            $payment_type = 'Bonifico';
            if($dp->payment_type_id == 1)
                $payment_type = 'Contanti';
            if($dp->payment_type_id == 2)
                $payment_type = 'Assegno';

            $dp->payment_type = $payment_type;
            $res['code'] = true;
            $res['message'] = 'OK';
            $res['data'] = $dp;
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


    public function destroy(DownPayment $downpayment)
    {
        try
        {
            $downpayment->delete();
        }
        catch(\Exception $e)
        {
            return "Questo elemento è usato da un'altro modulo";
        }
        return 'done';
    }


}
