@php
    // $arrQ = []; $barData = [];
    // foreach(range(date('Y'), date('Y')-3) as $year)
    // {
    //     $arrQ[$year][1] = $Stat::totMonth($year, 1, 3);
    //     $arrQ[$year][2] = $Stat::totMonth($year, 4, 3);
    //     $arrQ[$year][3] = $Stat::totMonth($year, 7, 3);
    //     $arrQ[$year][4] = $Stat::totMonth($year, 10, 3);
    // }

@endphp


<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">IVA</h3>
            </div>
            <div class="card-body">
                <div class="row">

                    @foreach(range(date('Y')-3, date('Y')-1) as $year)
                        @if( intval(str_replace("€ ","", $Stat::Imposte($year)) ) )
                            <div class="col-12 col-md-4">
                                <div class="card card-outline card-primary">
                                    <div class="card-header text-center" style="padding:.3rem">
                                        <h3 class="card-title w100">{{$year}}</h3>
                                    </div>
                                    <div class="card-body text-center" style="padding:.9rem .5rem">
                                        <span class="text-success"><small><b>Vendite</b></small><br>{{$Stat::Imposte($year)}}</span><br>
                                        <span class="text-danger"><small><b>Acquisti</b></small><br>{{$Stat::ImposteCosti($year)}}</span><br>
                                        <span class="text-primary"><small><b>Differenza</b></small><br>{{$Stat::ImposteBilancio($year)}}</span><br>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">IVA {{date('Y')}} Trimestrale</h3>
            </div>

            <div class="card-body pb-4">
                <div class="row">
                    <div class="table-responsive pb-2">
                        <table class="table mt-2">
                            <thead>
                                <th style="border-top:none;"></th>
                                <th style="border-top:none;">Primo</th>
                                <th style="border-top:none;">Secondo</th>
                                <th style="border-top:none;">Terzo</th>
                                <th style="border-top:none;">Quarto</th>
                            </thead>
                            <tbody>
                                @php
                                    $primo = $Stat::totMonthIva(1, 3);
                                    $secondo = $Stat::totMonthIva(4, 6);
                                    $terzo = $Stat::totMonthIva(7, 9);
                                    $quarto = $Stat::totMonthIva(10, 12);
                                @endphp
                                <tr>
                                    <td><b class="text-success">Vendite</b></td>
                                    <td>{{$primo['Vendite']}}</td>
                                    <td>{{$secondo['Vendite']}}</td>
                                    <td>{{$terzo['Vendite']}}</td>
                                    <td>{{$quarto['Vendite']}}</td>
                                </tr>
                                <tr>
                                    <td><b class="text-danger">Acquisti</b></td>
                                    <td>{{$primo['Acquisti']}}</td>
                                    <td>{{$secondo['Acquisti']}}</td>
                                    <td>{{$terzo['Acquisti']}}</td>
                                    <td>{{$quarto['Acquisti']}}</td>
                                </tr>
                                <tr>
                                    <td><b class="text-primary">Differenza</b></td>
                                    <td>{{$primo['Bilancio']}}</td>
                                    <td>{{$secondo['Bilancio']}}</td>
                                    <td>{{$terzo['Bilancio']}}</td>
                                    <td>{{$quarto['Bilancio']}}</td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
