{!! Form::model($invoice, ['url' => $invoice->url.'/update-saldo', 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'saldoForm']) !!}
    <div class="form-group">
        <label for="name" class="col-form-label">Tipo saldo:</label>
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

{!! Form::close() !!}

{{-- @push('scripts')
    <script>
        console.log('ciao');
        //$('#data_saldo').datetimepicker({ format: 'DD/MM/YYYY' });
    </script>
@endpush --}}
