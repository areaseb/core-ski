{!! Form::open(['url' => url($url_action), 'method' => 'get', 'id' => 'formFilter']) !!}

<div class="row" id="advancedSearchBox">
@php
$years = [''=>'']+range(date('Y'), 1900);
@endphp

    @if(request()->input())
        <div style="float: left;width:87px;">
            <div class="form-group">
                <a href="#" class="btn btn-success btn-sm" id="refresh" style="height:36px; line-height:26px;"><i class="fa fa-redo"></i> Reset</a>
            </div>
        </div>
    @endif



    <div class="col-md-3">
    
        <div class="row">     
            <b>Dal</b>&nbsp;
            <input type="date" name="data_in"  class="form-item " > 
        </div>
    </div>

    <div class="col-md-3">
        <div class="row">
        <b>Al</b>&nbsp;     
            <input type="date" name="data_out"  class="form-item " > 
        </div>
    </div>

    <div class="col-md-3">
        <div class="row">
        <b>Sede</b>&nbsp;
            <select class="" name="branch_id">
                <option value="">Tutte</option>
                @foreach($branches as $b)
                    @if(request('branch_id') == $b->id)
                        <option value="{{$b->id}}" selected>{{$b->nome}}</option>
                    @else
                        <option value="{{$b->id}}">{{$b->nome}}</option>
                    @endif
                @endforeach
            </select>

        </div>
    </div>
    
    <div class="col-md-2">
        <div class="row">
            <div class="form-group">
                <a href="#" class="btn btn-info btn-sm filter" style="height:36px; line-height:26px;"><i class="fas fa-search"></i> Filtra</a>
            </div>
        </div>
    </div>


</div>
{!! Form::close() !!}


@push('scripts')
<script>

$('select[name="branch_id"]').select2({width: '70%'});

$('.filter').on('click', function(){
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
