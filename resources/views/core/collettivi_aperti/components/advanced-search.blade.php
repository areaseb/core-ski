{!! Form::open(['url' => url($url_action), 'method' => 'get', 'id' => 'formFilter']) !!}

<div class="row" id="advancedSearchBox">
@php
$year = date("Y");
$from = request('from') != null ? request('from') : date('Y-m-d');	//date(strval($year -1 ).'-07-01');
$to = request('to') != null ? request('to') : date('Y-m-d');
@endphp


    <div class="col-md-3">
        <div class="form-group">
            Dal
        <input type="date" id="from" name="from" class="form-control " value="{{$from}}">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            Al
        <input type="date" id="to" name="to" class="form-control " value="{{$to}}">
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="row">
            <div class="form-group">
            	<br>
                <a href="#" class="btn btn-info btn-sm datefilter" style="height:36px; line-height:26px;"><i class="fas fa-search"></i> Filtra</a>
            </div>
        </div>
    </div>


</div>
{!! Form::close() !!}


@push('scripts')
<script>

    $('.datefilter').on('click', function(){
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
