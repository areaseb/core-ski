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
            <select class="custom-select" name="tipo">
                <option></option>
                @foreach(Areaseb\Core\Models\Client::all() as $tipo)
                    @if(request('tipo') == $tipo->id)
                        <option selected="selected" value="{{$tipo->id}}">{{$tipo->nome}}</option>
                    @else
                        <option value="{{$tipo->id}}">{{$tipo->nome}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>


    <div class="col">
        <div class="form-group">
            <select class="custom-select" name="list">
                <option></option>
                @foreach(Areaseb\Core\Models\NewsletterList::orderBy('nome', 'asc')->get() as $list)
                    @if(request('list') == $list->id)
                        <option  value="{{$list->id}}" selected>{{$list->nome}}</option>
                    @else
                        <option value="{{$list->id}}" >{{$list->nome}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>


    <div class="col">
        <div class="form-group">
            <select class="custom-select" name="created_at">
                <option></option>
                <option value="0">Oggi</option>
                <option value="1" @if(request('created_at') == 1) selected @endif>Ieri</option>
                <option value="7" @if(request('created_at') == 7) selected @endif>Ultimi 7 giorni</option>
                <option value="30" @if(request('created_at') == 30) selected @endif>Ultimi 30 giorni</option>
            </select>
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <select class="custom-select" name="origin">
                <option></option>
                @foreach (\Areaseb\Core\Models\Contact::uniqueOrigin() as $o)
                    <option @if(request('origin') == $o) selected="selected" @endif >{{$o}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <select class="custom-select" name="subscribed">
                <option></option>
                @if(request()->has('subscribed') && request()->get('subscribed'))
                    <option value="1" @if(intval(request('subscribed')) === 1) selected="selected" @endif >Sì</option>
                    <option value="0" @if(intval(request('subscribed')) === 0) selected="selected" @endif>No</option>
                @else
                    <option value="1">Sì</option>
                    <option value="0">No</option>
                @endif
            </select>
        </div>
    </div>

    <div class="col">
        <div class="form-group">
            <select class="custom-select" name="sector">
                <option></option>
                @foreach(Areaseb\Core\Models\Sector::pluck('nome', 'id')->toArray() as $id => $nome)
                    @if(request('sector') == $id)
                        <option selected="selected" value="{{$id}}">{{$nome}}</option>
                    @else
                        <option value="{{$id}}">{{$nome}}</option>
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
    $('select[name="province"]').select2({placeholder: 'Provincia', width:'100%'});
    $('select[name="region"]').select2({placeholder: 'Regione', width:'100%'});

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
