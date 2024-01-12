{!! Form::open(['url' => url($url_action), 'method' => 'get', 'id' => 'formFilter']) !!}

<div class="row" id="advancedSearchBox">
@php
$year = date("Y");
$from = request('from') != null ? request('from') : date('Y-m-d');	//date(strval($year -1 ).'-07-01');
$to = request('to') != null ? request('to') : date('Y-m-d');
@endphp

    <div class="col-md-2">
        <div class="form-group">
            Sede
            <select class="form-control " name="sede">
                <option value="">Tutti</option>

                @foreach($sedi as $c)
                    @if(request('sede') == $c->id)
                        <option value="{{$c->id}}" selected>{{$c->nome}}</option>
                    @else
                    <option value="{{$c->id}}" >{{$c->nome}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            Cliente
            <select class="form-control " name="client">
                <option value="">Tutti</option>

                @foreach($clienti as $c)
                    @if(request('client') == $c->id)
                        <option value="{{$c->id}}" selected>{{$c->nome}}</option>
                    @else
                    <option value="{{$c->id}}" >{{$c->nome}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
        Albergo 
            <select class="form-control " name="ritrovo">
                <option value="">Tutti</option>

                @foreach($ritrovi as $r)
                    @if(request('ritrovo') == $r->id)
                        <option value="{{$r->id}}" selected>{{$r->luogo}}</option>
                    @else
                    <option value="{{$r->id}}" >{{$r->luogo}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            Dal
        <input type="date" id="from" name="from" class="form-control " value="{{$from}}">
        </div>
    </div>

    <div class="col-md-2">
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
