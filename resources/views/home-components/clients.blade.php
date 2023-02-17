@php
    $lComp = explode(',',$aziende->labels);
    $dComp = explode(',',$aziende->data);
    $summaryComp = [];
    foreach($lComp as $key => $label)
    {
        $label = str_replace('"', '', $label);
        $id = Areaseb\Core\Models\Client::where('nome', $label)->first()->id;
        $summaryComp[$id]['value'] = $dComp[$key];
        $summaryComp[$id]['label'] = $label;
    }
@endphp
<div class="col-6">
    <div class="card card-outline card-info">

        <div class="card-header">
            <h3 class="card-title">Aziende ({{$aziende->total}})</h3>
        </div>
        <div class="card-body">
            <canvas id="pieChartCompanies" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
        </div>
        <div class="card-footer bg-info">
            <div class="row">
                @foreach($summaryComp as $id => $values)
                    <div class="col text-center">
                        <a href="{{url('companies?tipo='.$id)}}" style="color:#fff;">{{$values['label']}}: {{$values['value']}}</a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@php
    $l = explode(',',$contatti->labels);
    $d = explode(',',$contatti->data);
    $summary = [];
    foreach($l as $key => $label)
    {
        $label = str_replace('"', '', $label);
        $id = Areaseb\Core\Models\Client::where('nome', $label)->first()->id;
        $summary[$id]['value'] = $d[$key];
        $summary[$id]['label'] = $label;
    }
@endphp

<div class="col-6">
    <div class="card card-outline card-info">

        <div class="card-header">
            <h3 class="card-title">Contatti ({{$contatti->total}})</h3>
        </div>
        <div class="card-body">
            <canvas id="pieChartContacts" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
        </div>
        <div class="card-footer bg-info">
            <div class="row">
                @foreach($summary as $id => $values)
                    <div class="col text-center">
                        <a href="{{url('contacts?tipo='.$id)}}" style="color:#fff;">{{$values['label']}}: {{$values['value']}}</a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

@if(isset(\Areaseb\Core\Models\Setting::base()->marketing_cost))
    @if(\Areaseb\Core\Models\Setting::base()->marketing_cost)

        @php
            $cost = \Areaseb\Core\Models\Setting::base()->marketing_cost;
            $companies = Areaseb\Core\Models\Company::whereDate('created_at', '>=', \Carbon\Carbon::today()->startOfYear())->count();
            if($companies == 0)
            {
                $ratio = $cost;
            }
            else
            {
                $ratio = $cost/$companies;
            }

            foreach(Areaseb\Core\Models\Client::company()->get() as $type)
            {
                $array[$type->nome] = $type->companies()->whereDate('created_at', '>=', \Carbon\Carbon::today()->startOfYear())->count();
            }

            $tot = $array['Lead']+$array['Prospect']+$array['Client'];
        @endphp

        <div class="col-12">
                    <div class="card card-outline card-info">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-3 text-center">
                                    <b>Totale costo marketing {{date('Y')}}</b>
                                    <p class="mb-0">€ {{number_format($cost, 2, ',', '.')}}</p>
                                </div>

                                <div class="col-12 col-sm-6 col-md-3 text-center">
                                    <b>Totale <u>Lead</u> Generate: <div style="line-height:inherit;vertical-align:top;" class="badge badge-secondary">{{$array['Lead']+$array['Prospect']+$array['Client']}}</div></b>
                                    <p class="mb-0">Costo/Lead: €
                                        @if(($array['Lead']+$array['Prospect']+$array['Client']) > 0)
                                            {{number_format($cost/($array['Lead']+$array['Prospect']+$array['Client']), 2, ',', '.')}}
                                        @else
                                            0,00
                                        @endif
                                    </p>
                                </div>

                                <div class="col-12 col-sm-6 col-md-3 text-center">
                                    <b>Totale <u>Prospect</u> Generate: <div style="line-height:inherit;vertical-align:top;" class="badge badge-secondary">{{$array['Prospect']}}</div></b>
                                    <p class="mb-0">Costo/Prospect: €
                                        @if($array['Prospect'] > 0)
                                            {{number_format($cost/$array['Prospect'], 2, ',', '.')}} [{{round(($array['Prospect']/$tot)*100)}}%]
                                        @else
                                            0,00
                                        @endif
                                    </p>
                                </div>

                                <div class="col-12 col-sm-6 col-md-3 text-center">
                                    <b>Totale <u>Client</u> Generate: <div style="line-height:inherit;vertical-align:top;" class="badge badge-secondary">{{$array['Client']}}</div></b>
                                    <p class="mb-0">Costo/Client: €
                                        @if($array['Client'] > 0)
                                            {{number_format($cost/$array['Client'], 2, ',', '.')}} [{{round(($array['Client']/$tot)*100)}}%]
                                        @else
                                            0,00
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    @endif
@endif


@push('scripts')
<script>

    var pieCompanies  = {
      labels: [{!!$aziende->labels!!}],
      datasets: [
        {
          data: [{{$aziende->data}}],
          backgroundColor : [ '#00c0ef', '#3c8dbc', '#d2d6de'],
        }
      ]
  }
    var pieChartCanvas = $('#pieChartCompanies').get(0).getContext('2d')
    var pieOptions     = {maintainAspectRatio : false,responsive : true}

    new Chart(pieChartCanvas, {
      type: 'pie',
      data: pieCompanies,
      options: pieOptions
    });
</script>
<script>
    var pieContacts  = {
      labels: [{!!$contatti->labels!!}],
      datasets: [
        {
          data: [{{$contatti->data}}],
          backgroundColor : [ '#00c0ef', '#3c8dbc', '#d2d6de'],
        }
      ]
  }
    var pieChartCanvas = $('#pieChartContacts').get(0).getContext('2d')

    new Chart(pieChartCanvas, {
      type: 'pie',
      data: pieContacts,
      options: pieOptions
    });
</script>

@endpush
