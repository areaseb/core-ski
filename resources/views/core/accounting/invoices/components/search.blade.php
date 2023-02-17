{!! Form::open(['url' => $url, 'method' => 'get', 'id' => 'formFilterInvoices']) !!}
    <div class="row">

        @if(request()->input())
            <div style="float: left;width:87px;">
                <div class="form-group">
                    <a href="{{url('invoices')}}" class="btn btn-success" id="refresh" title="reset ricerca"><i class="fa fa-redo"></i> Reset</a>
                </div>
            </div>
        @endif


        <div class="col-12 col-sm-4 col-lg-2">
            <div class="form-group">
                {!!Form::select('tipo', [''=>'']+config('invoice.types'), request('tipo'), ['id' => 'tipo'])!!}
            </div>
        </div>

        <div class="col-12 col-sm-4 col-lg-2">
            <div class="form-group">
                <div class="input-group">
                {!! Form::select('company',
                    [''=>'']+Areaseb\Core\Models\Company::orderBy('rag_soc', 'ASC')->pluck('rag_soc', 'id')->toArray(),
                    request('company'),['class' => 'select2Company']) !!}
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-4 col-lg-2">
            <div class="form-group">
                <select id="saldato" name="saldato">
                    <option @if(!request()->has('saldato')) selected @endif></option>
                    <option value="1" @if(request('saldato') == '1') selected @endif >Sì</option>
                    <option value="0" @if(request('saldato') == '0') selected @endif >No</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-12 col-lg-3">
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


        <div class="col-12 col-sm-12 col-lg-3">
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

    </div>
{{Form::close()}}

@push('scripts')
<script>

    function submitter()
    {
        let go = 0;
        go += ($("#range").val() == '') ? 0 : 1;
        go += ($('select[name="company"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="saldato"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="tipo"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="anno"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="mese"]').find('option:selected').val() == '') ? 0 : 1;


        if(go > 0)
        {
            $('#formFilterInvoices').submit();
        }
        else
        {
            window.location.href = baseURL + 'invoices';
        }

    }

    $('select#tipo').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Tipo'});
    $('select#saldato').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Saldato'});
    $('select#anno').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Anno'});
    $('select#mese').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Mese'});
    $('.select2Company').select2({width: '100%', placeholder:'Seleziona Azienda'})

    $('select[name="company"]').on('change', function(){
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
    })

    $('span.bg-danger.disabled').on('click', function(){
        $('.select2').val(null).trigger('change');
    });

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


$('#range').on('apply.daterangepicker', function(ev, picker) {
submitter();
});

$('#range').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
    submitter();
});



</script>
@endpush
