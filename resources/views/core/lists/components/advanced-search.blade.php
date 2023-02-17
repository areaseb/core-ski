{!! Form::open(['url' => url($url_action), 'id' => 'formFilter']) !!}
@if(request()->input())
    @if(request()->has('id'))
        <div class="row d-none" id="advancedSearchBox">
    @else
        <div class="row" id="advancedSearchBox">
    @endif
@else
    <div class="row d-none" id="advancedSearchBox">
@endif


        <div class="col col-sm-6 col-lg-2 col-xl-1">

            <div class="form-group">
                @if(request()->query())
                    <a href="{{url('create-list')}}" class="btn btn-danger"><i class="fas fa-redo"></i></a>
                @endif
                <button type="submit" class="btn btn-success"><i class="fas fa-search"></i></button>
            </div>

        </div>


        <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
            <div class="form-group">
                <select class="custom-select" name="region[]" multiple="multiple">
                    @php
                        $regions = Areaseb\Core\Models\City::uniqueRegions();
                        unset($regions['']);
                        $selectedRegions = is_null(request('region')) ? [] : explode('|',request('region'));
                    @endphp
                    @foreach($regions as $region)
                        @if(in_array($region, $selectedRegions))
                            <option selected>{{$region}}</option>
                        @else
                            <option>{{$region}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3 col-xl-3">
            <div class="form-group">
                <select class="custom-select" name="province[]" multiple="multiple">
                    @php
                        $provinces = Areaseb\Core\Models\City::uniqueProvinces(request('region'));
                        unset($provinces['']) ;
                        $selectedProvinces = is_null(request('province')) ? [] : explode('|',request('province'));
                    @endphp
                    @foreach($provinces as $province)
                        @if(in_array($province, $selectedProvinces))
                            <option selected>{{$province}}</option>
                        @else
                            <option>{{$province}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3 col-xl-3">
            <div class="form-group">
                <select class="custom-select" name="tipo[]" multiple="multiple">
                    @php

                        $selectedTypes = is_null(request('tipo')) ? [] : explode('|',request('tipo'));
                    @endphp

                    @foreach(Areaseb\Core\Models\Client::all() as $tipo)
                        @if(in_array($tipo->id, $selectedTypes))
                            <option selected="selected" value="{{$tipo->id}}">{{$tipo->nome}}</option>
                        @else
                            <option value="{{$tipo->id}}">{{$tipo->nome}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>


        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
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


        <div class="col-12 col-sm-6 col-lg-7 col-xl-3">
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


        <div class="col-12 col-sm-6 col-lg-7 col-xl-3">
            <div class="form-group">
                {!!Form::select('origin', ['' => '']+\Areaseb\Core\Models\Company::uniqueOrigin(), request('origin'), ['class' => 'custom-select'])!!}
            </div>
        </div>


        <div class="col-12 col-sm-6 col-lg-7 col-xl-3">
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


        <div class="col-12 col-sm-6 col-lg-7 col-xl-3">
            <div class="form-group">
                {!!Form::select('supplier', ['' => 'Fornitore', 0=>'No', 1=>'Sì'], request('supplier'), ['class' => 'custom-select'])!!}
            </div>
        </div>





 {{-- <div class="col-12">
        <div class="form-group">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="testo da cercare" name="search" value="{{request('search')}}">
                <div class="input-group-append" id="button-addon4">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cerca</button>
                    <a href="{{url('contacts')}}" class="btn btn-success"><i class="fas fa-redo"></i></a>
                </div>
            </div>
        </div>
    </div> --}}

</div>
{!! Form::close() !!}


@push('scripts')
<script>
    let checked = $('#customSwitch1').prop('checked');

    if(checked === true)
    {
        $('#advancedSearchBox').removeClass('d-none');
    }
    else
    {
        $('#advancedSearchBox').addClass('d-none');
    }


    function submitter()
    {
        let go = 0;
        go += ($("#range").val() == '') ? 0 : 1;
        go += ($('select[name="list"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="tipo"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="province"]').find('option:selected').val() == '') ? 0 : 1;
        go += ($('select[name="region"]').find('option:selected').val() == '') ? 0 : 1;


        if(go > 0)
        {
            //let data =  $('form#formFilter').serialize() ;
            //console.log( data );
            console.log($('select[name="province[]"]').find('option:selected').val());
        }
        else
        {
            //window.location.href = baseURL + 'invoices';
        }

    }

    $('select[name="list"]').select2({placeholder: 'List newsletter', width:'100%'});
    $('select[name="tipo[]"]').select2({placeholder: 'Tipo contatto', width:'100%'});
    $('select[name="province[]"]').select2({placeholder: 'Provincia', width:'100%'});
    $('select[name="region[]"]').select2({placeholder: 'Regione', width:'100%'});
    $('select[name="sector"]').select2({placeholder: 'Categorie', width: '100%'});
    $('select[name="origin"]').select2({placeholder: 'Origine', width: '100%'});

    // $('select[name="province[]"]').on('change', function(){
    //     submitter();
    // });


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



    $('#range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });


    $('form#formFilter button').on('click', function(e){
        e.preventDefault();

        let region = "?region=";
        let province = "&province=";
        let tipo = "&tipo=";
        let list = "&list="+$('select[name="list"]').val();
        let range = "&range="+$("#range").val();
        let origine = "&origin="+$('select[name="origin"]').val();
        let settore = "&sector="+$('select[name="sector"]').val();
        let supplier = "&supplier=";

        let regs = $('select[name="region[]"]').val();
        if(regs.length > 1)
        {
            region += regs.join('|');
        }
        else
        {
            region += (typeof regs[0] == 'undefined') ? "" : regs[0];
        }

        let provs = $('select[name="province[]"]').val();
        if(provs.length > 1)
        {
            province += provs.join('|');
        }
        else
        {
            province += (typeof provs[0] == 'undefined') ? "" : provs[0];
        }

        let type = $('select[name="tipo[]"]').val();
        if(type.length > 1)
        {
            tipo += type.join('|');
        }
        else
        {
            tipo += (typeof type[0] == 'undefined') ? "" : type[0];
        }


        let origin = $('select[name="origin"]').val();
        if(origin.length > 1)
        {
            origine += type.join('|');
        }
        else
        {
            origine += (typeof origin[0] == 'undefined') ? "" : origin[0];
        }


        let sector = $('select[name="origin"]').val();
        if(sector.length > 1)
        {
            settore += type.join('|');
        }
        else
        {
            settore += (typeof sector[0] == 'undefined') ? "" : sector[0];
        }

        let fornitore = $('select[name="supplier"]').val();
        if(fornitore.length > 1)
        {
            supplier += fornitore
        }
        else
        {
            supplier += (typeof fornitore[0] == 'undefined') ? "" : fornitore[0];
        }
        window.location.href = baseURL +'create-list'+ region+province+tipo+list+range+origine+settore+supplier;

    })

</script>
@endpush
