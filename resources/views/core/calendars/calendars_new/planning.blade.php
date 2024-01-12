@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Calendario ore'])

@php
    use Areaseb\Core\Models\{Ora};
    function oreMaestro($id_maestro, $ore)
    {
    // dd($id_maestro, $ore);
        $arr = []; $count = 0;
        foreach ($ore as $ora)
        {
            if(isset($ora['id_maestro']) && $ora['id_maestro'] == $id_maestro)
            {
                $arr[$count] = $ora;
                $count++;
            }
        }
        return $arr;
    }


    function oreMaestroPerMezzora($oreMaestro)
    {
        $arr = [];
        
        for($o = 8; $o <= 17.5; $o = $o + 0.5)
        {
            $elem = explode(".", $o);

            $intero = $elem[0];
            $resto = isset($elem[1]) ? $elem[1] : 0;
            //list($intero, $resto) = $elem;
            //dd(explode(".", $o));
            if($resto == 0){
                $ora = sprintf('%02d',$intero).":00:00";
            } else {
                $ora = sprintf('%02d',$intero).":30:00";
            }
            $arr[] = $ora;		
        }

        $arr2 = [];
        foreach($arr as $ora)
        {
            $turno = findHour($oreMaestro, $ora);
            if($turno)
            {
                $arr2[] = $turno;
            }
            else
            {
                $arr2[] = [
                    'id' => '',
                    'ora_in' => $ora,
                    'ora_out' => '',
                    'id_maestro' => '',
                    'richiesto' => '',
                    'id_cliente' => '',
                    'id_cc' => '',
                    'specialita' => '',
                    'ritrovo' => ''
                ];
            }
        }
        return $arr2;
    }

    function findHour($oreMaestro, $ora)
    {
        foreach($oreMaestro as $turno)
        {
            if($turno['ora_in'] == $ora)
            {
                return $turno;
            }
        }
        return false;
    }


    function getOre($giorno)
    {

        $arr = [];$count = 0;
        $row= Ora::where('data', $giorno)->orderBy('ora_in')->get()->toArray();
        return $row;
        foreach($row as $key => $value)
            {
                    if($key == 'id')
                    {
                        $arr[$count][$key] = intval($value);
                    }
                    else
                    {
                        $arr[$count][$key] = $value;
                    }
                    $count++;
            }
        return $arr;
    }


@endphp

@section('content')

@php
    $date = \Carbon\Carbon::now();

    $current_date = $date->dayName . ' ' . $date->format('d') . ' di ' . $date->monthName . ' ' . $date->format('Y');

    $table_hours = [
        '8:00',
        '8:30',
        '9:00',
        '9:30',
        '10:00',
        '10:30',
        '11:00',
        '11:30',
        '12:00',
        '12:30',
        '13:00',
        '13:30',
        '14:00',
        '14:30',
        '15:00',
        '15:30',
        '16:00',
        '16:30',
        '17:00',
        '17:30',
    ];
@endphp

<!-- Day filter -->
<form>
    <div class="row">
        <div class="col-12 col-md-3">
            <b>Giorno:</b>
            <input type="date" name="day" class="form-control" />
        </div>
        <div class="col-12 col-md-3">
            <br />
            <input type="submit" value="Filtra" class="btn btn-primary">
        </div>
    </div>
</form>

<!-- Current day and navigation -->
<div class="row my-4">
    <div class="col-3 col-md-3">
        <<
    </div>
    <div class="col-6 col-md-6 text-center">
        {{$current_date}}
    </div>
    <div class="col-3 col-md-3 text-right">
        >>
    </div>
</div>

<!-- Teachers and hours -->
<table class="table">
    
    <!-- Header -->
    <thead>
        <tr>
            <th scope="col" colspan="2">Teacher</th>

            @foreach($table_hours as $hour)
                <th scope="col">{{$hour}}</th>
            @endforeach

            <th scope="col">Ore</th>
        </tr>
    </thead>

    <!-- Body -->
    <tbody>
        @foreach($teachers as $teacher)
            <tr>
                <!-- Name and phone -->
                <td>
                    <a href="/contacts-master/{{$teacher->contact->id}}" class="" target="_blank">
                        {{$teacher->contact->cognome}} {{$teacher->contact->nome}}
                    </a>
                    <br />
                    <a href="tel:{{$teacher->contact->cellulare}}" class="">
                        {{$teacher->contact->cellulare}}
                    </a>
                </td>

                <!-- Add hour -->
                <td>
                    <button class="btn btn-primary btn-xs"><i class="fa fa-plus"></i></button>
                </td>
                
                @foreach($table_hours as $hour)
                    <td class="text-center">
                        <!-- Teacher bookings -->
                        @if($teacher->hours())
                            {{$teacher->hours}}
                        @endif

                        <button class="btn text-success btn-xs"><i class="fa fa-plus"></i></button>
                    </td>
                @endforeach

                <td>0,0</td>
            </tr>
        @endforeach
    </tbody>

    <!-- Test -->
    <div>
       
    </div>

    <!-- Footer -->
    <thead>
        <tr>
            <th scope="col" colspan="2">Teacher</th>

            @foreach($table_hours as $hour)
                <th scope="col">{{$hour}}</th>
            @endforeach

            <th scope="col">Ore</th>
        </tr>
    </thead>    
</table>

<!-- old code -->
<br /><br /><hr /><hr /><br /><br />

<div class="row">
        <div class="col-12">
            <div class="card">
                <table width="97%" cellpadding="3" cellspacing="0" border="0" align="center" class="tabelle">
                @php
                    $appuntamenti = getOre($giorno_query);
                    $i = 0;
                @endphp

                @foreach ($maestri as $maestro)
                    @php
                        $id_maestro = $maestro->master->id;
                        $nome_maestro = $maestro->nome;
                        $cognome_maestro = $maestro->cognome;
                        $cell = $maestro->cellulare;
                        $colore_maestro = $maestro->color;
                        $id_sede= $maestro->branchContact()->branch_id;

                        $imgAdd = asset('img/aggiungi.png');
                    @endphp
                        <tr>
                            <td class="intestazione" width=\"15%\">
                                <div class="cella_maestro" style="float: left;">
                                    <a href="/contacts-master/{{$id_maestro}}" style="color: white;">{{$cognome_maestro.' '.$nome_maestro }}</a>
                                    <br><span style="font-weight: normal;"><a href="tel:{{$cell}}" style="color: white;">{{$cell}}</a></span>
                                </div>
                                <div style="float: right; padding-right: 10px;">
                                    <a href="#" onClick="openAddOra({{$id_maestro}}, {{$id_sede}})" title="Aggiungi ora"><i align="top" class="fa fa-plus plus-icon"></i></a>
                                </div>
                            </td>

                @php
                    $tot_ore = 0;
                    $lista = oreMaestroPerMezzora(oreMaestro($id_maestro, $appuntamenti));
                @endphp
                @foreach ($lista as $ore)
                            @php
                                    $timeArr = explode(':',$ore['ora_in']);
                                    $ora = intval($timeArr[0]).":".$timeArr[1];
                                    $id_ora = $ore['id'];
                                    $ora_in = $ore['ora_in'];
                                    $ora_out = $ore['ora_out'];
                                    $richiesto = $ore['richiesto'];
                                    $id_cliente = $ore['id_cliente'];
                                    $cc_ora = $ore['id_cc'];
                                    $specialita_ora = $ore['specialita'];
                                    $ritrovo = $ore['ritrovo'];
                                    $aperta = isset($ore['note']) ? $ore['note'] : "";

                                    //$aperta = $ore['note'];
                                    $nome_cliente = "";
                                    if($ora_out != '')
                                    {

                                        $lista_specialita_ora = explode(",", $specialita_ora);
                                        //dd($specialita_ora,$lista_specialita_ora);
                                        if(in_array("1", $lista_specialita_ora))
                                        {
                                            $imgBiberon = asset('img/biberon.png');
                                            $bambino = "<img src=\"$imgBiberon\" align=\"center\" border=\"0\" width=\"15\">";
                                        } else {
                                            $bambino = "";
                                        }

                                        if($ritrovo == "Esterno - Fuori sede")
                                        {
                                            $imgfuori_sede = asset('img/fuori_sede.png');
                                            $ritrovo = "<img src=\"$imgfuori_sede\" align=\"center\" border=\"0\" width=\"15\">";
                                        } else {
                                            $ritrovo = "";
                                        }

                                        if(in_array("16", $lista_specialita_ora))
                                        {
                                            $imgDollaro = asset('img/dollaro.png');
                                            $dollaro = "<img src=\"$imgDollaro\" align=\"center\" border=\"0\" width=\"15\">";
                                        } else {
                                            $dollaro = "";
                                        }

                                        if(in_array("17", $lista_specialita_ora))
                                        {
                                            $imgTelefono = asset('img/telefono.png');
                                            $telefono = "<img src=\"$imgTelefono\" align=\"center\" border=\"0\" width=\"15\">";
                                        } else {
                                            $telefono = "";
                                        }
                                        
                                        if(substr($aperta, 0, 2) == "OA")
                                        {
                                            $imgAperta = asset('img/aperta.png');
                                            $aperta = "<img src=\"$imgAperta\" align=\"center\" border=\"0\" width=\"15\">";
                                        } else {
                                            $aperta = "";
                                        }

                                        list($h_in, $m_in) = explode(":", $ora_in);
                                        list($h_out, $m_out) = explode(":", $ora_out);
                                        $dif_ore = $h_out - $h_in;
                                        $dif_minuti = $m_out - $m_in;
                                        $dif_minuti_ok = ($dif_minuti * 100) / 60;
                                        if($dif_minuti_ok < 0){
                                            $dif_ore--;
                                        }
                                        $dif_minuti_ok = abs($dif_minuti_ok);
                                        $diff = "$dif_ore.$dif_minuti_ok";

                                        $moltiplicatore = ($diff / 0.5);

                                        $width = $moltiplicatore * 4.1;
                                        $width = $width - ($width * 0.045);
                                        //$width = ($diff / 0.5) * 4;
                                        if(substr($id_cliente, 0, 1) == "C"){
                                                $id_collettivo = substr($id_cliente, 2);
                                                //recupero il cliente
                                                $allievi_c =Areaseb\Core\Models\CollettivoAllievi::where('id_collettivo',$id_collettivo)->where('giorno',$giorno_query)->where('id_maestro',$id_maestro)->count();
                                                $collettivo = Areaseb\Core\Models\Collettivo::find($id_collettivo);
                                                $allievi_c = "($allievi_c)";
                                                $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 4px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; height: 49px; z-index: 10;\">
                                                                                            <a href=\"/collective/$id_collettivo\" title=\"Modifica collettivo\">Collettivo ".$collettivo->nome." $allievi_c </a> $bambino $ritrovo $dollaro $telefono $aperta";

                                                $tot_ore = $tot_ore + $diff;

                                        }
                                        elseif($id_cliente != "") {
                                        
                                                list($tipo, $id_cliente) = explode('_', $id_cliente);
                                                
                                                if($tipo == 'T'){
                                                    $contact = Areaseb\Core\Models\Contact::find($id_cliente);
                                                    if($contact != null){
                                                        $contact->disabile = $contact->isDisabled($id_cliente) != null ? "S" : "N";
                                                        $cognome_cliente = $contact->cognome;
                                                        $tel_cliente = $contact->cellulare;
                                                        $cell_cliente = $contact->cellulare;
                                                        $disabile = $contact->disabile;
                                                        $cf = $contact->cod_fiscale;


                                                        if($contact->nickname == ""){
                                                            $nome_cli = "$cognome_cliente";
                                                        } else {
                                                            $nome_cli = "$contact->nickname";
                                                        }

                                                        if($richiesto == "S"){
                                                            $imgRichiesto = asset('img/richiesto.png');
                                                            $nome_cli = $nome_cli . " <img src=\"$imgRichiesto\" align=\"center\" border=\"0\" width=\"15\">";
                                                        }

                                                        if($cell_cliente == ""){
                                                            $ntelefono = $tel_cliente;
                                                        } else {
                                                            $ntelefono = $cell_cliente;
                                                        }

                                                        if($disabile == "S"){
                                                            $imgDisabili = asset('img/disabili.png');
                                                            $cliente_disabile = "<a href=\"/contacts/$id_cliente\" title=\"Visualizza scheda disabile\"><img src=\"$imgDisabili\" align=\"center\" border=\"0\" width=\"15\"></a>";
                                                        } else {
                                                            $cliente_disabile = "";
                                                        }

                                                        if($cf == ""){
                                                            $imgda_compilare = asset('img/da_compilare.png');
                                                            $cf = "<a href=\"/contacts/$id_cliente\" title=\"Modifica cliente\"><img src=\"$imgda_compilare\" align=\"center\" border=\"0\" width=\"15\"></a>";
                                                        } else {
                                                            $cf = "";
                                                        }

                                                        $elem = Areaseb\Core\Models\Invoice::select('invoices.*')->join('invoice_ora','invoice_ora.invoice_id','=','invoices.id')
                                                                                            ->where('invoice_ora.ora_id',$id_ora)
                                                                                            ->first();


                                                        $img_attenzione = asset('img/attenzione.png');
                                                        if($elem != null && $elem->saldato == 0){
                                                            $link_fattura = 'invoices/'.$elem->id.'/edit';
                                                            $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 4px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
                                                                            <a href=\"#\" onClick=\"openModOra($id_ora)\" title=\"Modifica ora\"><b>$nome_cli</b></a> $cliente_disabile $bambino $ritrovo $cf $dollaro $telefono $aperta <a href=\"/$link_fattura\" title=\"Anteprima fattura\"><img src=\"$img_attenzione\" align=\"center\" border=\"0\" width=\"15\" width=\"15\"></a><br>
                                                                                                    <a href=\"tel:$ntelefono\">$ntelefono</a>";
                                                            $tot_ore = $tot_ore + $diff;
                                                        } elseif($elem != null && $elem->saldato == 1){
                                                            $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 4px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
                                                                            <a href=\"#\" onClick=\"openModOra($id_ora)\" title=\"Modifica ora\"><b>$nome_cli</b></a> $cliente_disabile $bambino $ritrovo $cf $dollaro $telefono $aperta<br>
                                                                                                    <a href=\"tel:$ntelefono\">$ntelefono</a>";
                                                            $tot_ore = $tot_ore + $diff;
                                                        } 


                                                        //dd($cognome_cliente);

                                                    }
                                                }
                                                
                                                if($tipo == 'Y'){
                                                    $contact = Areaseb\Core\Models\Company::find($id_cliente);
                                                    if($contact != null){
                                                        $cognome_cliente = $contact->rag_soc;
                                                        $tel_cliente = $contact->phone;
                                                        $cell_cliente = $contact->mobile;
                                                        $cf = $contact->cf;


                                                        if($contact->nickname == ""){
                                                            $nome_cli = "$cognome_cliente";
                                                        } else {
                                                            $nome_cli = "$contact->nickname";
                                                        }

                                                        if($richiesto == "S"){
                                                            $imgRichiesto = asset('img/richiesto.png');
                                                            $nome_cli = $nome_cli . " <img src=\"$imgRichiesto\" align=\"center\" border=\"0\">";
                                                        }

                                                        if($cell_cliente == ""){
                                                            $ntelefono = $tel_cliente;
                                                        } else {
                                                            $ntelefono = $cell_cliente;
                                                        }

                                                        $cliente_disabile = "";

                                                        if($cf == ""){
                                                            $imgda_compilare = asset('img/da_compilare.png');
                                                            $cf = "<a href=\"/contacts/$id_cliente\" title=\"Modifica cliente\"><img src=\"$imgda_compilare\" align=\"center\" border=\"0\"></a>";
                                                        } else {
                                                            $cf = "";
                                                        }

                                                        $elem = Areaseb\Core\Models\Invoice::select('invoices.*')->join('invoice_ora','invoice_ora.invoice_id','=','invoices.id')
                                                                                            ->where('invoice_ora.ora_id',$id_ora)
                                                                                            ->first();


                                                        $img_attenzione = asset('img/attenzione.png');
                                                        if($elem != null && $elem->saldato == 0){
                                                            $link_fattura = 'invoices/'.$elem->id.'/edit';
                                                            $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 4px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
                                                                            <a href=\"#\" onClick=\"openModOra($id_ora)\" title=\"Modifica ora\"><b>$nome_cli</b></a> $cliente_disabile $bambino $ritrovo $cf $dollaro $telefono $aperta <a href=\"/$link_fattura\" title=\"Anteprima fattura\"><img src=\"$img_attenzione\" align=\"center\" border=\"0\" width=\"15\"></a><br>
                                                                                                    <a href=\"tel:$ntelefono\">$ntelefono</a>";
                                                            $tot_ore = $tot_ore + $diff;
                                                        } elseif($elem != null && $elem->saldato == 1){
                                                            $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 4px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
                                                                            <a href=\"#\" onClick=\"openModOra($id_ora)\" title=\"Modifica ora\"><b>$nome_cli</b></a> $cliente_disabile $bambino $ritrovo $cf $dollaro $telefono $aperta<br>
                                                                                                    <a href=\"tel:$ntelefono\">$ntelefono</a>";
                                                            $tot_ore = $tot_ore + $diff;
                                                        } 


                                                        //dd($cognome_cliente);

                                                    }
                                                }
                                                
                                                if($tipo == 'L')
                                                {
                                                    $label = Areaseb\Core\Models\Label::find($id_cliente);
                                                    $nome_cli = $label->nome;
                                                    $colore = $label->colore;

                    
                                                    $elem = Areaseb\Core\Models\Invoice::select('invoices.*')->join('invoice_ora','invoice_ora.invoice_id','=','invoices.id')
                                                                                                ->where('invoice_ora.ora_id',$id_ora)
                                                                                                ->first();

                                                    $saldo = "";
                                                    
                                                    $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 4px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore; width: ".$width."%; z-index: 10;\">
                                                    <a href=\"#\" onClick=\"openModOra($id_ora)\" title=\"Modifica ora\"><b>$nome_cli</b></a><br><br>";
                                                    
                    
                                                }
                                            }
                                            elseif($id_cliente == "" && $ora_out != ""){

                                                $imgAttenzione = asset('img/attenzione.png');
                                                $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 4px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
                                                                                    ";
                                                $tot_ore = $tot_ore + $diff;
                                            } else {
                                                $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 4px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: red; width: ".$width."%; z-index: 10;\">
                                                                                        <img src=\"../img/attenzione.png\" align=\"top\" border=\"0\" height=\"30%\"> <img src=\"../img/attenzione.png\" align=\"top\" border=\"0\" height=\"30%\"> <a onClick=\"javascript: window.open('worker/modora.php?id=$id_ora&giorno=$giorno&avanzamento=$avanzamento', 'modora', 'width=900,height=580,left=150,top=50,scrollbars=yes');\" title=\"Modifica ora\" style=\"color: white;\"><b>ATTENZIONE !!!<br>Cliente non impostato !</b></a> <img src=\"../img/attenzione.png\" align=\"top\" border=\"0\" height=\"30%\"> <img src=\"../img/attenzione.png\" align=\"top\" border=\"0\" height=\"30%\">";
                                            }
                                    }
                                    if($ora_in != ""){
                                        if($nome_cliente == ""){
                                            $ora = explode(":",$ora);
                                            $dateFromModal = strval($g).'/'.strval($m).'/'.strval($a).'/'.strval($ora[0]).'/'.strval($ora[1]);
                                            $nome_cliente = "<a href=\"#\" onClick=\"openAddOraPreimpostata($ora[0],$ora[1],$id_maestro,$id_sede)\"  title=\"Aggiungi ora\"><i align=\"top\" class=\"fa fa-plus plus-icon-tab text-success\"></i></a>";
                                            $align = "center";
                                        } else {
                                            $align = "left";
                                        }
                                        echo "<td align=\"$align\" valign=\"top\" width=\"4%\">
                                                    $nome_cliente
                                                </div>
                                            </td>";
                                        $nome_cliente = '';
                                    }
                                    else {                    
                                        $availability = Areaseb\Core\Models\Availability::where('contact_id',$id_maestro)->where('data_start','<=', $giorno_query)->where('data_end','>=', $giorno_query)->first();

                                        $id_disp = $availability->id;

                                        if($id_disp != ""){
                                            $ora = explode(":",$ora);
                                            $dateFromModal = strval($g).'/'.strval($m).'/'.strval($a).'/'.strval($ora[0]).'/'.strval($ora[1]);
                                            echo  "<td align=\"center\" valign=\"top\" width=\"4%\">
                                            <a href=\"#\" onClick=\"openAddOraPreimpostata($ora[0],$ora[1],$id_maestro,$id_sede)\"  title=\"Aggiungi ora\"><i align=\"top\" class=\"fa fa-plus plus-icon-tab text-success\"></i></a></td>";
                                        } else {
                                            echo  "<td bgcolor=\"black\">&nbsp;</td>";
                                        }

                                        $id_disp = "";
                                    }
                                    $telefono = "";
                                    $cliente_disabile = "";
                                    $ora_in = "";

                            @endphp
                    @endforeach




                @php

                //fine ciclo appuntamenti
                    $tot_ore = number_format($tot_ore, 1, ",", ".");
                    echo "<td class=\"intestazione\" align=\"center\" width=\"5%\">$tot_ore</td>";

                    $tot_ore = 0;
                    $id_maestro = "";
                    $query2 = "";
                    $nome_cliente = "";

                    $i++;
                @endphp
                @endforeach
                </table>
                <table width="97%" cellpadding="3" cellspacing="0" border="0" align="center" class="tabelle">
                    <tr>
                        <td class="intestazione" width="14%">&nbsp;</td>


                @php
                for($o = 8; $o <= 17.5; $o = $o + 0.5){
                    $elem = explode(".", $o);
                    $intero = $elem[0];
                    $resto = isset($elem[1]) ? $elem[1] : 0;

                    if(!$ipad && !$iphone && !$android && !$webos && !$ipod){

                        if($resto == 0){
                            $ora = "$intero:00";
                        } else {
                            $ora = "$intero:30";
                        }

                    } else {

                        if($resto == 0){
                            $ora = "$intero";
                        } else {
                            $ora = "";
                        }

                    }

                    echo "<td class=\"intestazione\" align=\"left\" width=\"4%\">$ora</td>";

                }
                @endphp

                <td class="intestazione" width="7%" align="center" style="padding-left: 1.3%">ORE</td>
                                </tr>
                            </table>
                            <br><br>
                </div>



                <!-- MODAL AGGIUNGI -->
                <div class="modal fade" id="modalAggiungi" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
                    aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5>Inserisci nuova ora</h5>
                                
                            </div>
                            <div class="modal-body">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td width="45%" valign="top">
                                                    <table width="100%" cellpadding="3" cellspacing="3">
                                                        <tbody>
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Collettivo</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <select name="collettivo" class="form-control"  id="collettivo_id_modal_add">
                                                                        <option value="">- Scegli -</option>
                                                                        @foreach ($collettivi as $item)
                                                                            <option value="{{ $item->id }}">{{ $item->nome }}</option>
                                                                        @endforeach 
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Azienda</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <div class="row" style="margin:0 !important">
                                                                    
                                                                        <select name="cliente"  class="form-control col-md-5"  id="cliente_id_modal_add">
                                                                            <option value="">- Scegli Azienda -</option>
                                                                            @foreach ($clienti as $item)
                                                                                <option value="{{ $item->id }}">{{ $item->rag_soc }}</option>
                                                                            @endforeach 
                                                                        </select>
                                                                        &nbsp;
                                                                        <select name="partecipante"  class="form-control col-md-5"  id="partecipante_id_modal_add">
                                                                            <option value="">- Scegli Privato -</option>
                                                                            @foreach ($contacts as $item)
                                                                                <option value="{{ $item->id }}">{{ $item->nome.' '.$item->cognome }}</option>
                                                                            @endforeach 
                                                                        </select>
                                                                        
                                                                        &nbsp;
                                                                        <a href="#" onclick="gotoCreate()" class="btn btn-sm btn-primary btn-block col-md-2"><b> <i class="fa fa-plus"></i></b></a>

                                                                    </div>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>	
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Segnaposto</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <div class="row" style="margin:0 !important">
                                                                    
                                                                        <select name="label"  class="form-control col-md-6"  id="label_id_modal_add">
                                                                            <option value="">- Scegli -</option>
                                                                            @foreach ($labels as $item)
                                                                                <option value="{{ $item->id }}">{{ $item->nome }}</option>
                                                                            @endforeach 
                                                                        </select>

                                                                    </div>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>	
                                                            <tr>
                                                                <td colspan="3"><br></td>
                                                            </tr>
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Alloggio</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <select name="alloggio"  class="form-control" id="alloggio_modal_add">
                                                                        <option value="">- Scegli -</option>
                                                                        @foreach ($alloggi as $item)
                                                                            <option value="{{ $item->id }}">{{ $item->luogo }}</option>
                                                                        @endforeach 
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>	
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b class="pax_modal_add_enabled">Pax</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <input type="text" name="pax" size="2"  id="pax_modal_add" class="form-item pax_modal_add_enabled" maxlength="2" value="1"> &nbsp;&nbsp;&nbsp; <!--<b>Sci club</b> <input type="checkbox" name="sciclub" id="sciclub_modal_add">-->
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>	
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Data</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <input type="date" name="data_in"  class="form-item" id="data_in_modal_add"> 
                                                                    <div style="display: inline;">
                                                                        <b>Al</b> 
                                                                        <input type="date" name="data_out"  onchange="document.getElementById('frequenza').style.display='table-row';" class="form-item" id="data_out_modal_add">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>
                                                            <tr id="frequenza" style="display: none;">
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Frequenza</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <input type="checkbox" name="freq_1" id="freq_1" value="S"> Lunedì &nbsp;&nbsp; 
                                                                    <input type="checkbox" name="freq_2" id="freq_2" value="S"> Martedì &nbsp;&nbsp; 
                                                                    <input type="checkbox" name="freq_3" id="freq_3" value="S"> Mercoledì &nbsp;&nbsp; <br>
                                                                    <input type="checkbox" name="freq_4" id="freq_4" value="S"> Giovedì &nbsp;&nbsp; 
                                                                    <input type="checkbox" name="freq_5" id="freq_5" value="S"> Venerdì &nbsp;&nbsp; 
                                                                    <input type="checkbox" name="freq_6" id="freq_6" value="S"> Sabato &nbsp;&nbsp; <br>
                                                                    <input type="checkbox" name="freq_0" id="freq_0" value="S"> Domenica 
                                                                    <br><br>oppure<br><br>
                                                                    <input type="checkbox" name="freq_C" id="freq_C" value="S"> Continuativo
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Dalle ore</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <select name="ora_in"  class="form-item" id="ora_in_modal_add">
                                                                        <option value="">- Scegli -</option>
                                                                        <option value=""></option>
                                                                        <option value="08:00">08:00</option>
                                                                        <option value="08:30">08:30</option>
                                                                        <option value="09:00">09:00</option>
                                                                        <option value="09:30">09:30</option>
                                                                        <option value="10:00">10:00</option>
                                                                        <option value="10:30">10:30</option>
                                                                        <option value="11:00">11:00</option>
                                                                        <option value="11:30">11:30</option>
                                                                        <option value="12:00">12:00</option>
                                                                        <option value="12:30">12:30</option>
                                                                        <option value="13:00">13:00</option>
                                                                        <option value="13:30">13:30</option>
                                                                        <option value="14:00">14:00</option>
                                                                        <option value="14:30">14:30</option>
                                                                        <option value="15:00">15:00</option>
                                                                        <option value="15:30">15:30</option>
                                                                        <option value="16:00">16:00</option>
                                                                        <option value="16:30">16:30</option>
                                                                        <option value="17:00">17:00</option>
                                                                        <option value="17:30">17:30</option>
                                                                        <option value="18:00">18:00</option>
                                                                    </select>
                                                                    &nbsp;&nbsp;&nbsp;
                                                                    <b>Alle ore</b>
                                                                    &nbsp;
                                                                    <select name="ora_out"  class="form-item" id="ora_out_modal_add">
                                                                        <option value="">- Scegli -</option>
                                                                        <option value=""></option>
                                                                        <option value="08:00">08:00</option>
                                                                        <option value="08:30">08:30</option>
                                                                        <option value="09:00">09:00</option>
                                                                        <option value="09:30">09:30</option>
                                                                        <option value="10:00">10:00</option>
                                                                        <option value="10:30">10:30</option>
                                                                        <option value="11:00">11:00</option>
                                                                        <option value="11:30">11:30</option>
                                                                        <option value="12:00">12:00</option>
                                                                        <option value="12:30">12:30</option>
                                                                        <option value="13:00">13:00</option>
                                                                        <option value="13:30">13:30</option>
                                                                        <option value="14:00">14:00</option>
                                                                        <option value="14:30">14:30</option>
                                                                        <option value="15:00">15:00</option>
                                                                        <option value="15:30">15:30</option>
                                                                        <option value="16:00">16:00</option>
                                                                        <option value="16:30">16:30</option>
                                                                        <option value="17:00">17:00</option>
                                                                        <option value="17:30">17:30</option>
                                                                        <option value="18:00">18:00</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>	
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Ritrovo</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <select name="ritrovo"  class="form-control" id="ritrovo_modal_add">
                                                                        <option value="">- Scegli -</option> 
                                                                        @foreach ($ritrovi as $item)
                                                                            <option value="{{ $item->id }}">{{ $item->luogo }}</option>
                                                                        @endforeach   
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>	
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Disciplina</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <select name="disciplina" class="form-control" id="disciplina_modal_add">
                                                                        <option value="">- Scegli -</option>
                                                                        <option value="1" >Discesa</option>
                                                                        <option value="2">Fondo - Classico </option>
                                                                        <option value="3">Fondo - Skating </option>
                                                                        <option value="4">Snowboard</option>					
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>	
                                                            <tr id="tr_livello">
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Livello</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <select name="livello" id="livello_modal_add"  class="form-control">
                                                                        <option value="PRA">Primo Approccio</option>
                                                                        <option value="ELE">Elementare</option>
                                                                        <option value="BAS">Base</option>
                                                                        <option value="INT">Intermedio</option>
                                                                        <option value="AVA">Avanzato</option>
                                                                    </select>
                                                                    </td>
                                                            </tr>
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Venditore</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <select name="venditore"  id="venditore_modal_add" class="form-control">
                                                                        <option value="">- Scegli -</option>
                                                                        <option value=""></option>
                                                                        <option value="S" selected="">Segreteria</option>
                                                                        <option value="N">Noleggio</option>
                                                                        <option value="M">Maestro</option>
                                                                        <option value="P">Prevendita</option>
                                                                        <option value="H">Altro</option>
                                                                    </select>
                                                                    <div id="divmaestro" style="display: none">
                                                                        <br>
                                                                        <select name="maestro_v" id="maestro_v_modal_add" class="form-control">
                                                                            <option value="">- Scegli maestro -</option>  
                                                                            @foreach ($maestri_list as $item)
                                                                                <option value="{{ $item->master->id }}">{{ $item->nome.' '.$item->cognome }}</option>
                                                                            @endforeach    
                                                                        </select>
                                                                    </div>
                                                                </td>
                                                            </tr>							    			
                                                            <tr @if(!auth()->user()->hasRole('super')) style="display: none" @endif>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Sede</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                {!! Form::select('branch_id_modal_add',$branches, null, ['class' => 'form-control', 'data-placeholder' => 'Associa una sede', 'data-fouc', 'style' => 'width:100%']) !!}

                                                                </td>
                                                            </tr> 	
                                                        </tbody>
                                                    </table>
                                                </td>
                                                <td valign="top">
                                                    <table width="100%" cellpadding="3" cellspacing="3">
                                                        <tbody>
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    &nbsp;
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <div align="center"><b>Persona</b></div>
                                                                            <table width="100%" cellpadding="0" cellspacing="0" class="tabelle">
                                                                                    <tbody><tr><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="2" value="S" class="specialita_modal_add"> Adulto
                                                                                    </td><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="1" value="S" class="specialita_modal_add"> Bambino (3-6)
                                                                                    </td></tr>
                                                                                        <tr><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="3" value="S" class="specialita_modal_add"> Disabile
                                                                                    </td><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="15" value="S" class="specialita_modal_add"> Ragazzo (7-14)
                                                                                    </td></tr>
                                                                                        <tr>	</tr>
                                                                                </tbody>
                                                                            </table>
                                                                            <br>
                                                                            <div align="center"><b>Lingua</b></div>
                                                                            <table width="100%" cellpadding="0" cellspacing="0" class="tabelle">
                                                                                    <tbody><tr><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="9" value="S" class="specialita_modal_add"> Francese
                                                                                    </td><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="8" value="S" class="specialita_modal_add"> Inglese
                                                                                    </td></tr>
                                                                                        <tr><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="14" value="S" class="specialita_modal_add"> Polacco
                                                                                    </td><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="13" value="S" class="specialita_modal_add"> Tedesco
                                                                                    </td></tr>
                                                                                        <tr>	</tr>
                                                                                </tbody>
                                                                            </table>
                                                                            <br>
                                                                            
                                                                            <div align="center"><b>Segreteria</b></div>
                                                                            <table width="100%" cellpadding="0" cellspacing="0" class="tabelle">
                                                                                    <tbody><tr><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="16" value="S" class="specialita_modal_add"> Pagamento in pista
                                                                                    </td><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="17" value="S" class="specialita_modal_add"> Prenotazione telefonica
                                                                                    </td></tr>
                                                                                        <tr>	</tr>
                                                                                </tbody>
                                                                            </table>
                                                                            <br>				
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>		
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Note lezione</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <textarea name="note" cols="34" class="form-control" id="note_lez_modal_add" rows="3"></textarea>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr> 	
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
								        </tbody>
                                    </table>
                                    <h5 id="maestro_id_modal_add" name="maestro" hidden></h5>
	
                            </div>

                            <div class="alert alert-info" style="display:none">
                                <strong>Attenzione! Operazione fallita!</strong> 
                            </div>


                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="closemodalAggiungi">Annulla</button>
                                <button type="button" class="btn btn-primary" id="btnsave">Salva</button>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal fade" id="modalModifica" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
                    aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5>Modifica ora</h5>
                                
                            </div>
                            <div class="modal-body">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td width="45%" valign="top">
                                                    <table width="100%" cellpadding="3" cellspacing="3">
                                                        <tbody>
                                                            <tr id="tr_modal_upd">
                                                                
                                                            </tr>
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Maestro</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                <select name="maestro_v" id="maestro_modal_upd" class="form-control">
                                                                            <option value="">- Scegli maestro -</option>  
                                                                            @foreach ($maestri_list as $item)
                                                                                <option value="{{ $item->master->id }}">{{ $item->nome.' '.$item->cognome }}</option>
                                                                            @endforeach    
                                                                        </select>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>	


                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Alloggio</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <select name="alloggio"  class="form-control" id="alloggio_modal_upd">
                                                                        <option value="">- Scegli -</option>
                                                                        @foreach ($alloggi as $item)
                                                                            <option value="{{ $item->id }}">{{ $item->luogo }}</option>
                                                                        @endforeach 
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>	
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b class="pax_modal_upd_enabled">Pax</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <input type="text" name="pax" size="2"  id="pax_modal_upd" class="form-item pax_modal_upd_enabled" maxlength="2" value="1"> &nbsp;&nbsp;&nbsp; <!--<b>Sci club</b> <input type="checkbox" name="sciclub" id="sciclub_modal_upd">-->
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>	
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Data</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <input type="date" name="data_in"  class="form-item" id="data_in_modal_upd"> 
                                                                    <div style="display: none;">
                                                                        <b>Al</b> 
                                                                        <input type="date" name="data_out"  class="form-item" id="data_out_modal_upd">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>
                                                            <tr id="frequenza" style="display: none;">
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Frequenza</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                <input type="checkbox" name="freq_1" id="freq_1_upd" value="S"> Lunedì &nbsp;&nbsp; 
                                                                    <input type="checkbox" name="freq_2" id="freq_2_upd" value="S"> Martedì &nbsp;&nbsp; 
                                                                    <input type="checkbox" name="freq_3" id="freq_3_upd" value="S"> Mercoledì &nbsp;&nbsp; <br>
                                                                    <input type="checkbox" name="freq_4" id="freq_4_upd" value="S"> Giovedì &nbsp;&nbsp; 
                                                                    <input type="checkbox" name="freq_5" id="freq_5_upd" value="S"> Venerdì &nbsp;&nbsp; 
                                                                    <input type="checkbox" name="freq_6" id="freq_6_upd" value="S"> Sabato &nbsp;&nbsp; <br>
                                                                    <input type="checkbox" name="freq_0" id="freq_0_upd" value="S"> Domenica 
                                                                    <br><br>oppure<br><br>
                                                                    <input type="checkbox" name="freq_C" id="freq_C_upd" value="S"> Continuativo
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Dalle ore</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <select name="ora_in"  class="form-item" id="ora_in_modal_upd">
                                                                        <option value="">- Scegli -</option>
                                                                        <option value=""></option>
                                                                        <option value="08:00">08:00</option>
                                                                        <option value="08:30">08:30</option>
                                                                        <option value="09:00">09:00</option>
                                                                        <option value="09:30">09:30</option>
                                                                        <option value="10:00">10:00</option>
                                                                        <option value="10:30">10:30</option>
                                                                        <option value="11:00">11:00</option>
                                                                        <option value="11:30">11:30</option>
                                                                        <option value="12:00">12:00</option>
                                                                        <option value="12:30">12:30</option>
                                                                        <option value="13:00">13:00</option>
                                                                        <option value="13:30">13:30</option>
                                                                        <option value="14:00">14:00</option>
                                                                        <option value="14:30">14:30</option>
                                                                        <option value="15:00">15:00</option>
                                                                        <option value="15:30">15:30</option>
                                                                        <option value="16:00">16:00</option>
                                                                        <option value="16:30">16:30</option>
                                                                        <option value="17:00">17:00</option>
                                                                        <option value="17:30">17:30</option>
                                                                        <option value="18:00">18:00</option>
                                                                    </select>
                                                                    &nbsp;&nbsp;&nbsp;
                                                                    <b>Alle ore</b>
                                                                    &nbsp;
                                                                    <select name="ora_out"  class="form-item" id="ora_out_modal_upd">
                                                                        <option value="">- Scegli -</option>
                                                                        <option value=""></option>
                                                                        <option value="08:00">08:00</option>
                                                                        <option value="08:30">08:30</option>
                                                                        <option value="09:00">09:00</option>
                                                                        <option value="09:30">09:30</option>
                                                                        <option value="10:00">10:00</option>
                                                                        <option value="10:30">10:30</option>
                                                                        <option value="11:00">11:00</option>
                                                                        <option value="11:30">11:30</option>
                                                                        <option value="12:00">12:00</option>
                                                                        <option value="12:30">12:30</option>
                                                                        <option value="13:00">13:00</option>
                                                                        <option value="13:30">13:30</option>
                                                                        <option value="14:00">14:00</option>
                                                                        <option value="14:30">14:30</option>
                                                                        <option value="15:00">15:00</option>
                                                                        <option value="15:30">15:30</option>
                                                                        <option value="16:00">16:00</option>
                                                                        <option value="16:30">16:30</option>
                                                                        <option value="17:00">17:00</option>
                                                                        <option value="17:30">17:30</option>
                                                                        <option value="18:00">18:00</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>	
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Ritrovo</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <select name="ritrovo"  class="form-control" id="ritrovo_modal_upd">
                                                                        <option value="">- Scegli -</option> 
                                                                        @foreach ($ritrovi as $item)
                                                                            <option value="{{ $item->id }}">{{ $item->luogo }}</option>
                                                                        @endforeach   
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>	
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Disciplina</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <select name="disciplina" class="form-control" id="disciplina_modal_upd">
                                                                    <option value="">- Scegli -</option>
                                                                        <option value="1" >Discesa</option>
                                                                        <option value="2">Fondo - Classico </option>
                                                                        <option value="2">Fondo - Skating </option>
                                                                        <option value="4">Snowboard</option>					
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>	
                                                            <tr id="tr_livello_upd">
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Livello</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <select name="livello" id="livello_modal_upd"  class="form-control">
                                                                        <option value="PRA">Primo Approccio</option>
                                                                        <option value="ELE">Elementare</option>
                                                                        <option value="BAS">Base</option>
                                                                        <option value="INT">Intermedio</option>
                                                                        <option value="AVA">Avanzato</option>
                                                                    </select>
                                                                    </td>
                                                            </tr>
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Venditore</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <select name="venditore"  id="venditore_modal_upd" class="form-control">
                                                                        <option value="">- Scegli -</option>
                                                                        <option value=""></option>
                                                                        <option value="S" selected="">Segreteria</option>
                                                                        <option value="N">Noleggio</option>
                                                                        <option value="M">Maestro</option>
                                                                        <option value="P">Prevendita</option>
                                                                        <option value="H">Altro</option>
                                                                    </select>
                                                                    <div id="divmaestro_upd" style="display: none">
                                                                        <br>
                                                                        <select name="maestro_v" id="maestro_v_modal_upd" class="form-control">
                                                                            <option value="">- Scegli maestro -</option>  
                                                                            @foreach ($maestri_list as $item)
                                                                                <option value="{{ $item->master->id }}">{{ $item->nome.' '.$item->cognome }}</option>
                                                                            @endforeach    
                                                                        </select>
                                                                    </div>
                                                                </td>
                                                            </tr> 	 							    			
                                                            <tr @if(!auth()->user()->hasRole('super')) style="display: none" @endif>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Sede</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                {!! Form::select('branch_id_modal_upd[]',$branches, null, ['class' => 'select2 ucc', 'data-placeholder' => 'Associa uno o più sedi', 'data-fouc', 'style' => 'width:100%']) !!}

                                                                </td>
                                                            </tr> 	
                                                        </tbody>
                                                    </table>
                                                </td>
                                                <td valign="top">
                                                    <table width="100%" cellpadding="3" cellspacing="3">
                                                        <tbody>
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    &nbsp;
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <div align="center"><b>Persona</b></div>
                                                                            <table width="100%" cellpadding="0" cellspacing="0" class="tabelle">
                                                                                    <tbody><tr><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="2_upd" value="S" class="specialita_modal_upd"> Adulto
                                                                                    </td><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="1_upd" value="S" class="specialita_modal_upd"> Bambino (3-6)
                                                                                    </td></tr>
                                                                                        <tr><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="3_upd" value="S" class="specialita_modal_upd"> Disabile
                                                                                    </td><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="15_upd" value="S" class="specialita_modal_upd"> Ragazzo (7-14)
                                                                                    </td></tr>
                                                                                        <tr>	</tr>
                                                                                </tbody>
                                                                            </table>
                                                                            <br>
                                                                            <div align="center"><b>Lingua</b></div>
                                                                            <table width="100%" cellpadding="0" cellspacing="0" class="tabelle">
                                                                                    <tbody><tr><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="9_upd" value="S" class="specialita_modal_upd"> Francese
                                                                                    </td><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="8_upd" value="S" class="specialita_modal_upd"> Inglese
                                                                                    </td></tr>
                                                                                        <tr><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="14_upd" value="S" class="specialita_modal_upd"> Polacco
                                                                                    </td><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="13_upd" value="S" class="specialita_modal_upd"> Tedesco
                                                                                    </td></tr>
                                                                                        <tr>	</tr>
                                                                                </tbody>
                                                                            </table>
                                                                            
                                                                            
                                                                            <br>
                                                                            <div align="center"><b>Segreteria</b></div>
                                                                            <table width="100%" cellpadding="0" cellspacing="0" class="tabelle">
                                                                                    <tbody><tr><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="16_upd" value="S" class="specialita_modal_upd"> Pagamento in pista
                                                                                    </td><td align="left" width="50%" class="td-modal">
                                                                                        <input type="checkbox" id="17_upd" value="S" class="specialita_modal_upd"> Prenotazione telefonica
                                                                                    </td></tr>
                                                                                        <tr>	</tr>
                                                                                </tbody>
                                                                            </table>
                                                                            <br>				
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr>		
                                                            <tr>
                                                                <td width="20%" class="testo" align="right" valign="top">
                                                                    <b>Note lezione</b>
                                                                </td>
                                                                <td width="80%" class="testo" align="left" valign="top">
                                                                    <textarea name="note" cols="34" class="form-control" id="note_lez_modal_upd" rows="3"></textarea>
                                                                </td>
                                                                <td>
                                                                </td>
                                                            </tr> 	
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
								        </tbody>
                                    </table>
                                    <h5 id="record_id_modal_upd" name="record" hidden></h5>
                                    <h5 id="invoice_id_modal_upd" hidden></h5>
	
                            </div>

                            <div class="alert alert-info" style="display:none">
                                <strong>Attenzione!</strong> Operazione fallita!
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="closemodalModifica">Annulla</button>
                                <button type="button" class="btn btn-primary" id="btnupdate">Modifica</button>
                                <button type="button" class="btn btn-warning" id="btnFattura">Fattura</button>
                                <button type="button" class="btn btn-warning" id="btnopenModalDoc">Aggiungi a Documento</button>
                                <button type="button" class="btn btn-danger" id="btndelete">Elimina</button>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="modal fade" id="modalAddByCliente" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5>Completa Inserimento nuova ora</h5>
                                <h5 id="oraIDByCliente" hidden></h5>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <select class="form-control"  id="maestro_id_modal_addCliente">
                                            <option value="">- Scegli -</option>
                                        </select>
                                    </div> 
                                    <div class="col-md-6">
                                        <b>Maestro richiesto</b> <input type="checkbox" name="sciclub" id="richiesto_modal_add">
                                    </div> 
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="closemodalAggiungiCliente">Annulla</button>
                                <button type="button" class="btn btn-warning" id="btnsaveCliente">Lascia ora aperta</button>
                                <button type="button" class="btn btn-success" id="btnclosedCliente">Termina</button>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal fade" id="modalAddByCollettivo" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5>Completa Inserimento nuova ora</h5>
                                <h5 id="oraIDByCollettivo" hidden></h5>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                    <b>Livello dell'alievo</b>
					  					<select name=\"livello\" id="livelloCollettivo">
				    						<option value=\"\">- Scegli -</option>
				    						<optgroup label=\"Bronzo\">
					    						<option value=\"B_B\">Base</option>
					    						<option value=\"B_I\">Intermedio</option>
					    						<option value=\"B_A\">Avanzato</option>
					    					</optgroup>
					    					<optgroup label=\"Argento\">
					    						<option value=\"A_B\">Base</option>
					    						<option value=\"A_I\">Intermedio</option>
					    						<option value=\"A_A\">Avanzato</option>
					    					</optgroup>
					    					<optgroup label=\"Oro\">
					    						<option value=\"O_B\">Base</option>
					    						<option value=\"O_I\">Intermedio</option>
					    						<option value=\"O_A\">Avanzato</option>
					    					</optgroup>
				    					</select>
                                    </div> 
                                    <div class="col-md-6">
                                        <b>Età</b> <input type="text" id="eta_modal_add" > 
                                    </div> 
                                </div>
                            </div>
                            <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" id="closemodalAggiungiCollettivo">Annulla</button>
                                <button type="button" class="btn btn-warning" id="btnsaveCollettivo">Lascia ora aperta</button>
                                <button type="button" class="btn btn-success" id="btnclosedCollettivo">Termina</button>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Modal Delete -->
                <div class="modal fade" id="modalEliminazioneOra" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5>Eliminazione Ora</h5>
                                <h5 id="oraIDRemove" hidden></h5>
                                <h5 id="invoiceIDRemove" hidden></h5>
                            </div>
                            <div class="modal-body">
                                <h4>Sei sicuro di voler eliminare la seguente ora?</h4>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="closemodalEliminazioneOra">Annulla</button>
                                <button type="button" class="btn btn-danger" id="btnRemoveOra"><i
                                        class="fa fa-trash mr-1"></i>Conferma</button>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal fade" id="modalAddDoc" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5>Inserisci i dati del documento in cui aggiungere l'ora</h5>
                                <h5 id="ccID" hidden></h5>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <b>Sede: </b> <label id="lbl_cc"></label>
                                    </div> 
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                    <b>Tipo di documento: </b>
                                        <select class="form-control"  id="tipo_doc">
                                            <option value="R">Ricevuta</option>
                                            <option value="F">Fattura</option>
                                            <option value="D">Documento</option>
                                            <option value="A">Nota di accredito</option>
                                        </select>
                                    </div> 
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <b>N° documento: </b><input type="text" id="n_doc" class="form-control">
                                    </div> 
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <b>Data documento: </b><input type="date" id="data_doc" class="form-control">
                                    </div> 
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                    <b>Prodotto:</b>
                                    {!! Form::select('prodotto_doc',$products, null, ['class' => 'select2 ucc', 'data-fouc', 'style' => 'width:100%']) !!}

                                    </div> 
                                </div>

                                <br>
                                <div class="alert alert-info alert-info-add-doc" style="display:none">
                                    <strong>Attenzione!</strong> Documento non trovato!
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="closemodalAddDoc">Annulla</button>
                                <button type="button" class="btn btn-success" id="btnAddDoc">Inserisci</button>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

@stop

@section('scripts')
    <script src="{{asset('plugins/jquery-ui/jquery-ui.min.js')}}"></script>
    <script>

    var giorno_output = '<?php echo $giorno_output; ?>';
    var branches = <?php echo json_encode($branches); ?>;
    sessionStorage.clear();



    var collettivi = <?php echo json_encode($collettivi); ?>;
    var clienti = <?php echo json_encode($clienti); ?>;
    var contacts = <?php echo json_encode($contacts); ?>;


    function preimpostaCollection(id_sede){
        const filteredCollettivi = collettivi.filter(val => val.centro_costo == id_sede);
        console.log(collettivi, filteredCollettivi);
        $("#collettivo_id_modal_add").select2('destroy').empty();
        $('#collettivo_id_modal_add').append('<option value="">- Scegli Collettivo -</option>');    
        filteredCollettivi.forEach(element => {
            $('#collettivo_id_modal_add').append('<option value="'+element.id+'">'+element.nome+'</option>');    
        });
        $('#collettivo_id_modal_add').select2({width: '100%'});

        const filteredClienti = clienti.filter(val => val.sedi.includes(id_sede));
        console.log(clienti, filteredClienti);
        $("#cliente_id_modal_add").select2('destroy').empty();
        $('#cliente_id_modal_add').append('<option value="">- Scegli Azienda -</option>');    
        filteredClienti.forEach(element => {
            $('#cliente_id_modal_add').append('<option value="'+element.id+'">'+element.rag_soc+'</option>');    
        });
        $('#cliente_id_modal_add').select2();


        const filteredContacts = contacts.filter(val => val.sedi.includes(id_sede));
        console.log(contacts, filteredContacts);
        $("#partecipante_id_modal_add").select2('destroy').empty();
        $('#partecipante_id_modal_add').append('<option value="">- Scegli Privato -</option>');    
        filteredContacts.forEach(element => {
            $('#partecipante_id_modal_add').append('<option value="'+element.id+'">'+element.cognome+' ' + element.nome +'</option>');    
        });
        $('#partecipante_id_modal_add').select2();


    }

    
    function clearFields(){
        $('#collettivo_id_modal_add').val('').trigger('change')
        $('#cliente_id_modal_add').val('').trigger('change')
        $('#partecipante_id_modal_add').val('').trigger('change')
        $('#label_id_modal_add').val('')
        $('#alloggio_modal_add').val('')
        $('#sciclub_modal_add').prop('checked',false)
        $('.specialita_modal_add').prop('checked',false)
        $('#ritrovo_modal_add').val('')
        $('#note_lez_modal_add').val('')
        $('#note_lez_modal_add').text('')
        $('#pax_modal_add').val('1')
        $('select[name="branch_id_modal_add"]').val('')

        $('#ora_in_modal_add').val('')
        $('#ora_out_modal_add').val('')
        $('#ritrovo_modal_add').val('')
        $('#disciplina_modal_add').val('')
        $('#frequenza').hide();

    }


    function clearFieldsUpdate(){
        $('#maestro_v_modal_upd').val('')
        $('#alloggio_modal_upd').val('')
        $('#pax_modal_upd').val('1')
        $('#sciclub_modal_upd').prop('checked',false)
        $('#data_in_modal_upd').val('')
        $('#ora_in_modal_upd').val('')
        $('#ora_out_modal_upd').val('')
        $('#ritrovo_modal_upd').val('')
        $('#livello_modal_upd').val('')
        $('#venditore_modal_upd').val('')
        $('.specialita_modal_upd').prop('checked',false)
        $('#note_lez_modal_upd').val('')
        $('#note_lez_modal_upd').text('')
        $('#tr_modal_upd').html('')

    }
    
        //$('select[name="branch_id_modal_add"]').select2({width: '100%'});
        $('#maestro_id_modal_addCollettivo').select2({width: '100%'});
        $('#collettivo_id_modal_add').select2({width: '100%'});

        $('select[name="prodotto_doc"]').select2({width: '100%'});

        $('#cliente_id_modal_add').select2({width: '40%'});
        $('#partecipante_id_modal_add').select2({width: '40%'});

        $('.pax_modal_add_enabled').hide();

        $('select#venditore_modal_add').on('change', function(){
            $('#divmaestro').hide()
            if($(this).val() == 'M')
                $('#divmaestro').show()
        });

        $('select#company_id').on('change', function(){
            console.log($(this).val())
            reloadDropDown();
        });

        
        $('select#partecipante_id_modal_add').on('change', function(){
            console.log($(this).val())
            if($(this).val() != ''){
                $('.pax_modal_add_enabled').show();
                $('#tr_livello').show()
            }
            else{
                $('#tr_livello').hide()
                $('.pax_modal_add_enabled').hide();
            }
                
        });
        
        $('select#collettivo_id_modal_add').on('change', function(){
            console.log($(this).val())
            if($(this).val() != ''){
                $('select#cliente_id_modal_add').prop('disabled',true);
                $('select#partecipante_id_modal_add').prop('disabled',true);
                $('select#label_id_modal_add').prop('disabled',true);
            }
            else{
                $('select#cliente_id_modal_add').prop('disabled',false);
                $('select#partecipante_id_modal_add').prop('disabled',false);
                $('select#label_id_modal_add').prop('disabled',false);
            }                
        });
        
        $('select#cliente_id_modal_add').on('change', function(){
            console.log($(this).val())
            if($(this).val() != ''){
                $('select#collettivo_id_modal_add').prop('disabled',true);
                $('select#label_id_modal_add').prop('disabled',true);
            }
            else{
                $('select#collettivo_id_modal_add').prop('disabled',false);
                $('select#label_id_modal_add').prop('disabled',false);
            }                
        });
        
        $('select#partecipante_id_modal_add').on('change', function(){
            console.log($(this).val())
            if($(this).val() != ''){
                $('select#collettivo_id_modal_add').prop('disabled',true);
                $('select#label_id_modal_add').prop('disabled',true);
            }
            else{
                $('select#collettivo_id_modal_add').prop('disabled',false);
                $('select#label_id_modal_add').prop('disabled',false);
            }                
        });
        
        $('select#label_id_modal_add').on('change', function(){
            console.log($(this).val())
            if($(this).val() != ''){
                $('select#cliente_id_modal_add').prop('disabled',true);
                $('select#partecipante_id_modal_add').prop('disabled',true);
                $('select#collettivo_id_modal_add').prop('disabled',true);
            }
            else{
                $('select#cliente_id_modal_add').prop('disabled',false);
                $('select#partecipante_id_modal_add').prop('disabled',false);
                $('select#collettivo_id_modal_add').prop('disabled',false);
            }                
        });

        $('select#contact_id').on('change', function(){
            console.log($(this).val())
            reloadDataStudent();
        });



        function openAddOra(id_maestro, id_sede)
        {
            console.log('openAddOra: ', id_sede)
            clearFields();
            $('select[name="branch_id_modal_add"]').val(id_sede)
            $('select[name="branch_id_modal_add"]').prop('disabled',true)
            console.log('giorno_output: ', giorno_output)
            $('#data_in_modal_add').val(giorno_output)
            $('#data_out_modal_add').val(giorno_output)
            $('#maestro_id_modal_add').val(id_maestro)
           
            preimpostaCollection(id_sede);
            $('#tr_livello').hide()
            $('#modalAggiungi').modal('toggle');
        };


        function openAddOraPreimpostata(ora,min,id_maestro, id_sede){

            console.log('openAddOraPreimpostata: ', id_sede)
            clearFields();
            $('select[name="branch_id_modal_add"]').val(id_sede)
            $('select[name="branch_id_modal_add"]').prop('disabled',true)
            let oraFine = (ora + 1) < 10 ? '0'+(ora + 1) : (ora + 1);
            ora = ora < 10 ? '0'+ora : ora;   
            min = min == 30 ? ':30' : ':00';
            let time = ora + min;
            let timeFine = oraFine + min;
            console.log('time:',time)
            console.log('timeFine:',timeFine)
            $('#ora_in_modal_add').val(time)
            $('#ora_out_modal_add').val(timeFine)
            $('#data_in_modal_add').val(giorno_output)
            $('#data_out_modal_add').val(giorno_output)
            $('#maestro_id_modal_add').val(id_maestro)

            preimpostaCollection(id_sede);
            $('#tr_livello').hide()
            $('#modalAggiungi').modal('toggle');
        }


        //modale modifica

        $('select[name="branch_id_modal_upd[]"]').select2({width: '100%'});

        $("#closemodalModifica").click(function() {
            $('#modalModifica').modal('toggle');
        });

        $("#closemodalEliminazioneOra").click(function() {
            $('#modalEliminazioneOra').modal('toggle');
        });

        $("#closemodalAddDoc").click(function() {
            $('#modalAddDoc').modal('toggle');
        });

        
        function gotoCreate(){
            window.location.href = "/planning/create-company";
        }
        
        

        function openModOra(id_ora){
        
            clearFieldsUpdate();
            jQuery.ajax('/planning/get-ora',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "id_ora": id_ora

                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    console.log('id_cliente: ' + result.data.id_cliente)
                    var data = JSON.stringify(result.data)
                    console.log('data: ' + data)
                    if(result.code){
                        $('.pax_modal_upd_enabled').hide();
                        $('#data_in_modal_upd').val(giorno_output)
                        $('#data_out_modal_upd').val(giorno_output)
                        $('#maestro_v_modal_upd').val(result.data.nome_venditore)
                        $('#maestro_modal_upd').val(result.data.id_maestro)
                        
                        if(result.data.venditore == 'M'){
                			$('#divmaestro_upd').show();
                		} else {
                			$('#divmaestro_upd').hide();
                		}
                		
                        var arr = result.data.id_cliente.split('_')
                        $('#tr_livello_upd').hide()
                        //partecipante
                        if(arr[0] == 'T'){
                            $('#tr_modal_upd').append(`<td width="20%" class="testo" align="right" valign="top">
                                                                        <b>Partecipante</b>
                                                                    </td>
                                                                    <td width="80%" class="testo" align="left" valign="top">`
                                                                        + result.data.item_label+
                                                                        `</td>
                                                                    <td>
                                                                    </td>`);

                            $('.pax_modal_upd_enabled').show();
                            $('#tr_livello_upd').show()
                        }
                        //label
                        if(arr[0] == 'L'){
                            $('#tr_modal_upd').append(`<td width="20%" class="testo" align="right" valign="top">
                                                                        <b>Segnaposto</b>
                                                                    </td>
                                                                    <td width="80%" class="testo" align="left" valign="top">`
                                                                        +result.data.item_label+
                                                                        `</td>
                                                                    <td>
                                                                    </td>`);
                        }
                        if(arr[0] == 'Y'){
                            $('#tr_modal_upd').append(`<td width="20%" class="testo" align="right" valign="top">
                                                                        <b>Cliente</b>
                                                                    </td>
                                                                    <td width="80%" class="testo" align="left" valign="top">`
                                                                        + result.data.item_label+
                                                                        `</td>
                                                                    <td>
                                                                    </td>`);
                        }

                        if(arr[0] == 'C'){
                            $('#tr_modal_upd').append(`<td width="20%" class="testo" align="right" valign="top">
                                                                        <b>Collettivo</b>
                                                                    </td>
                                                                    <td width="80%" class="testo" align="left" valign="top">`
                                                                        +result.data.item_label+
                                                                        `</td>
                                                                    <td>
                                                                    </td>`);
                        }



                        $('#alloggio_modal_upd').val(result.data.id_alloggio)
                        $('#pax_modal_upd').val(result.data.pax)
                        $('#ora_in_modal_upd').val(result.data.ora_in.substring(0, 5))
                        $('#ora_out_modal_upd').val(result.data.ora_out.substring(0, 5))
                        $('#ritrovo_modal_upd').val(result.data.ritrovo_id)
                        $('#disciplina_modal_upd').val(result.data.disciplina)
                        $('#livello_modal_upd').val(result.data.livello)
                        $('#venditore_modal_upd').val(result.data.venditore)
                        $('select[name="branch_id_modal_upd[]"]').val(result.data.id_cc).trigger('change')
                        $('select[name="branch_id_modal_upd[]"]').prop('disabled',true)
                        $('#note_lez_modal_upd').val(result.data.note)
                        $('#record_id_modal_upd').val(result.data.id)
                        $('#invoice_id_modal_upd').val(result.data.invoice_id);

                        var arr = result.data.specialita != null ? result.data.specialita.split(',') : [];
                        var specs = [];
                        for (var i = 0; i < arr.length; i++){
                            specs.push(parseInt(arr[i]));
                        }

                        console.log(specs.includes(10));
                        if(specs.includes(10))
                            $('#10_upd').prop('checked',true)
                        if(specs.includes(11))
                            $('#11_upd').prop('checked',true)
                        if(specs.includes(9))
                            $('#9_upd').prop('checked',true)
                        if(specs.includes(8))
                            $('#8_upd').prop('checked',true)
                        if(specs.includes(13))
                            $('#13_upd').prop('checked',true)
                        if(specs.includes(14))
                            $('#14_upd').prop('checked',true)
                        if(specs.includes(2))
                            $('#2_upd').prop('checked',true)
                        if(specs.includes(1))
                            $('#1_upd').prop('checked',true)
                        if(specs.includes(3))
                            $('#3_upd').prop('checked',true)
                        if(specs.includes(15))
                            $('#15_upd').prop('checked',true)
                        if(specs.includes(16))
                            $('#16_upd').prop('checked',true)
                        if(specs.includes(17))
                            $('#17_upd').prop('checked',true)


                        $('#btnupdate').show()
                        if(result.data.saldato == 1){
                            $('#btnFattura').hide();
                            $('#btnopenModalDoc').hide()
						}
                        $('#modalModifica').modal('toggle');
                    }   
                }
            });
        }

    
        $( "#btnopenModalDoc" ).click(function() {
            //$('#modalModifica').modal('toggle');
            $('#modalAddDoc').modal('toggle');
            var branch_id = $('select[name="branch_id_modal_upd[]"]').val();
            console.log('btnopenModalDoc: ', $('select[name="branch_id_modal_upd[]"]').val())
            $('#ccID').val($('select[name="branch_id_modal_upd[]"]').val())
            console.log(branches[branch_id])
            $('#lbl_cc').text(branches[branch_id])
        })


        $( "#btnAddDoc" ).click(function() {
            var ora_id = $('#record_id_modal_upd').val()
            var invoice_id = $('#invoice_id_modal_upd').val()
            jQuery.ajax('/planning/add-document-ora',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "tipo_doc": $('#tipo_doc').val(),
                    "n_doc": $('#n_doc').val(),
                    "data_doc": $('#data_doc').val(),
                    "prodotto_doc":$('select[name="prodotto_doc"]').val(),
                    "ora_id":ora_id
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        if(result.data != null)
                            window.location.href = "/invoices/" + invoice_id + '/edit';
                        else
                            $('.alert-info-add-doc').show();
                    }     
                }
            });
        })


        $( "#btnFattura" ).click(function() {
            var invoice_id = $('#invoice_id_modal_upd').val()
            jQuery.ajax('/planning/update-fattura-ora',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "invoice_id": invoice_id
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        window.location.href = "/invoices/" + invoice_id + '/edit';
                    }     
                }
            });
        })


        
        $( "#btnupdate" ).click(function() {

            if($('#ora_in_modal_upd').val() == '' || $('#ora_out_modal_upd').val() == ''){
                alert('Inserisci sia l\'ora iniziale che quella finale!')
                return;
            }

            if($('#ora_in_modal_upd').val() > $('#ora_out_modal_upd').val() ){
                alert('L\'ora finale non può essere maggiore di quella iniziale!')
                return;
            }

            if($('#disciplina_modal_upd').val() == ''){
                alert('Per proseguire devi selezionare la disciplina!')
                return;
            }


            if($('#maestro_v_modal_upd').val() == ''){
                alert('Per proseguire devi selezionare il Maestro!')
                return;
            }


            var invoice_id = $('#invoice_id_modal_upd').val()
            var ora_id = $('#record_id_modal_upd').val()
            var lista_spec = "";
            $(".specialita_modal_upd").each(function() {
                if ($(this).is(':checked')) {
                    var id_ele = $(this).attr("id").split("_")[0];
                    lista_spec = lista_spec != '' ? lista_spec + ',' + id_ele : id_ele; 
                }
                    
            });

            jQuery.ajax('/planning/update-ora',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "ora_id":ora_id,
                    "invoice_id": invoice_id,
                    "maestro": $('#maestro_modal_upd').val(),
                    "alloggio": $('#alloggio_modal_upd').val(),
                    "pax": $('#pax_modal_upd').val(),
                    "sciclub": $('#sciclub_modal_upd').is(":checked") ? 1 : 0,
                    "data_in": $('#data_in_modal_upd').val(),
                    "data_out": $('#data_out_modal_upd').val(),
                    "ora_in": $('#ora_in_modal_upd').val(),
                    "ora_out": $('#ora_out_modal_upd').val(),
                    "lista_spec" : lista_spec,
                    "ritrovo": $('#ritrovo_modal_upd').val(),
                    "disciplina": $('#disciplina_modal_upd').val(),
                    "livello": $('#livello_modal_upd').val(),
                    "venditore": $('#venditore_modal_upd').val(),
                    "branches": $('select[name="branch_id_modal_upd[]"]').val(),
                    "note": $('#note_lez_modal_upd').val()
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        $('#modalModifica').modal('toggle');
                        setInterval(function() {
                                location.reload()
                        }, 1000);
                    } 
                    else{
                        alert(result.message);
                    }
                }
            });
        })

        $( "#btndelete" ).click(function() {
            var ora_id = $('#record_id_modal_upd').val()
            var invoice_id = $('#invoice_id_modal_upd').val()
            $('#modalEliminazioneOra').modal('toggle');
            $('#oraIDRemove').val(ora_id);
            $('#invoiceIDRemove').val(invoice_id);
        })

        $("#btnRemoveOra").click(function() {
                jQuery.ajax('/planning/delete-ora', {
                    method: 'POST',
                    data: {
                        "_token": '{{ csrf_token() }}',
                        "id_ora": $('#oraIDRemove').val(),
                        "id_invoice": $('#invoiceIDRemove').val()

                    },

                    complete: function(resp) {
                        var result = JSON.parse(resp.responseText);

                        console.log(JSON.stringify(result));

                        if (result.code) {
                            $('#modalEliminazioneOra').modal('toggle');

                            setInterval(function() {
                                location.reload()
                            }, 1000);
                        } else {

                            $('#modalEliminazioneOra').modal('toggle');
                        }

                    }
                })
            });

        //fine modale modifica

        $("#closemodalAggiungi").click(function() {
            $('#modalAggiungi').modal('toggle');
        });



        $( "#btnsaveCliente" ).click(function() {
            if($('#maestro_id_modal_addCliente').val() == ''){
                alert('Per proseguire devi selezionare un Maestro!')
                return;
            }

            if($('#oraIDByCliente').val() == ''){
                alert('Operazione non possibile.Gestire le singolarmente dal planning!')
                return;
            }

            jQuery.ajax('/planning/insert-ora-cliente',
            {
                method: 'POST',
                data: {
                "_token": '{{ csrf_token() }}',
                "ora_id": $('#oraIDByCliente').val(),
                "richiesto": $('#richiesto_modal_add').is(':checked') ? 'S' : 'N',
                "maestri": $('#maestro_id_modal_addCliente').val(),
                "aperta" : 1
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        $('#modalAddByCliente').modal('toggle');
                        setInterval(function() {
                                location.reload()
                        }, 1000);
                    }
                    else{
                        $('#modalAddByCliente').modal('toggle');
                    }         
                }
            });

        })


        $( "#btnclosedCliente" ).click(function() {
            if($('#maestro_id_modal_addCliente').val() == ''){
                alert('Per proseguire devi selezionare un Maestro!')
                return;
            }
            jQuery.ajax('/planning/insert-ora-cliente',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "ora_id": $('#oraIDByCliente').val(),
                    "richiesto": $('#richiesto_modal_add').is(':checked') ? 'S' : 'N',
                    "maestri": $('#maestro_id_modal_addCliente').val(),
                    "aperta" : 0

                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        $('#modalAddByCliente').modal('toggle');
                        setInterval(function() {
                            window.location.href = "/invoices/" + result.data + '/edit';
                        }, 1000);
                    }
                    else{
                        $('#modalAddByCliente').modal('toggle');
                    }         
                }
            });

        })




        $( "#btnsaveCollettivo" ).click(function() {
            jQuery.ajax('/planning/insert-ora-collettivo',
            {
                method: 'POST',
                data: {
                "_token": '{{ csrf_token() }}',
                "livello": $('#livelloCollettivo').val(),
                "eta": $('#eta_modal_add').val(),
                "colletivo_allievi_id": $('#oraIDByCollettivo').val(),
                "aperta" : 1
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        $('#modalAddByCollettivo').modal('toggle');
                        setInterval(function() {
                                location.reload()
                        }, 1000);
                    }
                    else{
                        $('#modalAddByCollettivo').modal('toggle');
                    }         
                }
            });

        })


        $( "#btnclosedCollettivo" ).click(function() {
            jQuery.ajax('/planning/insert-ora-collettivo',
            {
                method: 'POST',
                data: {
                    "_token": '{{ csrf_token() }}',
                    "livello": $('#livelloCollettivo').val(),
                    "eta": $('#eta_modal_add').val(),
                    "colletivo_allievi_id": $('#oraIDByCollettivo').val(),
                    "aperta" : 0

                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        $('#modalAddByCollettivo').modal('toggle');
                        window.location.href = "/invoices/" + result.data + '/edit';
                    }
                    else{
                        $('#modalAddByCollettivo').modal('toggle');
                    }         
                }
            });

        })


        $( "#btnsave" ).click(function() {
            var lista_spec = "";
            $(".specialita_modal_add").each(function() {
                if ($(this).is(':checked')) 
                    lista_spec = lista_spec != '' ? lista_spec + ',' + $(this).attr("id") : $(this).attr("id"); 
            });
            $('.alert').hide();
            console.log('lista_spec: ', lista_spec)

            if($('#ora_in_modal_add').val() == '' || $('#ora_out_modal_add').val() == ''){
                alert('Inserisci sia l\'ora iniziale che quella finale!')
                return;
            }

            if($('#ora_in_modal_add').val() > $('#ora_out_modal_add').val() ){
                alert('L\'ora finale non può essere maggiore di quella iniziale!')
                return;
            }

            if($('#disciplina_modal_add').val() == ''){
                alert('Per proseguire devi selezionare la disciplina!')
                return;
            }


            if($('#collettivo_id_modal_add').val() == '' && $('#cliente_id_modal_add').val() == '' && $('#partecipante_id_modal_add').val() == '' && $('#label_id_modal_add').val() == ''){
                alert('Per proseguire devi selezionare uno tra collettivo,azienda,contatto e segnaposto!')
                return;
            }


            if($('#collettivo_id_modal_add').val() != '' && $('#partecipante_id_modal_add').val() == ''){
                alert('Per proseguire devi selezionare un partecipante!')
                return;
            }


            jQuery.ajax('/planning/insert-ora',
            {
                method: 'POST',
                data: {
                "_token": '{{ csrf_token() }}',
                "colletivo": $('#collettivo_id_modal_add').val(),
                "cliente": $('#cliente_id_modal_add').val(),
                "partecipante": $('#partecipante_id_modal_add').val(),
                "alloggio": $('#alloggio_modal_add').val(),
                "pax": $('#pax_modal_add').val(),
                "sciclub": $('#sciclub_modal_add').is(":checked") ? 1 : 0,
                "data_in": $('#data_in_modal_add').val(),
                "data_out": $('#data_out_modal_add').val(),
                "ora_in": $('#ora_in_modal_add').val(),
                "ora_out": $('#ora_out_modal_add').val(),
                "lista_spec" : lista_spec,
                "ritrovo": $('#ritrovo_modal_add').val(),
                "disciplina": $('#disciplina_modal_add').val(),
                "livello": $('#livello_modal_add').val(),
                "venditore": $('#venditore_modal_add').val(),
                "branches": $('select[name="branch_id_modal_add"]').val(),
                "note": $('#note_lez_modal_add').val(),
                "maestro": $('#maestro_id_modal_add').val(),
                "maestro_v": $('#maestro_v_modal_add').val(),
                "label": $('#label_id_modal_add').val(),
                "freq_C": $('#freq_C').is(":checked") ? 1 : 0,
                "freq_0": $('#freq_0').is(":checked") ? 1 : 0,
                "freq_1": $('#freq_1').is(":checked") ? 1 : 0,
                "freq_2": $('#freq_2').is(":checked") ? 1 : 0,
                "freq_3": $('#freq_3').is(":checked") ? 1 : 0,
                "freq_4": $('#freq_4').is(":checked") ? 1 : 0,
                "freq_5": $('#freq_5').is(":checked") ? 1 : 0,
                "freq_6": $('#freq_6').is(":checked") ? 1 : 0,
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        if($('#collettivo_id_modal_add').val() != ''){
                            $('#oraIDByCollettivo').val(result.data.collettivo_allievi_id)
                            $('#modalAggiungi').modal('toggle');
                            $('#modalAddByCollettivo').modal({backdrop: 'static', keyboard: false});
                        }
                        else{
                            $('#oraIDByCliente').val(result.data.ora_id)
                            $('#modalAggiungi').modal('toggle');
                            if(result.data.maestri.length > 0){
                                $('#btnsaveCliente').show()
                                $('#btnclosedCliente').show()
                                result.data.maestri.forEach(element => {
                                    console.log('element: ', element)
                                    var selected = '';
                                    if($('#maestro_id_modal_add').val() != '' && $('#maestro_id_modal_add').val() != 'undefined'){
                                    	selected = 'selected';
                                    } else {
                                    	selected = '';
                                    }
                                    $('#maestro_id_modal_addCliente').append('<option value="' + element.id +'" ' + selected + '>' + element.nome + ' ' + element.cognome + '</option>');
                                });
                            }
                            else{
                                $('#btnsaveCliente').hide()
                                $('#btnclosedCliente').hide()
                                alert('Non ci sono Maestri disponibili!')
                            }
                            
                            
                            $('#modalAddByCliente').modal({backdrop: 'static', keyboard: false});
                        }
                        
                    }
                    else{
                        $('.alert strong').text(result.message);
                        $('.alert').show();
                        setInterval(function() {
                            $('.alert').hide();
                        }, 2000);

                    }         
                }
            });

        })


        //step 2

        $("#closemodalAggiungiCollettivo").click(function() {
            $('#modalAddByCollettivo').modal('toggle');
        });

        $("#closemodalAggiungiCliente").click(function() {
            $('#modalAddByCliente').modal('toggle');
        });
        



        /*$('select#cliente_id_modal_add').on('change', function(){
            console.log($(this).val())
            if($(this).val() != '')
                reloadDropDown();
        });*/


        function reloadDropDown(){
            $('#partecipante_id_modal_add').prop('disabled',false)
            jQuery.ajax('/api/list-students',
            {
                method: 'POST',
                data: {
                "_token": "{{csrf_token()}}",
                "company_id": $('select#cliente_id_modal_add').val()
                },
                complete: function (resp) {
                    $("#partecipante_id_modal_add").empty();
                    if(resp !== null)
                    {
                        var result = JSON.parse(resp.responseText);
                        console.log(result)
                        if(result.length > 0){
                            $('#partecipante_id_modal_add').append( '<option value="">Partecipante</option>')
                            result.forEach(element => {
                                console.log(element)
                                $('#partecipante_id_modal_add').append( '<option value="'+element.id+'">'+element.nome + ' ' + element.cognome + '</option>' );
                            });
                        }
                        else
                            $('#partecipante_id_modal_add').prop('disabled',true)
                        
                    }
                }
            });
        }

    </script>
@stop
