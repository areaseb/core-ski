<div class="col-md-6">
    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">Corpo</h3>
        </div>
        <div class="card-body">

            {!! Form::hidden('item_id', null) !!}

            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Prodotto*</label>
                <div class="col-sm-8">
                    {!! Form::select('product_id', $products, null, ['class' => 'form-control select2bs4', 'data-placeholder' => 'Seleziona Prodotto', 'id' => 'products', 'data-fouc', 'style' => 'width:100%']) !!}
                </div>
            </div>
            <input type="hidden" name="codice" class="codice" value="">
            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Descrizione</label>
                <div class="col-sm-8">
                    {!! Form::textarea('descrizione', null, ['class' => 'form-control desc', 'rows' => 2, 'maxlength' => 999]) !!}
                </div>
            </div>
            @if(isset($invoice))
                @php $exemption_id = $invoice->company->exemption_id; @endphp
            @else
                @php $exemption_id = null; @endphp
            @endif
            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Esenzione</label>
                <div class="col-sm-8">
                    {!! Form::select('exemption_id', $exemptions, $exemption_id, ['class' => 'form-control select2bs4', 'data-placeholder' => 'Seleziona Esenzione', 'data-fouc', 'id' => 'esenzione', 'style' => 'width:100%']) !!}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Quantità</label>
                <div class="col-sm-8">
                    <div class="input-group">
                        {!! Form::text('qta', 1, ['class' => 'form-control input-decimal', 'id' => 'qta']) !!}
                        <div class="input-group-append">
                            <span class="input-group-text input-group-text-sm" id="basic-addon2">00.00</span>
                        </div>
                    </div>
                    @include('areaseb::components.add-invalid', ['element' => 'qta'])
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-xl-7">

                        <div class="form-group row">
                            <label class="col-sm-4 col-xl-7 col-form-label">Prezzo*</label>
                            <div class="col-sm-8 col-xl-5">
                                <div class="input-group xl-ml-5">
                                    {!! Form::text('prezzo', null, ['class' => 'form-control input-decimal', 'id' => 'prezzo']) !!}
                                    <div class="input-group-append">
                                        <span class="input-group-text input-group-text-sm" id="basic-addon2">00.00€</span>
                                    </div>
                                </div>
                                @include('areaseb::components.add-invalid', ['element' => 'prezzo'])
                            </div>
                        </div>
                </div>
                <div class="col-sm-12 col-xl-5">

                    <div class="form-group row">
                        <label class="col-sm-4 col-xl-5 col-form-label">IVA*</label>
                        <div class="col-sm-8 col-xl-7">
                            <div class="input-group">
                                {!! Form::text('perc_iva', null, ['class' => 'form-control input-decimal', 'id' => 'perc_iva']) !!}
                                <div class="input-group-append">
                                    <span class="input-group-text input-group-text-sm" id="basic-addon2">00.00%</span>
                                </div>
                            </div>
                            @include('areaseb::components.add-invalid', ['element' => 'perc_iva'])
                        </div>
                    </div>

                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Sconto</label>
                <div class="col-sm-8">
                    <div class="row">
                        <div class="col relative">
                            <div class="input-group">
                                {!! Form::text('sconto1', null, ['class' => 'form-control input-decimal']) !!}
                                <div class="input-group-append">
                                    <span class="input-group-text input-group-text-sm" id="basic-addon2">%</span>
                                </div>
                            </div>
                            <span class="abs plus">+</span>
                        </div>
                        <div class="col relative">
                            <div class="input-group">
                                {!! Form::text('sconto2', null, ['class' => 'form-control input-decimal']) !!}
                                <div class="input-group-append">
                                    <span class="input-group-text input-group-text-sm" id="basic-addon2">%</span>
                                </div>
                            </div>
                            <span class="abs plus">+</span>
                        </div>
                        <div class="col">
                            <div class="input-group">
                                {!! Form::text('sconto3', null, ['class' => 'form-control input-decimal']) !!}
                                <div class="input-group-append">
                                    <span class="input-group-text input-group-text-sm" id="basic-addon2">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="card-footer text-center">
            <button class="btn btn-primary btn-sm btn-block" data-uid="" id="addItem" disabled><i class="fa fa-plus"></i> AGGIUNGI VOCE</button>
        </div>
    </div>
</div>

@push('scripts')
{{-- <script src="{{asset('js/invoices.js')}}"></script> --}}
<script>

var item = [];
var items = [];
var itemsFromDB = ($('textarea#itemsToForm').val() != '') ? JSON.parse($('textarea#itemsToForm').val()) : [];
var itemsChildren = [];

@isset($invoice)
    @if($invoice->company->exemption_id)
        $("select#esenzione").select2('data', {id: '{{$invoice->company->exemption_id}}', text: 'esenti'});
    @endif
@endisset

const getSconti = () => ({
    uno: $('input[name=sconto1]').val() ? $('input[name=sconto1]').val() : 0,
    due: $('input[name=sconto2]').val() ? $('input[name=sconto2]').val() : 0,
    tre: $('input[name=sconto3]').val() ? $('input[name=sconto3]').val() : 0
});

const getPercSconto = (s) => ((1-s)*100).toFixed(2);

const findSconto = (s) => (1-(parseFloat(s.uno)/100))*(1-(parseFloat(s.due)/100))*(1-(parseFloat(s.tre)/100));

const getExtra = (response) => {
    extra = {};
    extra.c_exception = response.exemption_id+"";
    extra.c_s1 = response.s1;
    extra.c_s2 = response.s2;
    extra.c_s3 = response.s3;
    extra.locale = response.lingua;
    extra.nazione = response.nation;
    return extra;
};

const processResult = (response, extra) => {
    $('input#prezzo').val(response.prezzo);
    $('input.codice').val(response.codice);
    $('textarea.desc').val(response.descrizione);
    $('button#addItem').prop('disabled', false);
    if(extra.c_exception != 'null')
    {
        $('input#perc_iva').val(0);
        $('select#esenzione').val(extra.c_exception);
        $('select#esenzione').trigger('change');
    }
    else
    {
        $('input#perc_iva').val(response.perc_iva);
    }
    if(extra.c_s1)
    {
        $('input[name="sconto1"]').val(extra.c_s1);
    }
    if(extra.c_s2)
    {
        $('input[name="sconto2"]').val(extra.c_s2);
    }
    if(extra.c_s3)
    {
        $('input[name="sconto3"]').val(extra.c_s3);
    }
}

const addChildrenItems = (element, extra) => {

    let perc_iva = 0;
    if(extra.c_exception != 'null')
    {
        perc_iva = 0;
    }
    else
    {
        perc_iva = element.perc_iva;
    }

    item = new Item(
            element.product_id,
            element.product.nome,
            element.product.codice,
            element.descrizione,
            element.product.prezzo,
            perc_iva,
            parseInt(element.qta),
            1 - (element.sconto/100),
            element.sconto,
            extra.nazione,
            null, //is_edit
            null, //item_id
        );
    itemsChildren.push(item);
}


class Item
{
    constructor(product_id, nome, codice, descrizione, prezzo, perc_iva, qta, sconto, perc_sconto, esenzione_id, nazione, isEdit, item_id)
    {
        this.uid = Math.random().toString(36).substr(2, 5);
        this.product_id = product_id;
        this.nome = nome;
        this.codice = codice;
        this.descrizione = descrizione;

        this.prezzo = parseFloat(prezzo);
        this.perc_iva = parseInt(perc_iva);
        this.sconto = sconto != 1 ? parseFloat(prezzo)*sconto : null;
        this.perc_sconto = sconto != 1 ? parseFloat(perc_sconto) : null;

        this.qta = parseFloat(qta).toFixed(2);
        this.ivato = (sconto != 1) ? (parseFloat(prezzo)*sconto) * parseFloat(qta) * (parseInt(perc_iva)/100) : parseFloat(prezzo) * parseFloat(qta) * (parseInt(perc_iva)/100);
        this.exemption_id = (esenzione_id==null) ? null : esenzione_id;
        this.nazione = nazione;
        this.item_id = (item_id==null) ? null : item_id;//HERE CHANGE
    }

    subtotal()
    {
        if(this.sconto == null)
        {
            return  (parseFloat(this.prezzo) * parseFloat(this.qta)) + parseFloat(this.ivato);
        }
        return ( parseFloat(this.sconto) * parseFloat(this.qta) ) + parseFloat(this.ivato);
    }

}


const resetItemForm = () => {
    $('#products').select2().val(null).trigger('change');
    $('textarea.desc').val('');
    $('input#prezzo').val('');
    $('input#perc_iva').val('');
    $('input.codice').val('');
    $('input[name="qta"]').val('1.00');
    $('input[name=sconto1]').val('');
    $('input[name=sconto2]').val('');
    $('input[name=sconto3]').val('');
    $('select[name="exemption_id"]').select2().val(null).trigger('change');
    $('input[name=item_id]').val('');

    let btn = $('button#addItem');
    btn.prop('disabled', true);
    btn.html('<i class="fa fa-plus"></i> AGGIUNGI VOCE');
    $('button#save').prop('disabled', false);
    if(btn.hasClass('edit'))
    {
        btn.removeClass('edit');
    }
}

const addItemToTable = (item) => {
    html = '<tr class="prodRowId-'+item.uid+'">';
        html += '<td class="pl-2">'+item.codice+'</td>';
        html += '<td>'+item.qta+'</td>';
        html += '<td>'+item.prezzo.toFixed(2)+'</td>';
        if(item.perc_sconto != null)
        {
            html += '<td>'+(item.perc_sconto.toFixed(2))+'</td>';
        }
        else
        {
            html +='<td></td>';
        }
        html += '<td>'+item.ivato.toFixed(2)+'</td>';
        html += '<td>'+item.subtotal().toFixed(2)+'</td>';
        html += '<td class="pr-2">';
        html += '<a href="#" class="btn btn-sm removeProdRow" id="prodId-'+item.uid+'"><span class="text-danger"><i class="fa fa-trash"></i></span></a>';
        html += '<a href="#" class="btn btn-sm editProdRow" id="prodId-'+item.uid+'"><span class="text-warning"><i class="fa fa-edit"></i></span></a>';
        html += '</td>';
    html += '</tr>';
    $('.table.voci tbody').append(html);
    resetItemForm();
}

const addItemsToTable = (r) => {
    if(Object.entries(r).length !== 0)
    {
        Object.entries(r).forEach(([key, item]) => {
            let newItem = {};
            var sconto = item.sconto == 0 ? 1 : (1-item.sconto/100);
            var perc_sconto = item.sconto == 0 ? 0 : item.sconto;
            newItem = new Item(
                item.product_id,
                item.product.nome,
                item.product.codice,
                item.descrizione,
                item.importo,
                item.perc_iva,
                item.qta,
                sconto,
                perc_sconto,
                item.exception_id,
                nazione,
                true,
                item.id
            );

            items.push(newItem);
            addItemToTable(newItem);
        });
    }
}

let company = null;let extra = {}; let esenzione = null;
let nazione = "{{$nazione ?? null}}";
company = $('select[name="company_id"]').val();

if(company)
{
    axios.get( baseURL+'api/companies/'+company+'/discount-exemption').then(function(r){
        nazione = r.data.nation;
        esenzione = r.data.exemption_id;
    });
}

$('select[name="company_id"]').on('change', function(){
    company = $('select[name="company_id"]').val();
    axios.get( baseURL+'api/companies/'+company+'/discount-exemption').then(function(r){
        nazione = r.data.nation;
        esenzione = r.data.exemption_id;
    });
});

addItemsToTable(itemsFromDB, nazione);

$("#products").on('select2:select', function(){
    let prod_id = $(this).find(':selected').val();

    if($('select[name="company_id"]').val() == ""){
        err("Devi prima selezionare un'azienda");
        resetItemForm();
        return false;
    }

    if($('button#addItem').hasClass('edit'))
    {
        $.get( baseURL+"api/products/"+$(this).find(':selected').val(), function( data ) {
            $('input.codice').val(data.codice);
        });
    }
    else
    {
        axios.get( baseURL+'api/companies/'+company+'/discount-exemption').then(function(resp1){

            //get extra info from company
            extra = getExtra(resp1.data);

            axios.get( baseURL+'api/products/'+prod_id+'/'+extra.locale ).then(function(resp2){

                //show process inf in table
                processResult(resp2.data, extra)

                if(resp2.data.children !== null)
                {
                    axios.get( baseURL+"api/products/"+prod_id+"/children/"+company ).then(function(resp3){

                        //load children
                        resp3.data.forEach(function(element){
                            addChildrenItems(element, extra)
                        });

                    });
                }

            });
        });
    }

});

$("select#esenzione").on('select2:select', function(){
    $.get( baseURL+"api/exemptions/"+$(this).find(':selected').val(), function( iva ) {
        $('input#perc_iva').val(iva);
    });
});


$('button#addItem').on('click', function(e){
    e.preventDefault();

    var prezzo = $('input#prezzo').val();

    if(prezzo == '')
    {
        $('input#prezzo').addClass('is-invalid');
        err('Il campo prezzo è obbligatorio');
        $('input#prezzo').on('focusin', function(){
            $(this).removeClass('is-invalid');
        });
        return false;
    }

    var select = $('#products').select2('data');
    var desc = $('textarea.desc').val();
    var perc_iva = $('input#perc_iva').val();
    var qta = $('input#qta').val() ? $('input#qta').val() : '1.00';
    var sconto = findSconto(getSconti());
    var esenzione_id = $('select[name="exemption_id"]').find(":selected").val() == '' ? null : $('select[name="exemption_id"]').find(":selected").val();
    var perc_sconto = getPercSconto(sconto);
    var codice = $('input.codice').val();

    if($(this).hasClass('edit'))
    {
        var item_id = $('input.item_id').val();
        let newItem = new Item(select[0].id, select[0].text, codice, desc, prezzo, perc_iva, qta, sconto, perc_sconto, esenzione_id, nazione, true, item_id);
        updateItemWhere(newItem, $(this).attr('data-uid'));
        resetItemForm();
    }
    else
    {
        item = new Item(select[0].id, select[0].text, codice, desc, prezzo, perc_iva, qta, sconto, perc_sconto, esenzione_id, nazione, null, null);
        items.push(item);
        addItemToTable(item);
        Object.entries(itemsChildren).forEach(([key, elem]) => {
            items.push(elem);
            addItemToTable(elem);
        });
        itemsChildren = [];
    }
});


const updateItemWhere = (newItem, uid) => {
    Object.entries(items).forEach(([key, elem]) => {
        if(elem.uid == uid)
        {
            if(elem.product_id != newItem.product_id)
            {
                elem.product_id = parseInt(newItem.product_id);
                $('tr.prodRowId-'+uid+' td').eq(0).text($('input[name="codice"]').val());
            }

            if(elem.descrizione != newItem.descrizione)
            {
                elem.descrizione = newItem.descrizione;
            }

            if(elem.qta != newItem.qta)
            {
                elem.qta = parseInt(newItem.qta).toFixed(2);
                $('tr.prodRowId-'+uid+' td').eq(1).text(parseInt(newItem.qta).toFixed(2));
            }

            if(elem.prezzo != newItem.prezzo)
            {
                elem.prezzo = parseFloat(newItem.prezzo).toFixed(2);
                $('tr.prodRowId-'+uid+' td').eq(2).text(parseFloat(newItem.prezzo).toFixed(2));
            }

            if(elem.perc_sconto != newItem.perc_sconto)
            {
                if( isNaN(parseFloat(newItem.perc_sconto)) )
                {
                    elem.perc_sconto = 0.00;
                    $('tr.prodRowId-'+uid+' td').eq(3).text("0.00");

                }
                else
                {
                    elem.perc_sconto = parseFloat(newItem.perc_sconto).toFixed(2);
                    $('tr.prodRowId-'+uid+' td').eq(3).text(parseFloat(newItem.perc_sconto).toFixed(2));
                }
            }

            if(elem.ivato != newItem.ivato)
            {
                elem.ivato = parseInt(newItem.ivato);
                $('tr.prodRowId-'+uid+' td').eq(4).text((newItem.ivato).toFixed(2));
            }

            if(elem.perc_iva != newItem.perc_iva)
            {
                elem.perc_iva = parseInt(newItem.perc_iva);
            }


            elem.ivato = parseInt(newItem.ivato);
            $('tr.prodRowId-'+uid+' td').eq(5).text((newItem.subtotal()).toFixed(2));


        }
    });
}


$('table.table.voci').on('click', 'a.removeProdRow', function(e){
    e.preventDefault();
    var uid = $(this).attr('id').replace('prodId-', '');

    var i = items.filter(item => item.uid == uid)[0];
    if(i.item_id)
    {
        let url = "{{url('invoices-item')}}/"+i.item_id;
        axios.delete(url, {_token:token}).then(response => {
            if(response.data == 'done')
            {
                pass('Voce eliminata');
            }
            else
            {
                err('Impossibile eliminare la voce');
            }
        });
    }

    $(this).parent('td').parent('tr').remove();
    items = items.filter(item => item.uid != uid);
});

$('table.table.voci').on('click', 'a.editProdRow', function(e){
    e.preventDefault();
    var uid = $(this).attr('id').replace('prodId-', '');
    var i = items.filter(item => item.uid == uid)[0];
    $('input[name="codice"]').val(i.codice);
    $('#products').select2().val(i.id).trigger('change');
    $('textarea.desc').val(i.descrizione);
    $('input#perc_iva').val(i.perc_iva);
    $('input#qta').val(parseFloat(i.qta).toFixed(2));
    $('input#prezzo').val(i.prezzo);
    $('input[name="item_id"]').val(i.item_id);//HERE CHANGE

    if(i.exemption_id)
    {
        $('select[name="exemption_id"]').select2().val(i.exemption_id).trigger('change');
    }
    if(i.exemption_id == null)
    {
        $('select[name="exemption_id"]').select2().val(null).trigger('change');
    }

    if(i.perc_sconto)
    {
        $('input[name=sconto1]').val(i.perc_sconto);
    }
    let btn = $('button#addItem');
    btn.prop('disabled', false);
    btn.html('<i class="fa fa-plus"></i> MODIFICA VOCE');
    btn.addClass('edit');
    btn.attr('data-uid', uid);
});



const validate = () => {
    let tipo_doc = $('select[name="tipo_doc"] :selected').val();
    let countErrPa = 0;
    if(tipo_doc === 'Pu')
    {
        let checkPa = {};
        checkPa.pa_n_doc = $('input[name="pa_n_doc"]');
        checkPa.pa_cup = $('input[name="pa_cup"]');
        checkPa.pa_data_doc = $('input[name="pa_data_doc"]');
        checkPa.pa_cig = $('input[name="pa_cig"]');

        $.each(checkPa, function(){
            if($(this).val() == '')
            {
                $(this).addClass('is-invalid');
                countErrPa++;
            }
        })

        if(countErrPa != 0)
        {
            err('Non hai compilato i campi per la Pubblica Amminstazione!');
        }
    }

    let countErrDDT = 0;
    let tipo = $('select[name="tipo"] :selected').val();
    if(tipo_doc === 'D')
    {
        let checkDDT = {};
        checkDDT.ddt_n_doc = $('input[name="ddt_n_doc"]');
        checkDDT.ddt_data_doc = $('input[name="ddt_data_doc"]');

        $.each(checkDDT, function(){
            if($(this).val() == '')
            {
                $(this).addClass('is-invalid');
                countErrDDT++;
            }
        });

        if(countErrDDT != 0)
        {
            err('Non hai compilato i campi per il DDT!');
        }
    }

    if(isEmpty(items))
    {
        err('Impossibile salvare la fattura: non hai caricato nessuna voce.');
        return false;
    }

    const doItemsHaveAtLeastOneExemption = (items) => {

        let item = items.filter(i => i.exemption_id !== null)
        if(item.length)
        {
            return item[0].exemption_id;
        }
        return null;
    }

    data.exemption_id = doItemsHaveAtLeastOneExemption(items);


    if(nazione !="IT")
    {
        let data = {};
        data._token = token;
        data.exemption_id = esenzione;

        axios.post(baseURL+'api/companies/'+company+'/check-vies', data).then((response) => {
            if(response.data.status != "success")
            {
                err(response.data.response);
                return false;
            }
        });
    }

    if((countErrPa+countErrDDT) === 0)
    {
        return true;
    }
    return false;
}



const isEmpty = (obj) => {
    for(var key in obj) {
        if(obj.hasOwnProperty(key))
            return false;
    }
    return true;
}

$('#invoiceForm').on('focusin', 'input.is-invalid', function(){
    $(this).removeClass('is-invalid');
});



$('button#save').on('click', function(e){
    e.preventDefault();
    if(validate())
    {
        $('textarea#itemsToForm').html(JSON.stringify(items));
        $('button[type="submit"]').trigger('click');
    }
    else
    {
       console.log('Validation did not pass');
    }

});



</script>
@endpush
