@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}costs">Acquisti</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => $cost->numero])

@section('content')

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><b>Pagamento Saldo</b><br><p class="text-muted mb-0">Per il saldo totale usa questo box.</p></h3>
                </div>
                {!! Form::model($cost, ['url' => route('costs.updateSaldo', $cost->id), 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'saldoForm']) !!}
                    <div class="card-body">
                        <div class="form-group">
                            <div class="input-group" id="data_saldo" data-target-input="nearest">
                                @php
                                    if(is_string($cost->data_saldo))
                                    {
                                        if(strpos($cost->data_saldo, '-') !== false)
                                        {
                                            $data_saldo = \Carbon\Carbon::parse($cost->data_saldo)->format('d/m/Y');
                                        }
                                        elseif(strpos($cost->data_saldo, '/') !== false)
                                        {
                                            $data_saldo = $cost->data_saldo;
                                        }
                                        else
                                        {
                                            $data_saldo = $cost->data_saldo->format('d/m/Y');
                                        }

                                    }
                                    elseif($cost->data_saldo)
                                    {
                                        $data_saldo = $cost->data_saldo->format('d/m/Y');
                                    }
                                    else
                                    {
                                        $data_saldo = date('d/m/Y');
                                    }
                                @endphp
                                {!! Form::text('data_saldo', $data_saldo, ['class' => 'form-control', 'data-target' => '#data_saldo', 'data-toggle' => 'datetimepicker']) !!}
                               <div class="input-group-append" data-target="#data_saldo" data-toggle="datetimepicker">
                                   <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                               </div>
                           </div>
                       </div>

                   </div>
                   <div class="card-footer p-0">
                       <button type="submit" class="btn btn-block btn-lg btn-success"><i class="fa fa-save"></i> Salva</button>
                   </div>
                {!! Form::close() !!}
            </div>
        </div>


        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recapiti</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                      <li class="list-group-item d-flex justify-content-between align-items-center"><b>Ragion Sociale</b> <span><a href="{{route('companies.show',$cost->company->id )}}">{{$cost->company->rag_soc}}</a></span></li>
                      <li class="list-group-item d-flex justify-content-between align-items-center"><b>Email</b> <span>{{$cost->company->email}}</span></li>
                      @if(!is_null($cost->company->phone) || !is_null($cost->company->mobile)) <li class="list-group-item d-flex justify-content-between align-items-center"><b>Telefono</b> <span>{{$cost->company->phone ?? $cost->company->mobile}}</span></li> @endif
                      @if($cost->company->address) <li class="list-group-item d-flex justify-content-between align-items-center"><b>Indirizzo</b> <span>{{$cost->company->address}}</span></li> @endif
                      @if($cost->company->province) <li class="list-group-item d-flex justify-content-between align-items-center"><b>Provincia</b> <span>{{$cost->company->province}}</span></li> @endif
                      @if($cost->company->contacts()->exists())
                          @php
                            $contact = $cost->company->contacts()->first();
                          @endphp
                          <li class="list-group-item d-flex justify-content-between align-items-center"><b>Contatto</b> <span>{{$contact->fullname}}</span></li>
                      @endif
                    </ul>
                </div>
            </div>
        </div>



            <div class="col">
                <div class="card" style="border:none;">
                    <div class="card-header" style="border-bottom:none;">
                        <h3 class="card-title">Stato pagamento</h3>
                    </div>
                    <div class="card-body p-0">

                        @if($cost->saldato)
                            <div class="small-box bg-success mb-0">
                                <div class="inner">
                                    <h2 class="mb-0">{{$cost->totale_formatted}}</h2>
                                    <p>Saldato</p>
                                </div>
                            </div>
                        @else
                            @if($cost->payments()->exists())
                                <div class="small-box bg-warning mb-0">
                                    <div class="inner">
                                        <h2 class="mb-0">€ {{number_format($cost->totale - $cost->payments()->sum('amount'), 2, ',', '.')}}</h2>
                                        <p>ancora da saldare</p>
                                    </div>
                                </div>
                            @else
                                <div class="small-box bg-danger mb-0">
                                    <div class="inner">
                                        <h2 class="mb-0">{{$cost->totale_formatted}}</h2>
                                        <p>da saldare</p>
                                    </div>
                                </div>
                            @endif
                        @endif

                    </div>
                </div>

                @if($cost->rate)
                    <div class="card" style="border:none;">
                        <div class="card-header" style="border-bottom:none;">
                            <h3 class="card-title">Rate Accordate</h3>
                        </div>
                        <div class="card-body p-0">

                            @php

                                if(strpos($cost->rate, ',' ) !== false)
                                {
                                    $rate = explode(',', $cost->rate);
                                }
                                elseif(strpos($cost->rate, ';' ) !== false)
                                {
                                    $rate = explode(';', $cost->rate);
                                }


                                $n_rate = count($rate);

                                $total = number_format($cost->totale, 2, '.', '');
                                $amount_rata = number_format($total / $n_rate, 2, '.', '');
                                $amount_payed = 0;

                            @endphp

                            @foreach($rate as $value)

                                @php
                                    $scadenza_rata = \Carbon\Carbon::createFromFormat('d/m/Y', trim($value))->format('d/m/Y');
                                @endphp

                                <li class="list-group-item">
                                    <span class="font-weight-semibold"><b>{{$scadenza_rata}}</b></span>
                                    <div class="ml-auto">
                                        @if($loop->last)
                                            &euro; {{number_format($total - $amount_payed , 2, ',', '.')}}
                                        @else
                                            &euro; {{number_format($amount_rata, 2, ',', '.')}}
                                        @endif
                                    </div>
                                </li>

                                @php
                                    $amount_payed += $amount_rata;
                                @endphp

                            @endforeach
                        </div>
                    </div>
                @endif


            </div>
        </div>
    </div>



    <div class="row">
        <div class="col">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Rateazione</h3>
                </div>
                <div class="card-body p-0">


                    <div class="table-responsive">
                        <table class="table text-center mb-0">
                            <thead>
                                <tr>
                                    <th style="border-top:none;">Data</th>
                                    <th style="border-top:none;">Tipo di pagamento</th>
                                    <th style="border-top:none;">Importo</th>
                                    <th style="border-top:none;"></th>
                                </tr>
                            </thead>
                            <tbody>

                                @php
                                    $da_pagare = $cost->total;
                                @endphp

                                @foreach($cost->payments as $payment)
                                    <tr>
                                        <td>{{$payment->date->format('d/m/Y')}}</td>
                                        <td>{{config('invoice.payment_modes')[$payment->payment_type]}}</td>
                                        <td>€ {{number_format($payment->amount, 2, ',', '.')}}</td>
                                        <td>
                                            {!! Form::open(['url' => route('costs.payments.delete', $payment->id), 'method' => 'DELETE']) !!}
                                                <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                    @php
                                        $da_pagare -= $payment->amount;
                                    @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>


                </div>

                @if(!$cost->saldato)
                    <div class="card-footer">
                        <div class="table-responsive" style="overflow-x:unset;">
                            <table class="table text-center my-5">
                                {!! Form::open(['url' => route('costs.payments.store', $cost->id)]) !!}
                                    <tr>
                                        <td style="border-top:none;">
                                            <div class="form-group mb-0">
                                                <div class="input-group" id="data" data-target-input="nearest">
                                                    {!! Form::text('data', date('d/m/Y'), ['class' => 'form-control', 'data-target' => '#data', 'data-toggle' => 'datetimepicker']) !!}
                                                   <div class="input-group-append" data-target="#data" data-toggle="datetimepicker">
                                                       <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                   </div>
                                               </div>
                                           </div>
                                        </td>
                                        <td style="border-top:none;">
                                            <div class="form-group text-left mb-0">
                                                {!! Form::select('tipo_saldo', config('invoice.payment_modes')
                                                    , 'B', ['class' => 'form-control', 'data-placeholder' => 'Seleziona Tipo Saldo', 'required']) !!}
                                            </div>
                                        </td>
                                        <td style="border-top:none;">
                                            <div class="form-group mb-0">
                                                <div class="input-group">
                                                    {!!Form::text('amount', null, ['class' => 'form-control input-decimal', 'max' => $da_pagare])!!}
                                                    <div class="input-group-append">
                                                        <span class="input-group-text input-group-text-sm">00.00</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="border-top:none;">
                                            <div class="form-group mb-0">
                                                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> Inserisci</button>
                                            </div>
                                        </td>
                                    </tr>
                                {!! Form::close() !!}
                            </table>
                        </div>
                    </div>
                @endif


            </div>
        </div>
    </div>

    {{-- @include('areaseb::core.accounting.payments.invoice-reference')

    @include('areaseb::core.accounting.payments.notices') --}}


@stop


@section('scripts')
<script>
    $('select[name="tipo_saldo"]').select2({allowClear:true, width: '100%'});
    $('select[name="type"]').select2({placeholder:"Tipo contatto"});
    $('#data_saldo').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#date-notice').datetimepicker({ format: 'DD/MM/YYYY' });
</script>
@stop
