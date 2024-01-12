{!! Form::open(['url' => url($url_action), 'method' => 'get', 'id' => 'formFilter']) !!}

<div class="row" id="advancedSearchBox">
@php
if(date('m') <= 6){
	$years = [''=>'']+range(date('Y'), 2011);
} else {
	$years = [''=>'']+range(date('Y')+1, 2011);
}
$years = [''=>'']+range(date('Y')+1, 2011);
@endphp

    @if(request()->has('year'))
        <div style="float: left;width:87px;">
            <div class="form-group">
                <a href="#" class="btn btn-success btn-sm" id="refresh" style="height:36px; line-height:26px;"><i class="fa fa-redo"></i> Reset</a>
            </div>
        </div>
    @endif

    <div class="col-md-3">
        <div class="form-group">
            
            <select class="custom-select " name="year">
                @foreach($years as $year)
                    @if(request('year') == $year)
                    <option selected value="{{$year}}"> {{intval($year) -1}} - {{$year}}</option>
                    @else
                    <option value="{{$year}}">{{intval($year) -1}} - {{$year}}</option>
                    @endif
                   
                @endforeach
            </select>
        </div>
    </div>


</div>
{!! Form::close() !!}


@push('scripts')
<script>


    $('select[name="sector"]').select2({placeholder: 'Settore', width:'100%'});

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

    $('select[name="subscribed"]').select2({placeholder: 'Iscritti', width:'100%'});
    $('select[name="origin"]').select2({placeholder: 'Origine', width:'100%', allowClear: true});
    $('select[name="created_at"]').select2({placeholder: 'Data di creazione', width:'100%'});
    $('select[name="list"]').select2({placeholder: 'List newsletter', width:'100%'});
    $('select[name="tipo"]').select2({placeholder: 'Tipo contatto', width:'100%'});
    $('select[name="year"]').select2({placeholder: 'Stagione', width:'100%'});
    $('select[name="branch"]').select2({placeholder: 'Sede', width:'100%'});

    $('select.custom-select').on('change', function(){
        $('#formFilter').submit();
    });

    $('#refresh').on('click', function(e){
        e.preventDefault();
        let currentUrl = window.location.href;
        let arr = currentUrl.split('?');
        window.location.href = arr[0];
    });


</script>
@endpush
