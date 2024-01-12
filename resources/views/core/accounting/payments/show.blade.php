@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}invoices">Fatture</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => $invoice->titolo])

@section('content')

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><b>Pagamento Saldo</b><br><p class="text-muted mb-0">Per il saldo totale usa questo box.</p></h3>
                </div>
                {!! Form::model($invoice, ['url' => $invoice->url.'/update-saldo', 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'saldoForm']) !!}
                    <div class="card-body">
                        <div class="form-group">
                            {!! Form::select('tipo_saldo', config('invoice.payment_modes')
                                , null, ['class' => 'form-control', 'data-placeholder' => 'Seleziona Tipo Saldo', 'required']) !!}
                        </div>
                        <div class="form-group">
                            <div class="input-group" id="data_saldo" data-target-input="nearest">
                                @php
                                    $data_saldo = $invoice->data_saldo ? $invoice->data_saldo->format('d/m/Y') : date('d/m/Y');
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

                @if($invoice->saldato)
                    {!! Form::open(['url' => route('invoices.mark-as-unpaid', $invoice->id)]) !!}
                        <button type="submit" class="btn btn-block btn-lg btn-warning"><i class="fa fa-save"></i> Marca come non pagato</button>
                    {!! Form::close() !!}
                @endif

            </div>
        </div>

        @include('areaseb::core.accounting.payments.info-contacts')


            <div class="col">
                <div class="card" style="border:none;">
                    <div class="card-header" style="border-bottom:none;">
                        <h3 class="card-title">Contatti</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group">
                            <div class="col">
                                <div class="card" style="border:none;">
                                    {{-- <div class="card-header" style="border-bottom:none;">
                                        <h3 class="card-title">Contatti</h3>
                                    </div> --}}
                                    @if($invoice->company_id)
	                                    <div class="card-body p-0">
	                                        <div class="list-group">
	                                            @forelse($invoice->company->contacts as $contact)
	                                                <div class="list-group-item list-group-item-action" style="border-right:none;border-left:none;">
	                                                    <div class="d-flex w-100 justify-content-between">
	                                                    <h5 class="mb-1">{{$contact->fullname}}</h5>
	                                                    <small><a href="{{$contact->url}}"><i class="fa fa-eye"></i></a></small>
	                                                    </div>
	                                                </div>
	                                            @empty
	                                                <div class="list-group-item list-group-item-action" style="border-right:none;border-left:none;">
	                                                    Non hai contatti registrati con questa azienda
	                                                </div>
	                                            @endforelse
	                                        </div>
	                                    </div>
	                                @endif
                                </div>
                        </div>
                    </div>
                </div>

                @if($invoice->saldato)
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h2 class="mb-0">{{$invoice->total_formatted}}</h2>
                            <p>Saldato</p>
                        </div>
                    </div>
                @else
                    @if($invoice->payments()->exists())
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h2 class="mb-0">€ {{number_format($invoice->total-$invoice->payments()->sum('amount'), 2, ',', '.')}}</h2>
                                <p>ancora da saldare</p>
                            </div>
                        </div>
                    @else
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h2 class="mb-0">{{$invoice->total_formatted}}</h2>
                                <p>da saldare</p>
                            </div>
                        </div>
                    @endif
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
                    <div class="table-responsive" style="overflow-x:none;">
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
                                    $da_pagare = $invoice->total;
                                @endphp

                                @foreach($invoice->payments()->orderBy('date', 'ASC')->get() as $payment)
                                    <tr>
                                        <td>{{$payment->date->format('d/m/Y')}}</td>
                                        <td>{{config('invoice.payment_modes')[$payment->payment_type]}}</td>
                                        <td>€ {{number_format($payment->amount, 2, ',', '.')}}</td>
                                        <td>
                                            {!! Form::open(['url' => route('invoices.payments.delete', $payment->id), 'method' => 'DELETE']) !!}
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
                @if($invoice->rate && !$invoice->saldato)

                    <div class="card-footer">
                        <div class="table-responsive" style="overflow-x:unset;">
                            <table class="table text-center my-5">
                                @php
                                    $rata = $invoice->total / count(explode(',', $invoice->rate));
                                    $pays = $invoice->payments()->count();
                                    $rates = explode(',', $invoice->rate);
                                @endphp

                                @for($x=0;$x<count($rates)-$pays;$x++)
                                    {!! Form::open(['url' => route('invoices.payments.store', $invoice->id)]) !!}
                                        <tr>
                                            <td style="border-top:none;">
                                                <div class="form-group mb-0">
                                                    <div class="input-group" id="data-{{$x}}" data-target-input="nearest">
                                                        {!! Form::text('data', trim($rates[$x]), ['class' => 'form-control', 'data-target' => '#data-'.$x, 'data-toggle' => 'datetimepicker']) !!}
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
                                                        {!!Form::text('amount', $rata, ['class' => 'form-control input-decimal', 'max' => $da_pagare])!!}
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
                                @endfor

                                {{-- @foreach(explode(',', $invoice->rate) as $r)



                                    {!! Form::open(['url' => route('invoices.payments.store', $invoice->id)]) !!}
                                        <tr>
                                            <td style="border-top:none;">
                                                <div class="form-group mb-0">
                                                    <div class="input-group" id="data" data-target-input="nearest">
                                                        {!! Form::text('data', trim($r), ['class' => 'form-control', 'data-target' => '#data', 'data-toggle' => 'datetimepicker']) !!}
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
                                                        {!!Form::text('amount', $rata, ['class' => 'form-control input-decimal', 'max' => $da_pagare])!!}
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

                                @endforeach --}}
                            </table>
                        </div>
                    </div>

                @else
                    <div class="card-footer">
                        <div class="table-responsive" style="overflow-x:unset;">
                            <table class="table text-center my-5">
                                {!! Form::open(['url' => route('invoices.payments.store', $invoice->id)]) !!}
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

    @include('areaseb::core.accounting.payments.invoice-reference')

    @include('areaseb::core.accounting.payments.notices')


@stop


@section('scripts')
<script>
    $('select[name="tipo_saldo"]').select2({allowClear:true, width: '100%'});
    $('select[name="type"]').select2({placeholder:"Tipo contatto"});
    $('#data_saldo').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data-0').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data-1').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data-2').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data-3').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data-4').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data-5').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data-6').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data-7').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data-8').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data-9').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#date-notice').datetimepicker({ format: 'DD/MM/YYYY' });
</script>
@stop
