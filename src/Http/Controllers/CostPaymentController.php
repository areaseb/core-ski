<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Category, Company, Exemption, Cost, CostPayment, Item, Primitive, Product, Setting, Stat};
use \Carbon\Carbon;

class CostPaymentController extends Controller
{
    public function show(Cost $cost)
    {
        return view('areaseb::core.accounting.payments.costs.show', compact('cost'));
    }

    public function store(Request $request, $cost)
    {
        $cost = Cost::find($cost);

        $total = (float) number_format($cost->totale,2, '.','');
        $payed = (float) number_format($cost->payments()->sum('amount'),2, '.','');
        $amount = (float) number_format($request->amount,2, '.','');
        $pop =  floatval($payed+$amount);
        $diff = (int) ($total - $pop);

        if($total > ($payed+$amount))
        {
            CostPayment::create([
                'cost_id' => $cost->id,
                'amount' => $amount,
                'payment_type' => $request->tipo_saldo,
                'date' => Carbon::createFromFormat('d/m/Y',$request->data)
            ]);
            return back()->with('message', 'Rata aggiunta');
        }
        elseif($diff === 0)
        {
            CostPayment::create([
                'cost_id' => $cost->id,
                'amount' => $amount,
                'payment_type' => $request->tipo_saldo,
                'date' => Carbon::createFromFormat('d/m/Y',$request->data)
            ]);

            $cost->update([
                'data_saldo' => $request->data,
                'saldato' => 1
            ]);

            return back()->with('message', 'Rata aggiunta ed ora la fattura risulta completamente saldata');
        }
        else
        {
            return back()->with('error', 'Rata NON aggiunta. Quantità maggiore della richiesta');
        }
    }

    public function destroy(CostPayment $payment)
    {
        $cost = $payment->cost;
        $payment->delete();
        $cost->update(['saldato' => 0]);

        return back()->with('message', 'Rata eliminata');
    }

}
