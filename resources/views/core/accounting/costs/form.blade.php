<div class="col-md-6">
    <div class="card card-outline card-primary">

        <div class="card-body pt-2 pb-0">

            <div class="form-group">
                <label>Numero</label>
                {!! Form::text('numero', null, ['class' => 'form-control', 'placeholder' => 'Generato dalla fattura elettronica']) !!}
            </div>

            <div class="form-group">
                <label>Fornitore*</label>
                {!! Form::select('company_id', $companies, $selectedCompany, ['class' => 'select2 form-control', 'required']) !!}
            </div>

            <div class="row">

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tipo Costo*</label>
                        {!! Form::select('expense_id', $expenses, $selectedExpense, ['class' => 'select2 form-control', 'required']) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Anno*</label>
                        {!! Form::select('anno',array_combine(range(date("Y"), 2015), range(date("Y"), 2015)),
                                null, ['class' => 'form-control select-minimal', 'id' => 'anno', 'required']) !!}
                    </div>
                </div>

            </div>
            <div class="form-group">
                <label>Data*</label>
                <div class="input-group" id="data" data-target-input="nearest">
                    @php
                        if(isset($cost))
                        {
                            $data = $cost->data ? $cost->data->format('d/m/Y') : null;
                        }
                        else
                        {
                            $data = old('data', date('d/m/Y'));
                        }
                    @endphp
                    {!! Form::text('data', $data, ['class' => 'form-control', 'data-target' => '#data', 'data-toggle' => 'datetimepicker', 'required']) !!}
                   <div class="input-group-append" data-target="#data" data-toggle="datetimepicker">
                       <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                   </div>
               </div>
            </div>

            <div class="form-group">
                <label>Data Scadenza</label>
                <div class="input-group" id="data_scadenza" data-target-input="nearest">
                    @php
                        if(isset($cost))
                        {
                            $data_scadenza = $cost->data_scadenza ? $cost->data_scadenza->format('d/m/Y') : null;
                        }
                        else
                        {
                            $data_scadenza = \Carbon\Carbon::today()->lastOfMonth()->format('d/m/Y');
                        }
                    @endphp
                    {!! Form::text('data_scadenza', $data_scadenza, ['class' => 'form-control', 'data-target' => '#data_scadenza', 'data-toggle' => 'datetimepicker', 'required']) !!}
                   <div class="input-group-append" data-target="#data_scadenza" data-toggle="datetimepicker">
                       <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                   </div>
               </div>
            </div>

            <div class="form-group">
                <label>Data Ricezione</label>
                <div class="input-group" id="data_ricezione" data-target-input="nearest">
                    @php
                        if(isset($cost))
                        {
                            $data_ricezione = $cost->data_ricezione ? $cost->data_ricezione->format('d/m/Y') : null;
                        }
                        else
                        {
                            $data_ricezione = \Carbon\Carbon::today()->lastOfMonth()->format('d/m/Y');
                        }
                    @endphp
                    {!! Form::text('data_ricezione', $data_ricezione, ['class' => 'form-control', 'data-target' => '#data_ricezione', 'data-toggle' => 'datetimepicker']) !!}
                   <div class="input-group-append" data-target="#data_ricezione" data-toggle="datetimepicker">
                       <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                   </div>
               </div>
            </div>


        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="card card-outline card-danger">
        <div class="card-header">
            <h3 class="card-title">Costi</h3>
        </div>
        <div class="card-body">


            <div class="form-group">
                <label>Imponibile*</label>
                <div class="input-group">
                    {!! Form::text('imponibile', null, ['class' => 'form-control input-decimal']) !!}
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2">00.00€</span>
                    </div>
                </div>
                @include('areaseb::components.add-invalid', ['element' => 'imponibile'])
            </div>


            <div class="form-group">
                <label>Iva €</label>
                <div class="input-group">

                    {!! Form::text('iva', null, ['class' => 'form-control', 'id' => 'ivato']) !!}
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2">00.00€</span>
                    </div>
                </div>
            </div>


            <div class="form-group">
                <label>Totale*</label>
                <div class="input-group">
                    {!! Form::text('totale', null, ['class' => 'form-control input-decimal']) !!}
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2">00.00€</span>
                    </div>
                </div>
                @include('areaseb::components.add-invalid', ['element' => 'totale'])
            </div>

            <div class="form-group">
                <label>Netto a pagare</label>
                <div class="input-group">
                    {!! Form::text('netto', null, ['class' => 'form-control input-decimal']) !!}
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2">00.00€</span>
                    </div>
                </div>
                @include('areaseb::components.add-invalid', ['element' => 'totale'])
            </div>


            <div class="form-group">
                <label>Rateazione</label>
                {!! Form::text('rate', null, ['class' => 'form-control ti', 'data-role' => "tagsinput"]) !!}
            </div>

            <div class="form-group">
                <label>Saldato</label>
                {!! Form::select('saldato',[0 => 'No', 1 => 'Sì'],
                        null, ['class' => 'form-control select-minimal', 'id' => 'anno', 'required']) !!}
            </div>

        </div>
    </div>
</div>
<div class="col-sm-6 offset-sm-3">
    <div class="card">
        <button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm"><i class="fa fa-save"></i> Salva</button>
    </div>
</div>

@section('scripts')
<script src="{{asset('plugins/tagsinput/tagsinput.min.js')}}"></script>
<script>

    $('input.ti').tagsinput('items');
    $('.select2').select2({width: '100%'})
    $('select[name="company_id"]').select2({placeholder:"Seleziona un'azienda"});
    $('select[name="expense_id"]').select2({placeholder:"Seleziona una spesa"});
    $('#data').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data_ricezione').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#data_scadenza').datetimepicker({ format: 'DD/MM/YYYY' });
</script>
@stop
