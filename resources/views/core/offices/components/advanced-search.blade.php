{!! Form::open(['url' => url($url_action), 'method' => 'get', 'id' => 'formFilter']) !!}
@if(request()->input())
    @if(request()->has('id'))
        <div class="row d-none" id="advancedSearchBox">
    @else
        <div class="row" id="advancedSearchBox">
    @endif
@else
    <div class="row d-none" id="advancedSearchBox">
@endif


    @if(request()->input())
        <div style="float: left;width:87px;">
            <div class="form-group">
                <a href="#" class="btn btn-success btn-sm" id="refresh" style="height:36px; line-height:26px;"><i class="fa fa-redo"></i> Reset</a>
            </div>
        </div>
    @endif

    <div class="col">
        <div class="form-group">
            <select class="custom-select" name="region">
                <option>Regione</option>
                @foreach(Areaseb\Core\Models\City::uniqueRegions() as $region)
                    @if(request('region') == $region)
                        <option selected>{{$region}}</option>
                    @else
                        <option>{{$region}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <select class="custom-select" name="province">
                <option>Provincia</option>
                @foreach(Areaseb\Core\Models\City::uniqueProvinces(request('region')) as $province)
                    @if(request('province') == $province)
                        <option selected>{{$province}}</option>
                    @else
                        <option>{{$province}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="col">
        <div class="form-group">
        <select class="custom-select" name="status">
                @if(!request()->input())
                <option selected>Stato</option>
                <option value="1" >Attivo</option>
                <option value="0" >Non Attivo</option>
                @else
                    <option>Stato</option>
                    <option value="1" {{request('status') == 1 ? 'selected' : ''}}>Attivo</option>
                    <option value="0" {{request('status') == 0 ? 'selected' : ''}}>Non Attivo</option>
                @endif 
            </select>
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <div class="input-group">
                <button type="button" class="btn btn-default float-right" id="daterange-btn">
                    <i class="far fa-calendar-alt"></i> Date
                    <i class="fas fa-caret-down"></i>
                </button>
            </div>
        </div>
    </div>
    {!!Form::hidden('from', request('from'))!!}
    {!!Form::hidden('to', request('to'))!!}


</div>
{!! Form::close() !!}


@push('scripts')
<script>


$('#daterange-btn').daterangepicker(
  {
    ranges: {
      'Oggi'       : [moment(), moment()],
      'Ieri'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Ultimi 7 Giorni' : [moment().subtract(6, 'days'), moment()],
      'Ultimi 30 Giorni': [moment().subtract(29, 'days'), moment()],
      'Questo Mese'  : [moment().startOf('month'), moment().endOf('month')],
      'Mese Scorso'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    },
    startDate: moment().subtract(29, 'days'),
    endDate: moment(),
    locale: {
        'format': "DD/MM/YYYY",
        "applyLabel": "Applica",
        "cancelLabel": "Annulla",
        "customRangeLabel": "Personalizzate",
    }
  }
);

$('#daterange-btn').on('apply.daterangepicker', function(ev, picker) {
    $('input[name="from"]').val(picker.startDate.format('YYYY-MM-DD'));
    $('input[name="to"]').val(picker.endDate.format('YYYY-MM-DD'));
    $('#formFilter').submit();
});


    $('#customSwitch1').on('change', function(){
        if($(this).prop('checked') === true)
        {
            $('#advancedSearchBox').removeClass('d-none');
        }
        else
        {
            $('#advancedSearchBox').addClass('d-none');
        }
    });

    $('select[name="region"]').select2({placeholder: 'Regione', width:'100%'});
    $('select[name="status"]').select2({placeholder: 'Stato', width:'100%'});
    $('select[name="province"]').select2({placeholder: 'Provincia', width:'100%'});
    $('select[name="tipo"]').select2({placeholder: 'Tipo contatto', width:'100%'});
    $('select[name="sector"]').select2({placeholder: 'Categorie', width:'100%'});
    $('select[name="supplier"]').select2({placeholder: 'Fornitore', width:'100%'});

    $('select.custom-select').on('change', function(){
        $('#formFilter').submit();
    });

    $('#refresh').on('click', function(e){
        e.preventDefault();
        let currentUrl = window.location.href;
        let arr = currentUrl.split('?');
        window.location.href = arr[0];
    });

    $('a.creaContatti').on('click', function(e){
        e.preventDefault();


        let data = {};
        data.region = ($('select[name="region"]').val() == "") ? null : $('select[name="region"]').val();
        data.status = ($('select[name="status"]').val() == "") ? null : $('select[name="status"]').val();
        data.province = ($('select[name="province"]').val() == "") ? null : $('select[name="province"]').val();
        data.tipo = ($('select[name="tipo"]').val() == "") ? null : $('select[name="tipo"]').val();
        data.sector = ($('select[name="sector"]').val() == "") ? null : $('select[name="sector"]').val();
        data.fornitore = ($('select[name="fornitore"]').val() == "") ? null : $('select[name="fornitore"]').val();
        data._token = token;


        $.post( "{{url('api/companies/create-contacts')}}", data, function(response){
            new Noty({
                text: response,
                type: 'success',
                theme: 'bootstrap-v4',
                timeout: 2500,
                layout: 'topRight'
            }).show();
        });

    });

</script>
@endpush
