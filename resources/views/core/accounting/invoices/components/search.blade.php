{!! Form::open(['url' => $url, 'method' => 'get', 'id' => 'formFilterInvoices']) !!}
    <div class="row">

        @if(request()->input())
            <div style="float: left;width:87px;" class="print_yes">
                <div class="form-group">
                	@php
                		$data = date('d/m/Y');
                	@endphp
                    <a href="{{url('invoices')}}?tipo=R&range={{urlencode("$data").'+-+'.urlencode("$data")}}" class="btn btn-success" id="refresh" title="reset ricerca"><i class="fa fa-redo"></i> Reset</a>
                </div>
            </div>
        @endif
		
		<div class="col-12 col-md-1 col-sm-1">
            <div class="form-group">
                {!!Form::text('numero', isset($_GET['numero']) ? $_GET['numero'] : null, ['id' => 'numero', 'class' => 'form-control', 'placeholder' => 'n. doc.'])!!}
            </div>
        </div>
        
        <div class="col-12 col-md-1 col-sm-1">
            <div class="form-group">
                {!!Form::select('tipo', [''=>'']+config('invoice.types'), isset($_GET['tipo']) ? $_GET['tipo'] : null, ['id' => 'tipo'])!!}
            </div>
        </div>

        <div class="col-12 col-md-1 col-sm-1">
            <div class="form-group">
                <div class="input-group">
                {!! Form::select('company',
                    [''=>'']+Areaseb\Core\Models\Company::orderBy('rag_soc', 'ASC')->pluck('rag_soc', 'id')->toArray(),
                    request('company'),['class' => 'select2Company']) !!}
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-1 col-sm-1">
            <div class="form-group">
                <div class="input-group">
                {!! Form::select('contact',
                    [''=>'']+$contacts,
                    request('contact'),['class' => 'select2Contact']) !!}
                </div>
            </div>
        </div>

        <div class="col-12 col-md-1 col-sm-1">
            <div class="form-group">
                <select id="saldato" name="saldato">
                    <option @if(!request()->has('saldato')) selected @endif></option>
                    <option value="1" @if(request('saldato') == '1') selected @endif >Sì</option>
                    <option value="0" @if(request('saldato') == '0') selected @endif >No</option>
                </select>
            </div>
        </div>
        
        <div class="col-12 col-md-1 col-sm-1">
            <div class="form-group">
                {!!Form::select('tipo_pag', [''=>'']+config('invoice.payment_modes'), isset($_GET['tipo_pag']) ? $_GET['tipo_pag'] : null, ['id' => 'tipo_pag'])!!}
            </div>
        </div>

        <div class="col-12 col-md-2 col-sm-2">
            <div class="form-group">
                <div class="row">
                    <div class="col-6">
                        {!!Form::select('anno', [''=>'', date('Y') => date('Y'), date('Y')-1 => date('Y')-1, date('Y')-2 => date('Y')-2,  date('Y')-3 => date('Y')-3 ], request('anno'), ['id' =>'anno'])!!}
                    </div>
                    <div class="col-6">
                        {!!Form::select('mese', [''=>'']+__('dates.months_arr'), request('mese'),['id' =>'mese'])!!}
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12 col-md-2 col-sm-2">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                    </div>
                    <input type="text" name="range" class="form-control float-right" id="range">
                </div>

            </div>
        </div>
        
        @if(auth()->user()->hasRole('super'))
	        <div class="col-12 col-md-1 col-sm-1">
	            <div class="form-group">
	                <div class="input-group">
	                {!! Form::select('cc',
	                    [''=>'']+Areaseb\Core\Models\Branch::orderBy('nome', 'ASC')->pluck('nome', 'id')->toArray(),
	                    request('cc'),['id' => 'cc']) !!}
	                </div>
	            </div>
	        </div>
	    @endif
	    
	    <div class="col-md-1">
	        <div class="row">
	            <div class="form-group">
	                <a href="#" class="btn btn-info btn-sm filter" style="height:36px; line-height:26px;"><i class="fas fa-search"></i> Filtra</a>
	            </div>
	        </div>
	    </div>

    </div>
{{Form::close()}}

@push('scripts')
<script>

/*    function submitter()
    {
        let go = 0;
        go += ($("#range").val() == '') ? 0 : 1;
        go += ($("#numero").val() == '') ? 0 : 1;
        go += ($('select[name="company"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="contact"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="saldato"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="tipo"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="anno"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="mese"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="cc"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="tipo_pag"]').find('option:selected').val() == '') ? 0 : 1;


        if(go > 0)
        {
            $('#formFilterInvoices').submit();
        }
        else
        {
            window.location.href = baseURL + 'invoices';
        }

    }*/
    
    $('.filter').on('click', function(){
        $('#formFilterInvoices').submit();
	});

    $('select#tipo').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Tipo Doc.'});
    $('select#saldato').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Saldato'});
    $('select#anno').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Anno'});
    $('select#mese').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Mese'});
    $('select#cc').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Sede'});
    $('select#tipo_pag').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Pagamento'});
    $('.select2Company').select2({width: '100%', placeholder:'Sel. Azienda'})
    $('.select2Contact').select2({width: '100%', placeholder:'Sel. Contatto'})

/*    $('select[name="company"]').on('change', function(){
        submitter();
    });
    
    $('select[name="contact"]').on('change', function(){
        submitter();
    });
    
    $('select[name="tipo_pag"]').on('change', function(){
        submitter();
    });

    $('select[name="anno"]').on('change', function(){
        submitter();
    });
    $('select[name="mese"]').on('change', function(){
        submitter();
    });
    $('select[name="saldato"]').on('change', function(){
        submitter();
    });

    $('select[name="tipo"]').on('change', function(){
        submitter();
    });

    $('select[name="cc"]').on('change', function(){
        submitter();
    });

    $('span.bg-danger.disabled').on('click', function(){
        $('.select2').val(null).trigger('change');
    });
    
    $("[name='numero']").on('input', function(e){
			
		setTimeout(() => {
			search = ($("[name='numero']").val() == "") ? null : $("[name='numero']").val();
		    
		    window.location.href="/invoices?numero=" + search;
		    
		}, "1500");
		
		
	});*/

let start = '';
let end = '';
@if(request()->get('range'))
    let str = "{{request()->get('range')}}";
    let arr = str.split(' - ');
    start = arr[0];
    end = arr[1];

    $('#range').daterangepicker({
        startDate: start,
        endDate: end,
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Applica',
            cancelLabel: 'Annulla',
            fromLabel: 'Da',
            toLabel: 'A'
        }
    });

@else
$('#range').daterangepicker({
    locale: {
        format: 'DD/MM/YYYY',
        applyLabel: 'Applica',
        cancelLabel: 'Annulla',
        fromLabel: 'Da',
        toLabel: 'A'
    }
});
$("#range").val('');
@endif


/*$('#range').on('apply.daterangepicker', function(ev, picker) {
submitter();
});
*/
$('#range').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
    //submitter();
});



</script>
@endpush
