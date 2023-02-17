@extends('areaseb::layouts.app')

@section('css')
<style>
figure{margin-bottom: 0;}
    .sparklist {overflow: visible;width: 100%;margin: 0;padding: 0;}
    .sparklist li {list-style: none;margin-right: 0;}
    .sparkline {display: inline-block;height: 1.3em;margin: 0 0.5em;-webkit-transition: all .5s ease;transition: all .5s ease;}
    .sparkline .index {position: relative;float: left;width: 10px;height: 1.3em;}
    .sparkline .index .count {display: block;position: absolute;bottom: 0;left: 1px;width: 100%;height: 0;font: 0/0 a;text-shadow: none;color: transparent;}
    .c1{background: #666;}
    .c2{background: #888;}
    .c3{background: #aaa;}
    .c4{background: #ccc;}
</style>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Statistiche Aziende'])

@section('content')


    @php

        if( intval( str_replace('€ ', '', $annualStats[(date('Y')-3)]) ) )
        {
            $range = range((date('Y'))-3, date('Y'), 1);
        }
        elseif(intval(str_replace('€ ', '', $annualStats[(date('Y')-2)]) ) )
        {
            $range = range((date('Y'))-2, date('Y'), 1);
        }
        elseif(intval(str_replace('€ ', '', $annualStats[(date('Y')-1)]) ) )
        {
            $range = range((date('Y'))-1, date('Y'), 1);
        }
        else
        {
            $range = range((date('Y')), date('Y'), 1);
        }

    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-4">
                            <a
                                @if(request()->has('ids'))
                                    href="{{url('stats/export?ids=')}}{{request('ids')}}"
                                @else
                                    href="{{url('stats/export')}}"
                                @endif
                                target="_BLANK" class="btn btn-sm btn-success"><i class="fa fa-file-excel"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <div class="card-tools">
                                {!!Form::open(['method' => 'GET'])!!}
                                    <div class="input-group">
                                        {!!Form::select('id', $companiesId, $companiesIdSelected, [ 'class'=> 'form-control form-control-sm selectCompany', 'multiple' => 'multiple'])!!}
                                        <div class="input-group-append">
                                            <button class="input-group-text overlay btn btn-sm" style="padding:3px 9px;font-size:15px;">Confronta</button>
                                        </div>
                                    </div>
                                {!!Form::close()!!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width:50%">Ragione Sociale</th>
                                    @foreach($range as $ran)
                                        <th>{{$ran}}</th>
                                    @endforeach
                                    <th class="d-none d-lg-table-cell"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($companies as $company)

                                    @php
                                        $temp = [];
                                        foreach($range as $key => $year)
                                        {
                                            $e = $company->invoices()->anno($year)->consegnate()->entrate()->sum(\DB::raw('iva + imponibile'));
                                            $u = $company->invoices()->anno($year)->consegnate()->notediaccredito()->sum(\DB::raw('iva + imponibile'));
                                            $temp[$key] = $e - abs($u);
                                        }
                                        $tot = array_sum($temp);
                                    @endphp

                                    <tr>
                                        <td style="width:50%"><a class="defaultColor" href="{{$company->url}}">{{$company->rag_soc}}</a></td>
                                        <td>{{\Areaseb\Core\Models\Primitive::NF($temp[0])}}</td>
                                        @if(isset($temp[1]))
                                            <td>{{\Areaseb\Core\Models\Primitive::NF($temp[1])}}</td>
                                        @endif
                                        @if(isset($temp[2]))
                                            <td>{{\Areaseb\Core\Models\Primitive::NF($temp[2])}}</td>
                                        @endif
                                        @if(isset($temp[3]))
                                            <td>{{\Areaseb\Core\Models\Primitive::NF($temp[3])}}</td>
                                        @endif
                                        <td class="d-none d-lg-table-cell">
                                            <figure>
                                                <ul class="sparklist">
                                                    <li>
                                                        <span class="sparkline">
                                                        @if(isset($temp[0]))
                                                            @if($tot > 0)
                                                                <span class="index"><span class="count c1" style="height: {{round(($temp[0]/$tot)*100)}}%;"></span> </span>
                                                            @else
                                                                <span class="index"><span class="count c1" style="height: 0%;"></span> </span>
                                                            @endif
                                                        @endif
                                                        @if(isset($temp[1]))
                                                            @if($tot > 0)
                                                                <span class="index"><span class="count c2" style="height: {{round(($temp[1]/$tot)*100)}}%;"></span> </span>
                                                            @else
                                                                <span class="index"><span class="count c2" style="height: 0%;"></span> </span>
                                                            @endif
                                                        @endif
                                                        @if(isset($temp[2]))
                                                            @if($tot > 0)
                                                                <span class="index"><span class="count c3" style="height: {{round(($temp[2]/$tot)*100)}}%;"></span> </span>
                                                            @else
                                                                <span class="index"><span class="count c3" style="height: 0%;"></span> </span>
                                                            @endif
                                                        @endif
                                                        @if(isset($temp[3]))
                                                            @if($tot > 0)
                                                                <span class="index"><span class="count c4" style="height: {{round(($temp[3]/$tot)*100)}}%;"></span> </span>
                                                            @else
                                                                <span class="index"><span class="count c4" style="height: 0%;"></span> </span>
                                                            @endif
                                                        @endif
                                                        </span>
                                                    </li>
                                                </ul>
                                            </figure>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width:50%"></th>
                                    @foreach($range as $ran)
                                        <th>{{$ran}}</th>
                                    @endforeach
                                    <th class="d-none d-lg-table-cell"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="width:50%"><strong>Totale</strong></td>
                                    @foreach($range as $ran)
                                        <td>{{$annualStats[$ran]}}</td>
                                    @endforeach
                                    <td class="d-none d-lg-table-cell"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

                {{-- <div class="card-footer text-center">
                    {{ $companies->links() }}
                </div> --}}
            </div>
        </div>
    </div>

@php
    $sectors = \Areaseb\Core\Models\Sector::orderBy('nome', 'ASC')->get();
@endphp

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Per Categoria Cliente</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-sm table-bordered table-striped">
                            <thead>
                                <th style="width:50%">Categoria</th>
                                @foreach($range as $ran)
                                    <th>{{$ran}}</th>
                                @endforeach
                                <th class="d-none d-lg-table-cell"></th>
                            </thead>
                            <tbody>
                                @foreach($sectors as $sector)

                                    @php

                                        $companiesId = $sector->companies()->pluck('id')->toArray();

                                        $tempSector = [];
                                        foreach($range as $key => $year)
                                        {
                                            if($year > 2018)
                                            {
                                                $e = \Areaseb\Core\Models\Invoice::whereIn('company_id', $companiesId)->anno($year)->consegnate()->entrate()->sum(\DB::raw('iva + imponibile'));
                                                $u = \Areaseb\Core\Models\Invoice::whereIn('company_id', $companiesId)->anno($year)->consegnate()->notediaccredito()->sum(\DB::raw('iva + imponibile'));
                                                $tempSector[$key] = $e - abs($u);
                                            }
                                            else
                                            {
                                                $e = \Areaseb\Core\Models\Invoice::whereIn('company_id', $companiesId)->anno($year)->entrate()->sum(\DB::raw('iva + imponibile'));
                                                $u = \Areaseb\Core\Models\Invoice::whereIn('company_id', $companiesId)->anno($year)->notediaccredito()->sum(\DB::raw('iva + imponibile'));
                                                $tempSector[$key] = $e - abs($u);
                                            }
                                        }

                                        $totSector = array_sum($tempSector);
                                    @endphp

                                    <tr>
                                        <td style="width:50%">{{$sector->nome}}</td>
                                        <td>{{\Areaseb\Core\Models\Primitive::NF($tempSector[0])}}</td>
                                        @if(isset($tempSector[1]))
                                            <td>{{\Areaseb\Core\Models\Primitive::NF($tempSector[1])}}</td>
                                        @endif
                                        @if(isset($tempSector[2]))
                                            <td>{{\Areaseb\Core\Models\Primitive::NF($tempSector[2])}}</td>
                                        @endif
                                        @if(isset($tempSector[3]))
                                            <td>{{\Areaseb\Core\Models\Primitive::NF($tempSector[3])}}</td>
                                        @endif
                                        <td class="d-none d-lg-table-cell">
                                            <figure>
                                                <ul class="sparklist">
                                                    <li>
                                                        <span class="sparkline">
                                                        @if(isset($tempSector[0]))
                                                            @if($totSector > 0)
                                                                <span class="index"><span class="count c1" style="height: {{round(($tempSector[0]/$totSector)*100)}}%;"></span> </span>
                                                            @else
                                                                <span class="index"><span class="count c1" style="height: 0%;"></span> </span>
                                                            @endif
                                                        @endif
                                                        @if(isset($tempSector[1]))
                                                            @if($totSector > 0)
                                                                <span class="index"><span class="count c2" style="height: {{round(($tempSector[1]/$totSector)*100)}}%;"></span> </span>
                                                            @else
                                                                <span class="index"><span class="count c2" style="height: 0%;"></span> </span>
                                                            @endif
                                                        @endif
                                                        @if(isset($tempSector[2]))
                                                            @if($totSector > 0)
                                                                <span class="index"><span class="count c3" style="height: {{round(($tempSector[2]/$totSector)*100)}}%;"></span> </span>
                                                            @else
                                                                <span class="index"><span class="count c3" style="height: 0%;"></span> </span>
                                                            @endif
                                                        @endif
                                                        @if(isset($tempSector[3]))
                                                            @if($totSector > 0)
                                                                <span class="index"><span class="count c4" style="height: {{round(($tempSector[3]/$totSector)*100)}}%;"></span> </span>
                                                            @else
                                                                <span class="index"><span class="count c4" style="height: 0%;"></span> </span>
                                                            @endif
                                                        @endif
                                                        </span>
                                                    </li>
                                                </ul>
                                            </figure>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('scripts')
<script>
    $('select.selectCompany').select2({placeholder:'Seleziona Aziende'});
$('button.overlay').on('click', function(e){
    e.preventDefault();
    let selected = 'stats/aziende?ids=';let count = 0;
    $.each($('select.selectCompany').select2('data'), function(){
        selected += $(this)[0].id+'-';
        count++;
    })
    if(count > 0)
    {
        selected = selected.substring(0, selected.length - 1);
        window.location.href = baseURL+''+selected;
    }
    else
    {
        window.location.href = baseURL+'stats/aziende'
    }
});

$('a#menu-stats-aziende').addClass('active');
$('a#menu-stats-aziende').parent('ul.nav-treeview').css('display', 'block');
$('a#menu-stats-aziende').parent('ul').parent('li.has-treeview ').addClass('menu-open');

</script>
@stop
