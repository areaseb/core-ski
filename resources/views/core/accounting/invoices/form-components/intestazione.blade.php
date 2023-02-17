<div class="col-md-6">
    <div class="card card-outline card-primary">
        {{-- <div class="card-header">
            <h3 class="card-title">Intestazione</h3>
        </div> --}}
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Tipo documento*</label>
                <div class="col-sm-8">
                    @php
                        if(isset($invoice))
                        {
                            $tipo_doc = $invoice->tipo_doc;
                        }
                        else
                        {
                            $tipo_doc = 'Pr';
                        }
                    @endphp

                    {!! Form::select('tipo_doc',
                        [
                            'Pr' => 'Privato',
                            'Pu' => 'Pubblica Amministrazione'
                        ],
                        $tipo_doc, ['class' => 'form-control select-minimal', 'placeholder' => 'Seleziona Tipo Documento', 'required', 'data-fouc']) !!}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Tipo*</label>
                <div class="col-sm-8">
                    {!! Form::select('tipo',
                        [
                            'P' => 'Proforma',
                            'F' => 'Fattura',
                            'R' => 'Ricevuta',
                            'A' => 'Nota di Accredito',
                            'D' => 'DDT',
                            'U' => 'Autofattura'
                        ],
                        null, ['class' => 'form-control select-minimal', 'placeholder' => 'Seleziona Tipo', 'id' => 'tipo', 'required']) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-xl-7">
                    <div class="form-group row">
                        <label class="col-sm-4 col-xl-7 col-form-label">Anno*</label>
                        <div class="col-sm-8 col-xl-5">
                            {!! Form::select('anno',
                                array_combine(range(date("Y"), 2015), range(date("Y"), 2015)),
                                null, ['class' => 'form-control select-minimal', 'id' => 'anno', 'required']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-xl-5">
                    <div class="form-group row">
                        <label class="col-sm-4 col-xl-5 col-form-label">Numero*</label>
                        <div class="col-sm-8 col-xl-7">
                            {!! Form::text('numero', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Data emissione*</label>
                <div class="col-sm-8">
                    <div class="input-group" id="data" data-target-input="nearest">
                        @php
                            if(isset($invoice))
                            {
                                $data = $invoice->data ? $invoice->data->format('d/m/Y') : null;
                            }
                            else
                            {
                                $data = date('d/m/Y');
                            }
                        @endphp
                        {!! Form::text('data', $data, ['class' => 'form-control', 'data-target' => '#data', 'data-toggle' => 'datetimepicker', 'required']) !!}
                       <div class="input-group-append" data-target="#data" data-toggle="datetimepicker">
                           <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                       </div>
                   </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Azienda*</label>
                <div class="col-sm-8">
                    {!! Form::select('company_id',$companies,
                        $selectedCompany ? $selectedCompany : null, ['class' => 'form-control select2bs4', 'data-placeholder' => 'Seleziona Azienda', 'required', 'data-fouc']) !!}
                </div>
            </div>

            @if(!empty($deals) && class_exists("Deals\App\Models\Deal"))
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Trattativa</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            @php
                                $selectedDeal = request('deal');
                                if(is_null($selectedDeal))
                                {
                                    if(isset($invoice))
                                    {
                                        $selectedDeal = \Deals\App\Models\Deal::DealIdFromInvoice($invoice->id);
                                    }
                                }
                            @endphp
                            {!! Form::select('deal_id',$deals, $selectedDeal, ['class' => 'form-control select2bs4', 'data-placeholder' => 'Seleziona Trattativa', 'data-fouc']) !!}
                        </div>
                    </div>
                </div>
            @endif

            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Riferimento</label>
                <div class="col-sm-8">
                    {!! Form::text('riferimento', null, ['class' => 'form-control']) !!}
                </div>
            </div>

        </div>
    </div>
</div>


@push('scripts')
<script>

$('input[name="numero"]').on('focusout', function(){
    let tipo = $(this).find(':selected').val() ? $(this).find(':selected').val() : "F";
    let anno = $('select[name="anno"]').val() ? $('select[name="anno"]').val() : moment().format('YYYY');

    data = {
        _token: "{{csrf_token()}}",
        type: tipo,
        year: anno,
        number: $(this).val()
    };

    $.post("{{url('api/invoices/check')}}", data).done(function( data ) {
        if( parseInt(data) == 1)
        {
            new Noty({
                text: "Numero già usato da un'altra fattura! Cambiato con un valore valido",
                type: 'error',
                theme: 'bootstrap-v4',
                timeout: 2500,
                layout: 'topRight'
            }).show();

            retriveNumero(tipo, anno)

            new Noty({
                text: "Cambiato con un valore valido",
                type: 'success',
                theme: 'bootstrap-v4',
                timeout: 2500,
                layout: 'topRight'
            }).show();

        }
    });

});

$('.select-minimal').select2({minimumResultsForSearch: -1, width:'100%'});
$('#data').datetimepicker({ format: 'DD/MM/YYYY' });
$('#data_saldo').datetimepicker({ format: 'DD/MM/YYYY' });

$('select[name="tipo"]').on('change', function(){
    var tipo = $(this).find(':selected').val() ? $(this).find(':selected').val() : "F";
    var anno = $('select[name="anno"]').val() ? $('select[name="anno"]').val() : moment().format('YYYY');
    retriveNumero(tipo, anno);
    extraDDTFields(tipo);
    notaDiAccredito();

});



const notaDiAccredito = () => {
    let tipo = $('select[name="tipo"]').find(':selected').val();
    let smallExists = $('input[name="riferimento"]').parent('div').find('small').length;
    let rif = $('input[name="riferimento"]')
    if(tipo == 'A')
    {
        if(smallExists == 0)
        {
            rif.parent('div').append('<small class="text-muted">Riferimento della fattura da stornare (numero/anno)</small>');
            $(rif).on('focusin', function(){
                openModalCompanies()
            });
        }
    }
    else
    {
        if(smallExists >= 1)
        {
            rif.parent('div').find('small').remove();
        }
    }
}

notaDiAccredito();

const openModalCompanies = () => {

    $('#invoice-modal').modal('show');
    $('#invoice-modal .modal-header h5').html("Seleziona almeno una Fattura da stornare");

    $('select#invoices').select2({theme: 'bootstrap4',width: '100%', placeholder: 'Cerca fattura'});

    $('button.btn-save-rif').on('click', function(){
        let data = $('select#invoices').select2('data');
        let arr = []; let fatture = '';
        data.forEach(function (i) {
            if(i.text != '')
            {
                arr = i.text.split('-|-');
                fatture += arr[1].trim()+ ' ';
            }
        })
        $('input[name="riferimento"]').val('Con riferimento a: '+fatture);
        $('#invoice-modal').modal('hide');

    });

}

//on change of anno retrive new numero
$('select[name="anno"]').on('change', function(){
    var anno = $(this).find(':selected').val() ? $(this).find(':selected').val() : moment().format('YYYY');
    var tipo = $('select[name="tipo"]').find(':selected').val() ? $('select[name="tipo"]').find(':selected').val() : "F";
    retriveNumero(tipo, anno);
});

const retriveNumero = (tipo, anno) => {

    var url = "{{config('app.url')}}"+"api/invoices/"+tipo+"?anno="+anno;
    var str = window.location.href;
    var arr = str.split('/');
    var id = 0;
    arr.forEach(function(e){
        if(Number.isInteger(parseInt(e)))
        {
            id = e;
        }
    });

    if(id != 0)
    {
        url +="&id="+id;
    }

    $.get( url, function( data ) {
        $('input[name="numero"]').val(data);
        $('select[name="anno"]').val(anno);
    });
};


</script>
@endpush
