<div class="col-md-6">
    <div class="card card-outline card-danger">
        <div class="card-header">
            <h3 class="card-title">Dettagli</h3>
        </div>
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Pagamento*</label>
                <div class="col-sm-8">
                	@php
                		if(!isset($invoice)){
                			$pagam = 'RIDI';
                		} else {
                			$pagam = $invoice->pagamento;
                		} 
                	@endphp
                    {!! Form::select('pagamento', config('invoice.payment_types'), null, ['class' => 'form-control select2bs4', 'data-placeholder' => 'Seleziona Tipo Pagamento', 'id' => 'pagamento', 'required']) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-xl-7">
                    <div class="form-group row">
                        <label class="col-sm-4 col-xl-7 col-form-label">Tipo Saldo</label>
                        <div class="col-sm-8 col-xl-5">
                            {!! Form::select('tipo_saldo', config('invoice.payment_modes'), null, ['class' => 'form-control select2bs4', 'data-placeholder' => 'Seleziona Tipo Saldo']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-xl-5">
                    <div class="form-group row">
                        <label class="col-sm-4 col-xl-5 col-form-label">Split Payment</label>
                        <div class="col-sm-8 col-xl-7">
                            {!! Form::select('split_payment', [0=>'No',1=>"Sì"], null, ['class' => 'custom-select']) !!}
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($invoice))

                <div class="row">
                    <div class="col-sm-12 col-xl-7">
                        <div class="form-group row">
                            <label class="col-sm-4 col-xl-7 col-form-label">Data Saldo</label>
                            <div class="col-sm-8 col-xl-5">
                                <div class="input-group" id="data_saldo" data-target-input="nearest">
                                    @php
                                        if(isset($invoice))
                                        {
                                            $data_saldo = $invoice->data_saldo ? $invoice->data_saldo->format('d/m/Y') : null;
                                        }
                                        else
                                        {
                                            $data_saldo = null;
                                        }
                                    @endphp
                                    {!! Form::text('data_saldo', $data_saldo, ['class' => 'form-control fs14', 'data-target' => '#data_saldo', 'data-toggle' => 'datetimepicker']) !!}
                                    <div class="input-group-append" data-target="#data_saldo" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                               </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-5">
                        <div class="form-group row">
                            <label class="col-sm-4 col-xl-5 col-form-label">Scadenza</label>
                            <div class="col-sm-8 col-xl-7">
                                <div class="input-group" id="data_scadenza" data-target-input="nearest">
                                    @php
                                        if(isset($invoice))
                                        {
                                            $data_scadenza = $invoice->data_scadenza ? $invoice->data_scadenza->format('d/m/Y') : null;
                                        }
                                        else
                                        {
                                            $data_scadenza = null;
                                        }
                                    @endphp
                                    {!! Form::text('data_scadenza', $data_scadenza, ['class' => 'form-control fs14', 'data-target' => '#data_scadenza', 'data-toggle' => 'datetimepicker']) !!}
                                    <div class="input-group-append" data-target="#data_scadenza" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                               </div>
                           </div>
                        </div>
                    </div>
                </div>

            @else

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Data Saldo</label>
                    <div class="col-sm-8">
                        <div class="input-group" id="data_saldo" data-target-input="nearest">
                            @php
                                if(isset($invoice))
                                {
                                    $data_saldo = $invoice->data_saldo ? $invoice->data_saldo->format('d/m/Y') : null;
                                }
                                else
                                {
                                    $data_saldo = null;
                                }
                            @endphp

                            {!! Form::text('data_saldo', $data_saldo, ['class' => 'form-control', 'data-target' => '#data_saldo', 'data-toggle' => 'datetimepicker']) !!}
                           <div class="input-group-append" data-target="#data_saldo" data-toggle="datetimepicker">
                               <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                           </div>
                       </div>
                    </div>
                </div>

            @endif



            <div class="row">
                <div class="col-sm-12 col-xl-7">

                    <div class="form-group row">
                        <label class="col-sm-4 col-xl-7 col-form-label">Spese d'incasso</label>
                        <div class="col-sm-8 col-xl-5">
                            <div class="input-group xl-ml-5">
                                {!! Form::text('spese', null, ['class' => 'form-control input-decimal']) !!}
                                <div class="input-group-append">
                                    <span class="input-group-text input-group-text-sm" id="basic-addon2">00.00 €</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-sm-12 col-xl-5">

                    <div class="form-group row">
                        <label class="col-sm-4 col-xl-6 col-form-label">% rit. acconto</label>
                        <div class="col-sm-8 col-xl-6">
                            <div class="input-group">
                                {!! Form::text('perc_ritenuta', null, ['class' => 'form-control input-decimal']) !!}
                                <div class="input-group-append">
                                    <span class="input-group-text input-group-text-sm" id="basic-addon2">00.00 %</span>
                                </div>
                            </div>
                            @include('areaseb::components.add-invalid', ['element' => 'ritenuta'])
                        </div>
                    </div>

                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Rateazione</label>
                <div class="col-sm-8">
                    {!! Form::text('rate', null, ['class' => 'form-control ti', 'data-role' => "tagsinput", 'placeholder' => date('d/m/Y') ]) !!}
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
	$('#pagamento').select2().val('{{$pagam}}').trigger('change');
</script>
<script src="{{asset('plugins/tagsinput/tagsinput.min.js')}}"></script>
<script>
    $('input.ti').tagsinput('items');
    $('select[name="pagamento"]').on('change', function(){
    	if($('select[name="pagamento"]').val() != null){
	        if($('select[name="pagamento"]').val().includes('RB'))
	        {
	            $('input[name="spese"]').val('3.80');
	        }
	        else if($('select[name="pagamento"]').val() == 'BO3P' || $('select[name="pagamento"]').val() == 'BO5P')
	        {
	            $('input.ti').tagsinput('add', '21/07/2020');
	            $('input.ti').tagsinput('add', '31/07/2020');
	        }
	        else
	        {
	            $('input[name="spese"]').val('');
	        }
	    }
    });
</script>
@endpush
