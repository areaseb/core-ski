<table id="table" class="table table-sm table-bordered table-striped">
    <thead>
        <tr>
            <th style="width:50%">Ragione Sociale</th>
            <th>{{date('Y')-3}}</th>
            <th>{{date('Y')-2}}</th>
            <th>{{date('Y')-1}}</th>
            <th>{{date('Y')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($companies as $company)

            @php
                $temp = [];
                foreach(range((date('Y')-3), date('Y'), 1) as $key => $year)
                {
                    if($year > 2018)
                    {
                        $e = $company->invoices()->anno($year)->consegnate()->entrate()->sum('imponibile');
                        $u = $company->invoices()->anno($year)->consegnate()->notediaccredito()->sum('imponibile');
                        $temp[$key] = $e - abs($u);
                    }
                    else
                    {
                        $e = $company->invoices()->anno($year)->entrate()->sum('imponibile');
                        $u = $company->invoices()->anno($year)->notediaccredito()->sum('imponibile');
                        $temp[$key] = $e - abs($u);
                    }
                }

                $tot = array_sum($temp);
                $a_perc = ($tot > 0) ? round(($temp[0]/$tot)*100) : 0;
                $b_perc = ($tot > 0) ? round(($temp[1]/$tot)*100) : 0;
                $c_perc = ($tot > 0) ? round(($temp[2]/$tot)*100) : 0;
                $d_perc = ($tot > 0) ? round(($temp[3]/$tot)*100) : 0;
            @endphp

            <tr>
                <td style="width:50%">{{$company->rag_soc}}</td>
                <td>{{\Areaseb\Core\Models\Primitive::NF($temp[0])}}</td>
                <td>{{\Areaseb\Core\Models\Primitive::NF($temp[1])}}</td>
                <td>{{\Areaseb\Core\Models\Primitive::NF($temp[2])}}</td>
                <td>{{\Areaseb\Core\Models\Primitive::NF($temp[3])}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
