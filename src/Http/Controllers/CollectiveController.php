<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{City, Contact, Sede, Master, Specialization, Collettivo, Ora, CollettivoAllievi, Company, CollettivoAcconti, Invoice, Item};

class CollectiveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request()->has('year'))
        {
            $year = intval(request()->year);
            $from = date(strval($year -1 ).'-07-01');
            $to = date(strval(($year)).'-06-31');
            //dd($from, $to);
            $collectives = Collettivo::query()->whereBetween('data_in', [$from, $to])->orderBy('data_in', 'DESC')->paginate(30);
        }
        else
        {
        	if(date('m') <= 6){
        		$from = date(strval(date('Y')-1).'-07-01');
            	$to = date(strval(date('Y')).'-06-31');
        	} else {
        		$from = date(strval(date('Y')).'-07-01');
            	$to = date(strval(date('Y')+1).'-06-31');
        	}
        	
        	
            $collectives = Collettivo::query()->whereBetween('data_in', [$from, $to])->orderBy('data_in', 'DESC')->paginate(30);
        }

        foreach ($collectives as $value) {
            
            if($value->disciplina != null || $value->disciplina == 0){
                //dd($value->disciplina);
                switch ($value->disciplina) {
                    case 1:
                        $value->disciplina = 'Discesa';
                      break;
                    case 2:
                    case 3:
                        $value->disciplina = 'Fondo';
                      break;
                    case 4:
                        $value->disciplina = 'Snowboard';
                  }
            }
            if($value->specialita != null){
                $specialita =  Specialization::whereIn('id',[$value->specialita])->get();
                $value->specialita == '';
                foreach ($specialita as $rec) {
                    $value->specialita=  $value->specialita == '' ? $rec->nome : $value->specialita.','.$rec->nome;
                }
            }
            if($value->centro_costo != null){
                $value->centro_costo = Sede::where('id',$value->centro_costo)->first()->nome;
            }

            if($value->frequenza != null){
                $frequenza = "";
                if($value->frequenza == 'C')
                    $frequenza = 'Continuativo';
                
                if (strpos($value->frequenza, '1') !== false) { 
                    $frequenza = $frequenza == '' ? 'Lunedì' : $frequenza.'<br> Lunedì';
                }
                if (str_contains($value->frequenza, 2)) { 
                    $frequenza =$frequenza == '' ? 'Martedì' : $frequenza.'<br> Martedì';
                }
                if (str_contains($value->frequenza, 3)) { 
                    $frequenza = $frequenza == '' ? 'Mercoledì' : $frequenza.'<br> Mercoledì';
                }
                if (str_contains($value->frequenza, 4)) { 
                    $frequenza = $frequenza == '' ? 'Giovedì' : $frequenza.'<br> Giovedì';
                }
                if (str_contains($value->frequenza, 5)) { 
                    $frequenza = $frequenza == '' ? 'Venerdì' : $frequenza.'<br> Venerdì';
                }
                if (str_contains($value->frequenza, 6)) { 
                    $frequenza = $frequenza == '' ? 'Sabato' : $frequenza.'<br> Sabato';
                }
                if (str_contains($value->frequenza, 0)) { 
                    $frequenza = $frequenza == '' ? 'Domenica' : $frequenza.'<br> Domenica';
                }

                $value->frequenza = $frequenza;
            }

        }

        return view('areaseb::core.collective.index', compact('collectives'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $branches = [''=>'']+Sede::pluck('nome', 'id')->toArray();
        $specializzazioni = [''=>'']+Specialization::pluck('nome', 'id')->toArray();

        return view('areaseb::core.collective.create', compact('specializzazioni', 'branches'));
  
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate(request(), [
            'nome' => 'required',
            'data_in' => 'required',
            'data_out' => 'required',
            'disciplina' => 'required',
            'branch_id' => 'required',
            'specializzazioni' => 'required'
        ]);


        $nome = $request->nome;
        $data_in = explode("T",$request->data_in)[0];
        $data_out = explode("T",$request->data_out)[0];
        $ora_in =explode("T",$request->data_in)[1];
        $ora_out = explode("T",$request->data_out)[1];
        $disciplina =$request->disciplina;
        $centro_costo = $request->branch_id;
        $giorni = $request->giorni;
        if(isset($request->specializzazioni)){
        	$specializzazioni = implode(",",$request->specializzazioni);
        } else {
        	$specializzazioni = null;
        }
        

        if($nome != "" && $data_in != "" && $data_out != "" && $ora_in != "" && $ora_out != "" && $centro_costo != "" && $disciplina != ""){
    									  	
	        $frequenza = "";
	            
	        if(isset($request->is_continuous)){
	            $frequenza = "C";
	        } else{
	            $frequenza =implode(",",$giorni);
	        }
	        //inserisco il collettivo
	        $collettivo = new Collettivo();
	        $collettivo->nome = $nome;
	        $collettivo->data_in = $data_in;
	        $collettivo->data_out = $data_out;
	        $collettivo->frequenza =  $frequenza;
	        $collettivo->ora_in = $ora_in;
	        $collettivo->ora_out = $ora_out;
	        $collettivo->centro_costo = $centro_costo;
	        $collettivo->disciplina = $disciplina;
	        $collettivo->specialita = $specializzazioni;
	        $collettivo->save();
	                

	        $strHtml= "";
	          
	        list($a_in, $m_in, $g_in) = explode("-", $data_in);
	        list($a_out, $m_out, $g_out) = explode("-", $data_out);

	        //$date_diff = mktime(0, 0, 0, $m_out, $g_out, $a_out) - mktime(0, 0, 0, $m_in, $g_in, $a_in);
	        //$date_diff  = floor(($date_diff / 60 / 60 / 24) / 1);
	        $date_diff = date_diff(date_create($data_in), date_create($data_out))->format('%a');

	        //dd($g_in, $m_in, $a_in.'-'.$g_out, $m_out, $a_out.' '.$date_diff);
	        
	        if($frequenza == "C"){
	            for($i = 0; $i <= $date_diff; $i++){
	                  $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	              }
	        } else {
	            //domenica
	            if(str_contains($frequenza, '0')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 0){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	            
	            //lunedi'
	            if(str_contains($frequenza, '1')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 1){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	            
	            //martedi'
	            if(str_contains($frequenza, '2')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 2){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	            
	            //mercoledi'
	            if(str_contains($frequenza, '3')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 3){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	            
	            //giovedi'
	            if(str_contains($frequenza, '4')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 4){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	            
	            //venerdi'
	            if(str_contains($frequenza, '5')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 5){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	            
	            //sabato'
	            if(str_contains($frequenza, '6')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 6){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	        }

	        //ordino l'array di date
	        $date = array_map('strtotime', $elenco_date);
	        sort($date);
	        foreach($date as $k => $d){
	            $elenco_date[$k] = date('Y-m-d', $d);
	        }
    
			for($i = 0; $i <= count($elenco_date)-1; $i++){
			  
			  list($a, $m, $g) = explode("-", $elenco_date[$i]);
			  $data = "$g/$m/$a";
			  
			  $strHtml= $strHtml."<div style=\"width: 90%; text-align: left;\"><b>$data</b></div><br>
			              <table width=\"90%\" align=\"center\" cellpadding=\"3\" cellspacing=\"0\">
			                  <tr>";
			  
/*			  if(auth()->user()->hasRole('super')){
			        $query_costo = "";
			    } else {
			        $query_costo = "and cb.branch_id =".$request->branch_id;
			    }*/

			    //$query_costo prima del primo in(
			    $query = "select m.id, c.nome, c.cognome from contacts as c 
			                inner join contact_branch as cb on cb.contact_id = c.id
			                inner join masters as m on m.contact_id = c.id
			                where c.attivo = 1 and m.disciplina = $disciplina 
			                and c.id 
			                in
			                    (
			                    select a.contact_id as id_maestro 
			                    	from availabilities as a 
			                    	where a.data_start <= \"$elenco_date[$i]\" and \"$elenco_date[$i]\" <= a.data_end 
			                    	and branch_id = $request->branch_id
			                    ) 
			                and m.id not in
			                    (
			                    select id_maestro 
			                    	from ora 
			                    	where data = \"$elenco_date[$i]\" and ora_in >= \"$ora_in\" and ora_out <= \"$ora_out\" 
			                    	or data = \"$elenco_date[$i]\" and ora_in <= \"$ora_in\" and ora_out >= \"$ora_out\"
			                    	or data = \"$elenco_date[$i]\" and ora_in < \"$ora_in\" and ora_out > \"$ora_in\"
			                    	or data = \"$elenco_date[$i]\" and ora_in < \"$ora_out\" and ora_out > \"$ora_out\"
			                    ) 
			                order by c.cognome;";
			    //dd($query);                                                                    
			    $arr = \DB::select($query);     
			    
			         
			     //recupero i dati
			     $k = 1;
			     foreach ($arr as $value) {
			     	$id = 
			        $strHtml=  $strHtml."<td align=\"left\">
			                      <input type=\"checkbox\" name=\"$elenco_date[$i]-$value->id\" value=\"S\"> $value->cognome $value->nome
			                  </td>";
			      if(($k % 5) == 0){
			        $strHtml= $strHtml."</tr><tr>";
			      }		
			      
			      $k++;	
			     }

			    $strHtml= $strHtml."	</tr>
			                </table>
			                <br><br>";
			}
          
            $strHtml= $strHtml."<input class=\"btn btn-primary\" type=\"submit\" value=\"Concludi\">
                      <input type=\"hidden\" name=\"corso\" value=\"$collettivo->id\">
                      <br><br>";
                  
            return view('areaseb::core.collective.select_master', compact('collettivo','strHtml'));
                                
        }


    }


    public function storeStep2(Request $request)
    {

        $this->validate(request(), [
            'corso' => 'required'
        ]);

        $collettivo = Collettivo::find($request->corso);

        list($a_in, $m_in, $g_in) = explode("-", $collettivo->data_in);		    			
		list($a_out, $m_out, $g_out) = explode("-", $collettivo->data_out);
        //$date_diff = mktime(0, 0, 0, $m_out, $g_out, $a_out) - mktime(0, 0, 0, $m_in, $g_in, $a_in);
        //$date_diff  = floor(($date_diff / 60 / 60 / 24) / 1);
        $date_diff = date_diff(date_create($collettivo->data_in), date_create($collettivo->data_out))->format('%a');


        $frequenza = $collettivo->frequenza;
        if($frequenza == "C"){
            for($i = 0; $i <= $date_diff; $i++){
                  $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
              }
        }
        else {
        			
            if(substr($frequenza, -1) == ','){
            	$frequenza = substr($frequenza, 0, -1);
            }
            $elenco_giorni = explode(",", $frequenza);

            //domenica
            if(in_array("0", $elenco_giorni)){
                for($i = 0; $i <= $date_diff; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 0){
                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
                    }
                }
            }
            
            //lunedi'
            if(in_array("1", $elenco_giorni)){
                for($i = 0; $i <= $date_diff; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 1){
                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
                    }
                }
            }
            
            //martedi'
            if(in_array("2", $elenco_giorni)){
                for($i = 0; $i <= $date_diff; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 2){
                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
                    }
                }
            }
            
            //mercoledi'
            if(in_array("3", $elenco_giorni)){
                for($i = 0; $i <= $date_diff; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 3){
                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
                    }
                }
            }
            
            //giovedi'
            if(in_array("4", $elenco_giorni)){
                for($i = 0; $i <= $date_diff; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 4){
                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
                    }
                }
            }
            
            //venerdi'
            if(in_array("5", $elenco_giorni)){
                for($i = 0; $i <= $date_diff; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 5){
                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
                    }
                }
            }
            
            //sabato'
            if(in_array("6", $elenco_giorni)){
                for($i = 0; $i <= $date_diff; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 6){
                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
                    }
                }
            }
        }
      
        $date = array_map('strtotime', $elenco_date);
        sort($date);
        foreach($date as $k => $d){
            $elenco_date[$k] = date('Y-m-d', $d);
        }

        for($i = 0; $i <= count($elenco_date)-1; $i++){
					  						  	
            if(auth()->user()->hasRole('super')){
                  $query_costo = "";
            } else {
                //$query_costo = "and c.id in(select contact_id from contact_branch where branch_id = ".str_replace(',', '', $collettivo->centro_costo).") ";
                $query_costo = "and branch_id = ".str_replace(',', '', $collettivo->centro_costo);
            }
			
            $query = "select m.id, c.nome, c.cognome from contacts as c 
                            inner join contact_branch as cb on cb.contact_id = c.id
                            inner join masters as m on m.contact_id = c.id
                            where c.attivo = 1 and m.disciplina = $collettivo->disciplina 
                            
                            and c.id in(select a.contact_id as id_maestro from availabilities as a where a.data_start <= \"$elenco_date[$i]\" and \"$elenco_date[$i]\" <= a.data_end $query_costo) order by c.cognome;";
              
            $arr = \DB::select($query);                                      

			$maestri_tutti = array();
			foreach ($arr as $value) {
				$maestri_tutti[] = $value->id;
			}
			
			$maestri_confermati = array();
					
            foreach ($arr as  $value) {
                if($request["$elenco_date[$i]-$value->id"] == "S"){	
                	
                	$maestri_confermati[] = $value->id;
                	
                	$ora_check = Ora::where('id_maestro', $value->id)->where('data', $elenco_date[$i])->where('id_cliente', 'C_'.$request->corso)->exists();
                	
                	if($ora_check){
                		$ora = Ora::where('id_maestro', $value->id)->where('data', $elenco_date[$i])->where('id_cliente', 'C_'.$request->corso)->first();
                	} else {
                		$ora = new Ora();
                	}
                    
                    $ora->data = $elenco_date[$i];
                    $ora->ora_in = $collettivo->ora_in;
                    $ora->ora_out = $collettivo->ora_out;
                    $ora->id_cliente =  "C_".$request->corso;
                    $ora->id_maestro = $value->id;
                    $ora->disciplina = $collettivo->disciplina;
                    $ora->venditore = "S";
                    $ora->id_cc = $collettivo->centro_costo;
                    
                    if($ora_check){
                    	$ora->update();
                    } else {
                    	$ora->save();
                    	
                    }
                    
                }
            }
            
            // maestri da cancellare
	        $maestri_diff = array_diff($maestri_tutti, $maestri_confermati);
	        
//	        dump($maestri_tutti, $maestri_confermati, $maestri_diff);
	               
            // cancello gli eliminati
            foreach($maestri_diff as $md){
            	$ora_check = Ora::where('id_maestro', $md)->where('data', $elenco_date[$i])->where('id_cliente', 'C_'.$request->corso)->exists();
                	
	        	if($ora_check){
	        		$ora = Ora::where('id_maestro', $md)->where('data', $elenco_date[$i])->where('id_cliente', 'C_'.$request->corso)->first();
	        		\DB::table('invoice_ora')->where('ora_id', $ora->id)->delete();
	        		CollettivoAllievi::where('id_collettivo', $request->corso)->where('giorno', $elenco_date[$i])->where('id_maestro', $md)->delete();
	        		$ora->delete();	        		
	        	}
            }
	            
        }
        
        
        return redirect(route('collective.index'))->with('message', 'Corso completato!');
                                    
    }
    
    public function udiffCompare($a, $b)
	{
	    return $a['ITEM'] - $b['ITEM'];
	}


    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Collettivo $collective)
    {
        $branch_id = $collective->centro_costo;
        if($collective->centro_costo != null){
            $collective->centro_costo = Sede::where('id',$collective->centro_costo)->first()->nome;
        }

        if($collective->specialita != null){
            $specialita =  Specialization::whereIn('id',[$collective->specialita])->get();
            $specialita_str = '';
            foreach ($specialita as $rec) {
                $specialita_str=  $specialita_str == '' ? $rec->nome : $specialita_str.'<br>'.$rec->nome;
            }
            $collective->specialita = $specialita_str;
        }


        switch ($collective->disciplina) {
            case 1:
                $collective->disciplina = 'Discesa';
              break;
            case 2:
            case 3:
                $collective->disciplina = 'Fondo';
              break;
            case 4:
                $collective->disciplina = 'Snowboard';
        }


        if($collective->frequenza != null){
            $frequenza = "";
            if($collective->frequenza == 'C')
                $frequenza = 'Continuativo';
            
            if (str_contains($collective->frequenza, 1) !== false) { 
                $frequenza = $frequenza == '' ? 'Lunedì' : $frequenza.'<br> Lunedì';
            }
            if (str_contains($collective->frequenza, 2)) { 
                $frequenza =$frequenza == '' ? 'Martedì' : $frequenza.'<br> Martedì';
            }
            if (str_contains($collective->frequenza, 3)) { 
                $frequenza = $frequenza == '' ? 'Mercoledì' : $frequenza.'<br> Mercoledì';
            }
            if (str_contains($collective->frequenza, 4)) { 
                $frequenza = $frequenza == '' ? 'Giovedì' : $frequenza.'<br> Giovedì';
            }
            if (str_contains($collective->frequenza, 5)) { 
                $frequenza = $frequenza == '' ? 'Venerdì' : $frequenza.'<br> Venerdì';
            }
            if (str_contains($collective->frequenza, 6)) { 
                $frequenza = $frequenza == '' ? 'Sabato' : $frequenza.'<br> Sabato';
            }
            if (str_contains($collective->frequenza, 0)) { 
                $frequenza = $frequenza == '' ? 'Domenica' : $frequenza.'<br> Domenica';
            }

            $collective->frequenza = $frequenza;
        }


        $availabilities = $collective->listDaysAndMasters();


        $strHtml = $this->setDailyList($collective);
        $students = CollettivoAllievi::where('id_collettivo', $collective->id)->groupBy('partecipante')->orderBy('id_cliente')->orderBy('partecipante')->get();	//paginate(30);
       
        $students_avail = array();
        foreach($students as $stu){
        	$students_avail[$stu->partecipante] = CollettivoAllievi::where('partecipante', $stu->partecipante)->where('id_collettivo', $collective->id)->orderBy('giorno')->pluck('id_maestro', 'giorno')->toArray();
        }

        //dd($branch_id);
        $companies = Company::join('company_branch','company_branch.company_id','=','companies.id')
                                ->where('company_branch.branch_id', $branch_id)
                                ->where('companies.client_id','!=', 4)
                                ->where('companies.active', 1)
                                ->orderBy('rag_soc')
                                ->pluck('companies.rag_soc', 'companies.id')
                                ->toArray();

        return view('areaseb::core.collective.show', compact('collective','students','students_avail','companies','availabilities','strHtml'));
    }


    private function setDailyList(Collettivo $collective){

        $strHtml ="<span id=\"imposta_sortable\" style=\"display: none\"></span><span id=\"imposta_droppable\" style=\"display: none\"></span>";

        $strHtml =  $strHtml."<span id=\"imposta_upload_data_asynchronously\" style=\"display: none\"></span>";
        
        $strHtml = $strHtml."<div style=\"clear: both; width: 90%; margin: auto; margin-bottom: 20px;\">
        <br><br>";

        $inizio = strtotime($collective->data_in);
        $fine = strtotime($collective->data_out);
        
        while($fine >= $inizio){
        	
            $day = ''; 	//date("d/m/Y", $inizio);
            
            if($collective->frequenza == 'Continuativo'){
                $day = date("d/m/Y", $inizio);
            } 
            else 
            {
                    if(strpos($collective->frequenza, ',')){
                    	$frequenza_ok = substr($collective->frequenza, 0, -1);
                    } else {
                    	$frequenza_ok = $collective->frequenza;
                    }
                   
                    $elenco_giorni = explode(",", $frequenza_ok);

                    //domenica
                    if(in_array("Domenica", $elenco_giorni)){
                        if(date("w", $inizio) == 0){
                            $day = date("d/m/Y", $inizio);
                        }
                    }

                    //lunedi'
                    if(in_array("Lunedì", $elenco_giorni)){
                        if(date("w", $inizio) == 1){
                            $day = date("d/m/Y", $inizio);
                        }
                    }

                    //martedi'
                    if(in_array("Martedì", $elenco_giorni)){
                        if(date("w", $inizio) == 2){
                            $day = date("d/m/Y", $inizio);
                        }
                    }

                    //mercoledi'
                    if(in_array("Mercoledì", $elenco_giorni)){
                        if(date("w", $inizio) == 3){
                            $day = date("d/m/Y", $inizio);
                        }
                    }

                    //giovedi'
                    if(in_array("Giovedì", $elenco_giorni)){
                        if(date("w", $inizio) == 4){
                            $day = date("d/m/Y", $inizio);
                        }
                    }

                    //venerdi'
                    if(in_array("Venerdì", $elenco_giorni)){
                        if(date("w", $inizio) == 5){
                            $day = date("d/m/Y", $inizio);
                        }
                    }

                    //sabato
                    if(in_array("Sabato", $elenco_giorni)){
                            if(date("w", $inizio) == 6){
                            $day = date("d/m/Y", $inizio);
                            }
                    }
            }

            if($day != "")
            {
                                                             
                list($g, $m, $a) = explode("/", $day);
                $data_m = "$a-$m-$g";

                $strHtml = $strHtml."<div style=\"width: 100%; clear: both; text-align: center;\">
                <h2>$day</h2>";
                     
                $query = "select distinct id_maestro from collettivo_allievi where id_collettivo = $collective->id and giorno = \"$data_m\" and id_maestro in (select id from masters);";
                $arr = \DB::table('collettivo_allievi')
                			->select('id_maestro')
                			->where('id_collettivo', $collective->id)
                			->where('giorno', $data_m)
                			->distinct()->get();
                		
                foreach ($arr as  $value) {
	
                    $strHtml = $strHtml."<div style=\"float: left; text-align: center;margin-left:10px\" id=\"$value->id_maestro-$data_m\">
                                            <table width=\"90%\" cellpadding=\"4\" cellspacing=\"0\" class=\"tabelle\">";

                    /*$contact = Contact::select('contacts.*','masters.color')
                                            ->join('masters','masters.contact_id','=','contacts.id')
                                            ->where('contacts.id', $value->id_maestro)
                                            ->first();*/
                    $contact = Master::find($value->id_maestro)->contact;
                    $contact->color = Master::find($value->id_maestro)->color;

                    $strHtml = $strHtml."<tr class=\"intestazione droppable_class\" rif=\"#$value->id_maestro-$data_m\" data=\"$data_m\" id_row_maestro=\"$value->id_maestro\">
                                                                <td colspan=\"4\">
                                                                $contact->cognome  $contact->nome
                                                                </td>
                                                            </tr>";

                    $query = "select id, id_cliente, partecipante, livello, eta, giorno from collettivo_allievi where id_collettivo = $collective->id and giorno = \"$data_m\" and id_maestro = $value->id_maestro;";
                    $arrPartecipants = \DB::select($query);
                    $i = 1;
                  
                    foreach ($arrPartecipants as  $item) {
                    	
                        $element = Contact::find($item->partecipante);
                        
                        if(!$element){
                        	$element = new Contact;
                        	$element->nome = 'NON';
                        	$element->cognome = 'TROVATO';
                        	$element->cellulare = 0;
                        }
                       	
                       	$last_date = CollettivoAllievi::where('id_collettivo', $collective->id)->where('partecipante', $element->id)->where('id_cliente', $item->id_cliente)->orderBy('giorno', 'DESC')->limit(1)->pluck('giorno');
                       	
                        // controllo se è l'ultimo giorno di corso
                        if($last_date->count() > 0 && $item->giorno == $last_date[0]){
                            $img = asset('img/coccarda.png');
                            $ultimo_giorno = "<img src=\"$img\" align=\"center\">";
                        } else {
                            $ultimo_giorno = "";
                        }
                        
                        $now = new \DateTime();
				        $date = new \DateTime($element->data_nascita);
                        $eta = $date->diff($now)->format("%y");

                        $strHtml = $strHtml."<tr style=\"color:black;background-color: $contact->color;\"rif=\"#$value->id_maestro-$data_m\" class=\"draggable_class\" id_row_collettivo_allievi=\"$item->id\">
                                                                    <td><b>$i</b></td>
                                                                    <td>$element->cognome $element->nome<br><small><a class=\"a-tel\" style=\"color: black\" href=\"tel:$element->cellulare\">$element->cellulare</a></small> </td>
                                                                    <td>$eta</td>
                                                                    <td>$item->livello<br>$ultimo_giorno</td>
                                                                </tr>";
                        $i++;
                    }

                    $strHtml = $strHtml."	</table>
                                    </div>";
                }
                $strHtml = $strHtml."</div>";
            }

            $day = "";
            
            $inizio = $inizio + 86400;
            
        }

                                        
            $strHtml = $strHtml."</div>
            <div style=\"clear: both;\"><br><br></div>";
            return $strHtml;
    }



    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Collettivo $collective)
    {
        $branches = [''=>'']+Sede::pluck('nome', 'id')->toArray();
        $specializzazioni = [''=>'']+Specialization::pluck('nome', 'id')->toArray();
        
        return view('areaseb::core.collective.edit', compact('collective','specializzazioni','branches'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Collettivo $collective)
    {
        $this->validate(request(), [
            'nome' => 'required',
            'data_in' => 'required',
            'data_out' => 'required',
            'disciplina' => 'required',
            'branch_id' => 'required'
        ]);
		
		// recupero le vecchie date
		$data_in_old = $collective->data_in;
		$data_out_old = $collective->data_out;
		$frequenza_old = $collective->frequenza;
		
		list($a_in_old, $m_in_old, $g_in_old) = explode("-", $data_in_old);
	    list($a_out_old, $m_out_old, $g_out_old) = explode("-", $data_out_old);
	        
		$date_diff_old = date_diff(date_create($data_in_old), date_create($data_out_old))->format('%a');
		$elenco_date_old = array();
		
		if($frequenza_old == "C"){
            for($i = 0; $i <= $date_diff_old; $i++){
                  $elenco_date_old[] = date("Y-m-d", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old));
              }
        } else {
            //domenica
            if(str_contains($frequenza_old, '0')){
                for($i = 0; $i <= $date_diff_old; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old)) == 0){
                        $elenco_date_old[] = date("Y-m-d", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old));
                    }
                }
            }
            
            //lunedi'
            if(str_contains($frequenza_old, '1')){
                for($i = 0; $i <= $date_diff_old; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old)) == 1){
                        $elenco_date_old[] = date("Y-m-d", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old));
                    }
                }
            }
            
            //martedi'
            if(str_contains($frequenza_old, '2')){
                for($i = 0; $i <= $date_diff_old; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old)) == 2){
                        $elenco_date_old[] = date("Y-m-d", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old));
                    }
                }
            }
            
            //mercoledi'
            if(str_contains($frequenza_old, '3')){
                for($i = 0; $i <= $date_diff_old; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old)) == 3){
                        $elenco_date_old[] = date("Y-m-d", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old));
                    }
                }
            }
            
            //giovedi'
            if(str_contains($frequenza_old, '4')){
                for($i = 0; $i <= $date_diff_old; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old)) == 4){
                        $elenco_date_old[] = date("Y-m-d", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old));
                    }
                }
            }
            
            //venerdi'
            if(str_contains($frequenza_old, '5')){
                for($i = 0; $i <= $date_diff_old; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old)) == 5){
                        $elenco_date_old[] = date("Y-m-d", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old));
                    }
                }
            }
            
            //sabato'
            if(str_contains($frequenza_old, '6')){
                for($i = 0; $i <= $date_diff_old; $i++){
                    if(date("w", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old)) == 6){
                        $elenco_date_old[] = date("Y-m-d", mktime(0, 0, 0, $m_in_old, $g_in_old+$i, $a_in_old));
                    }
                }
            }
        }

        //ordino l'array di date
        $date_old = array_map('strtotime', $elenco_date_old);
        sort($date_old);
        foreach($date_old as $k => $d){
            $elenco_date_old[$k] = date('Y-m-d', $d);
        }
		
		
		
		
		
		

        $nome = $request->nome;
        $data_in = explode("T",$request->data_in)[0];
        $data_out = explode("T",$request->data_out)[0];
        $ora_in =explode("T",$request->data_in)[1];
        $ora_out = explode("T",$request->data_out)[1];
        $disciplina =$request->disciplina;
        $centro_costo = $request->branch_id;
        $giorni = $request->giorni;
        $specializzazioni = implode(",",$request->specializzazioni);

        if($nome != "" && $data_in != "" && $data_out != "" && $ora_in != "" && $ora_out != "" && $centro_costo != "" && $disciplina != ""){
    									  	
	        $frequenza = "";
	            
	        if(isset($request->is_continuous)){
	            $frequenza = "C";
	        } else{
	            $frequenza =implode(",",$giorni);
	        }
	        
	        //modifico il collettivo
	        $collettivo = $collective;
	        $collettivo->nome = $nome;
	        $collettivo->data_in = $data_in;
	        $collettivo->data_out = $data_out;
	        $collettivo->frequenza =  $frequenza;
	        $collettivo->ora_in = $ora_in;
	        $collettivo->ora_out = $ora_out;
	        $collettivo->centro_costo = $centro_costo;
	        $collettivo->disciplina = $disciplina;
	        $collettivo->specialita = $specializzazioni;
	        $collettivo->save();

			$strHtml= "";
	          
	        list($a_in, $m_in, $g_in) = explode("-", $data_in);
	        list($a_out, $m_out, $g_out) = explode("-", $data_out);

	        //$date_diff = mktime(0, 0, 0, $m_out, $g_out, $a_out) - mktime(0, 0, 0, $m_in, $g_in, $a_in);
	        //$date_diff  = floor(($date_diff / 60 / 60 / 24) / 1);
	        $date_diff = date_diff(date_create($data_in), date_create($data_out))->format('%a');

	        //dd($g_in, $m_in, $a_in.'-'.$g_out, $m_out, $a_out.' '.$date_diff);
	        
	        if($frequenza == "C"){
	            for($i = 0; $i <= $date_diff; $i++){
	                  $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	              }
	        } else {
	            //domenica
	            if(str_contains($frequenza, '0')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 0){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	            
	            //lunedi'
	            if(str_contains($frequenza, '1')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 1){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	            
	            //martedi'
	            if(str_contains($frequenza, '2')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 2){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	            
	            //mercoledi'
	            if(str_contains($frequenza, '3')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 3){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	            
	            //giovedi'
	            if(str_contains($frequenza, '4')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 4){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	            
	            //venerdi'
	            if(str_contains($frequenza, '5')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 5){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	            
	            //sabato'
	            if(str_contains($frequenza, '6')){
	                for($i = 0; $i <= $date_diff; $i++){
	                    if(date("w", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in)) == 6){
	                        $elenco_date[] = date("Y-m-d", mktime(0, 0, 0, $m_in, $g_in+$i, $a_in));
	                    }
	                }
	            }
	        }

	        //ordino l'array di date
	        $date = array_map('strtotime', $elenco_date);
	        sort($date);
	        foreach($date as $k => $d){
	            $elenco_date[$k] = date('Y-m-d', $d);
	        }
    
			for($i = 0; $i <= count($elenco_date)-1; $i++){
			  
			  list($a, $m, $g) = explode("-", $elenco_date[$i]);
			  $data = "$g/$m/$a";
			  
			  $strHtml .= "<div style=\"width: 90%; text-align: left;\"><b>$data</b></div><br>
			              <table width=\"90%\" align=\"center\" cellpadding=\"3\" cellspacing=\"0\">
			                  <tr>";
			  
/*			    $maestri_del_collettivo = Ora::where('id_cliente', 'C_'.$collettivo->id)->pluck('id_maestro')->toArray();
			    $maestri_del_collettivo = array_unique($maestri_del_collettivo);
			    $maestri_del_collettivo = implode(',', $maestri_del_collettivo);
			    			    
			    $query = "select m.id, c.nome, c.cognome from contacts as c 
			                inner join contact_branch as cb on cb.contact_id = c.id
			                inner join masters as m on m.contact_id = c.id
			                where c.attivo = 1 and m.disciplina = $disciplina 
			                and c.id 
			                in
			                    (
			                    select a.contact_id as id_maestro 
			                    	from availabilities as a 
			                    	where a.data_start <= \"$elenco_date[$i]\" and \"$elenco_date[$i]\" <= a.data_end 
			                    	and branch_id = $request->branch_id
			                    ) 
			                and m.id not in
			                    (
			                    select id_maestro 
			                    	from ora 
			                    	where (data = \"$elenco_date[$i]\" and ora_in >= \"$ora_in\" and ora_out <= \"$ora_out\" 
			                    	or data = \"$elenco_date[$i]\" and ora_in <= \"$ora_in\" and ora_out >= \"$ora_out\"
			                    	or data = \"$elenco_date[$i]\" and ora_in < \"$ora_in\" and ora_out > \"$ora_in\"
			                    	or data = \"$elenco_date[$i]\" and ora_in < \"$ora_out\" and ora_out > \"$ora_out\") ";
			                    	if($maestri_del_collettivo){
			                    		$query .= "and id_maestro not in 
								                	(
								                	$maestri_del_collettivo
								                	)";
								    }
			    $query .= "     ) 
			                order by c.cognome;";
			    //dd($query);                                                                    
			    $arr = \DB::select($query);  */      
			    
			    
			    		    
			    			    
			    $ore_occupate = Ora::where('id_cliente', '!=', 'C_'.$collettivo->id)->where('data', $elenco_date[$i])->where('ora_in', '>=', $ora_in)->where('ora_out', '<=', $ora_out)
			    					->orWhere('data', $elenco_date[$i])->where('ora_in', '<=', $ora_in)->where('ora_out', '>=', $ora_out)->where('id_cliente', '!=', 'C_'.$collettivo->id)
			    					->orWhere('data', $elenco_date[$i])->where('ora_in', '<', $ora_in)->where('ora_out', '>', $ora_in)->where('id_cliente', '!=', 'C_'.$collettivo->id)
			    					->orWhere('data', $elenco_date[$i])->where('ora_in', '<', $ora_out)->where('ora_out', '>', $ora_out)->where('id_cliente', '!=', 'C_'.$collettivo->id)
			    					->pluck('id_maestro')
			    					->toArray();	
			    						    					
			    $day = $elenco_date[$i];
			    $branch_id = $request->branch_id;
			    			
			    $arr = Master::
			    		where('disciplina', $disciplina)
			    		->whereHas('availability', function($q) use($day, $branch_id) {
							$q->whereDate('data_start', '<=', $day)->whereDate('data_end', '>=', $day)->where('branch_id', $branch_id);
						})
						->whereHas('contact', function($qu) {
							$qu->where('attivo', 1)->orderBy('cognome');
						})
						->whereNotIn('id', $ore_occupate)
						->get();                          
			 
			 
			 
			 
			 
			     //recupero i dati
			     $k = 1;
			     foreach ($arr as $value) {
			     	
			     	$check_ora = Ora::where('id_maestro', $value->id)->where('data', $elenco_date[$i])->where('id_cliente', 'C_'.number_format($collettivo->id, 0, '', ''))->exists();
			        
			        if($check_ora == true){
			        	$checked = 'checked';
			        } else {
			        	$checked = '';
			        }
			        $strHtml .= "<td align=\"left\">
			                      <input type=\"checkbox\" name=\"$elenco_date[$i]-$value->id\" value=\"S\" $checked> ".$value->contact->cognome." ".$value->contact->nome."
			                  </td>";
			      if(($k % 5) == 0){
			        $strHtml .= "</tr><tr>";
			      }		
			      
			      $k++;	
			     }

			    $strHtml .= "	</tr>
			                </table>
			                <br><br>";
			}
          
            $strHtml .= "<input class=\"btn btn-primary\" type=\"submit\" value=\"Concludi\">
                      <input type=\"hidden\" name=\"corso\" value=\"$collettivo->id\">
                      <br><br>";
            
            // pulisco le date tolte
            foreach($elenco_date_old as $edo){
            	if(!in_array($edo, $elenco_date)){
            		
            		$ora_check = Ora::where('data', $edo)->where('id_cliente', 'C_'.$collective->id)->exists();
                	
		        	if($ora_check){
		        		$ore = Ora::where('data', $edo)->where('id_cliente', 'C_'.$collective->id)->get();
		        		foreach($ore as $ora){
		        			\DB::table('invoice_ora')->where('ora_id', $ora->id)->delete();
		        			$ora->delete();
		        		}		        		
		        		CollettivoAllievi::where('id_collettivo', $collective->id)->where('giorno', $edo)->delete();
		        		
		        	}
		        	
            	}
            }
                  
            return view('areaseb::core.collective.select_master', compact('collettivo','strHtml'));

	        //return redirect(route('collective.index'))->with('message', 'Corso modificato!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {    	
    	$ore = Ora::where('id_cliente', 'C_'.number_format($id, 0, '', ''))->pluck('id')->toArray();
    	$fattura_check = \DB::table('invoice_ora')->whereIn('ora_id', $ore)->distinct('invoice_id')->pluck('invoice_id')->toArray();
    	$check = '';
    	
    	foreach($fattura_check as $fattura){
    		if(Invoice::where('id', $fattura)->first()->aperta == 0){
    			$check = 'bad';
    		}
    	}
    	
    	if(count($fattura_check) > 0 && $check == 'bad'){
    		return redirect()->back()->with('message', 'Impossibile eliminare il corso, ore già fatturate a clienti');
    	}
    	
        CollettivoAcconti::where('id_collettivo', $id)->delete();
        CollettivoAllievi::where('id_collettivo', $id)->delete();
        \DB::table('invoice_ora')->whereIn('ora_id', $ore)->delete();
        Ora::whereIn('id', $ore)->delete();
        Item::whereIn('ora_id', $ore)->whereIn('invoice_id', $fattura_check)->delete();
        Collettivo::find($id)->delete();
        
        // se le fatture restano vuote vanno cancellate
        foreach($fattura_check as $fattura){
    		if(Invoice::where('id', $fattura)->first()->items()->count() == 0){
    			Invoice::where('id', $fattura)->delete();
    		}
    	}
    	
        return 'done';
    }


    public function deleteStudent($id)
    {
    	$student = CollettivoAllievi::find($id);
    	if(!$student){
    		return redirect()->back()->with('error', 'Allievo non trovato');
    	}
    	/*$ora = Ora::where('data', $student->giorno)->where('id_cliente', 'C_'.$student->id_collettivo)->where('id_maestro', $student->id_maestro)->first();
    	$invoice_ora = \DB::table('invoice_ora')->where('ora_id', $ora->id)->pluck('invoice_id')->toArray();
    	$invoice_check = Invoice::where('aperta', 1)->where('contact_id', $student->partecipante)->whereIn('id', $invoice_ora)->first();*/
    	$ora = Ora::where('id_cliente', 'C_'.$student->id_collettivo)->pluck('id')->toArray();
    	$invoice_check = Invoice::where('aperta', 1)->where('contact_id', $student->partecipante)->pluck('id')->toArray();
   	
    	if($invoice_check && $ora){
    		$records = Item::whereIn('invoice_id', $invoice_check)->whereIn('ora_id', $ora)->get();
  		
    		foreach($records as $record){
    			\DB::table('invoice_ora')->where('invoice_id', $record->invoice_id)->where('ora_id', $record->ora_id)->delete();
    		
	    		if($record){
	    			$record->delete();
	    		}
	    		if(Invoice::find($record->invoice_id)->items->count() == 0){
	    			Invoice::find($record->invoice_id)->delete();
	    		}
    		}
    		
    	}
    	 
    	CollettivoAcconti::where('id_cliente', $student->partecipante)->where('id_collettivo', $student->id_collettivo)->delete();
    	CollettivoAllievi::where('id_collettivo', $student->id_collettivo)->where('id_cliente', $student->id_cliente)->where('partecipante', $student->partecipante)->delete();
        
        return redirect()->back()->with('message', 'Allievo eliminato');
    }

    public function listStudentsByCompany(Request $request)
    {
        $c = Company::find($request->company_id);
        if($c->piva != null)
            return [];

        return json_encode(Contact::where('company_id', $request->company_id)->get());
    }

    public function detailStudent(Request $request)
    {
        $data =Contact::where('id',$request->contact_id)->first();
        $data->eta = $data->getAge($request->contact_id);
        return json_encode($data);
    }



    public function createStudent(Request $request)
    {
        try{
            $res = array();
            			
            $student = new CollettivoAllievi();
            $student->id_collettivo = $request->id_collettivo;
            $student->id_cliente = $request->id_cliente;
            $student->partecipante = $request->partecipante;
            $student->livello =  $request->livello;
            $student->eta = $request->eta;
            $student->save();


            $res['code'] = true;
            $res['message'] = 'Studente creato con successo!!';
            $res['data'] = null;
            echo json_encode($res);           
        }
        catch(Exception $e) {
            $res['code'] = false;
            $res['message'] = 'Operazione fallita!';
            $res['data'] = $e->getMessage();
            echo json_encode($res);
        }
    }


    public function updateStudent(Request $request)
    {
        try{
            \DB::beginTransaction();
            $res = array();
            
            $id_collettivo = $request->id_collettivo;
            $id_allievo = $request->id_allievo;
            
            if(isset($request->maestri)){
                $student_bup = CollettivoAllievi::find($request->id_allievo);
                $invoice_check = Invoice::where('aperta', 1)->where('contact_id', $student_bup->partecipante)->first();
                $collettivo =  Collettivo::find($student_bup->id_collettivo);

                if($invoice_check == null){
                    $invoice = new Invoice();
                    $invoice->contact_id = $student_bup->partecipante;
                    $invoice->branch_id = $collettivo->centro_costo;
                    $invoice->data = date("d/m/Y");
                    $invoice->data_registrazione = date("d/m/Y");
                    $invoice->pagamento = "RIDI";
                    $invoice->saldato = 0;
                    $invoice->aperta = 1;
                    $invoice->save();
                } else {
                    $invoice = $invoice_check;
                }    


                $nome_disciplina = 'Discesa';
                if($collettivo->disciplina == 2 || $collettivo->disciplina == 3)
                    $nome_disciplina = 'Fondo';
                if($collettivo->disciplina == 4)
                    $nome_disciplina = 'Snowboard';

                $elenco_spec = "";
                $specs= Specialization::whereIn('id', [$collettivo->specialita])->get();
                foreach ($specs as $value) {
                    $elenco_spec = $elenco_spec != '' ? $elenco_spec.','.$value->nome : $value->nome;
                }

				$solo_giorni = array();
				
                foreach ($request->maestri as $ele) {
                    if($ele != null){
                        list($gg, $id_maestro) = explode('_', $ele);
                        
                        $solo_giorni[] = $gg;
                        
                        $ss = CollettivoAllievi::where('id_collettivo', $collettivo->id)
                                                ->where('partecipante', $student_bup->partecipante)
                                                ->where('giorno', $gg)
                                                ->first();     
                        if($ss){
                            $newstudent = $ss;
                            $newstudent->livello =  $request->livello;
                            $newstudent->giorno = $gg;
                            $newstudent->id_maestro = $id_maestro;
                            $newstudent->update();
                        } else {
                        	$student = CollettivoAllievi::where('id_collettivo', $collettivo->id)
	                                                    ->where('partecipante', $student_bup->partecipante)
	                                                    ->whereNull('giorno')
	                                                    ->first();
	                        if($student){
	                            $newstudent = $student;
	                            $newstudent->livello =  $request->livello;
	                            $newstudent->giorno = $gg;
	                            $newstudent->id_maestro = $id_maestro;
	                            $newstudent->update();
	    
	                        }else{
                            	//dd($student_bup->partecipante, $request);
                                $newstudent = new CollettivoAllievi();//$student_bup->replicate();
                                $newstudent->id_collettivo = $collettivo->id;
                                $newstudent->id_cliente = $request->id_cliente;
                                $newstudent->partecipante = $student_bup->partecipante;
                                $newstudent->eta = $request->eta;
                                $newstudent->livello =  $request->livello;
                                $newstudent->giorno = $gg;
                                $newstudent->id_maestro = $id_maestro;
                                $newstudent->save();
                            } 
                        }
                        
                        
                        $descr = "<b>Corso collettivo:</b> " . Collettivo::find($collettivo->id)->nome . 
                        		"<br /><b>Data:</b>". \Carbon\Carbon::createFromFormat('Y-m-d', $gg)->format('d/m/Y') .
                                "<br /><b>Ora:</b> dalle ".$collettivo->ora_in." alle ".$collettivo->ora_out.
                                "<br /><b>Maestro:</b> ". Master::find($id_maestro)->contact->fullname ."
                                <br /><b>Disciplina:</b> $nome_disciplina
                                <br /><b>Specialit&agrave;:</b> $elenco_spec";


                        $ora = Ora::where('data', $gg)->where('id_cliente', 'C_'.$collettivo->id)->where('id_maestro', $id_maestro)->first();
                        $rel_invoice_ora = array('invoice_id' => $invoice->id,'ora_id' => $ora->id);
                        if(\DB::table('invoice_ora')->where($rel_invoice_ora)->count() == 0)
                            \DB::table('invoice_ora')->insert($rel_invoice_ora);

						$lista_ore_coll = array();
						$coll_all = CollettivoAllievi::where('id_collettivo', $collettivo->id)->where('partecipante', $student_bup->partecipante)->where('id_maestro', $id_maestro)->get();
						foreach($coll_all as $ca){
							$lista_ore_coll[] = Ora::where('id_cliente', 'C_' . $collettivo->id)->where('id_maestro', $ca->id_maestro)->where('data', $ca->giorno)->first()->id;
						}
						
                        //vericare se ho gia un item 
                        foreach($lista_ore_coll as $ora_coll){
	                        $record = Item::where('invoice_id', $invoice->id)->where('ora_id', $ora_coll)->first();
	                  
	                        if($record == null){
	                            $itemInvoice = new Item();
	                            $itemInvoice->product_id = 25;//da calcolare
	                            $itemInvoice->descrizione = $descr;
	                            $itemInvoice->qta = 1;
	                            $itemInvoice->importo = 0;
	                            $itemInvoice->perc_iva = 0;
	                            $itemInvoice->iva = 0;
	    
	                            $itemInvoice->invoice_id = $invoice->id;
	                            $itemInvoice->exemption_id = 12;
	                            $itemInvoice->ora_id = $ora_coll;
	                            $itemInvoice->save();
	                        }
	                        else
	                        {
	                            $record->descrizione = $descr;
	                            $record->save();
	                        }
	                    }

                    }
                }
                
                // pulisco i giorni tolti
                $giorni_da_togliere = CollettivoAllievi::where('id_collettivo', $collettivo->id)
                                                    ->where('partecipante', $student_bup->partecipante)
                                                    ->whereNotIn('giorno', $solo_giorni)
                                                    ->get();
                //dd($request, $solo_giorni, $giorni_da_togliere, $student_bup->partecipante, $student_bup->id_collettivo);
                
                foreach($giorni_da_togliere as $gg){
                	$invoice_check = Invoice::where('aperta', 1)->where('contact_id', $gg->partecipante)->first();
			    	$ora = Ora::where('data', $gg->giorno)->where('id_cliente', 'C_'.$gg->id_collettivo)->where('id_maestro', $gg->id_maestro)->first();
			    	
			    	if($invoice_check && $ora){
			    		$record = Item::where('invoice_id', $invoice_check->id)->where('ora_id', $ora->id)->first();
			    		
			    		\DB::table('invoice_ora')->where('invoice_id', $invoice_check->id)->where('ora_id', $ora->id)->delete();
					    
			    		if($record){
			    			$record->delete();
			    		}
			    		if($invoice_check->items->count() == 0){
			    			$invoice_check->delete();
			    		}
			    	}
			    	
			    	CollettivoAllievi::where('id_collettivo', $gg->id_collettivo)
                                                    ->where('partecipante', $gg->partecipante)
                                                    ->where('giorno', $gg->giorno)
                                                    ->delete();
                	
                }
                

                Contact::where('id', $student_bup->partecipante)->update(['livello'=> $request->livello]);

            \DB::commit();
            $res['code'] = true;
            $res['message'] = 'Studente modificato con successo!!';
            $res['data'] = $collettivo->id;
            echo json_encode($res);     
            }
            else{
                $res['code'] = false;
                $res['message'] = 'Operazione fallita!';
                $res['data'] = null;
                echo json_encode($res);
            }      
        }
        catch(Exception $e) {
            $res['code'] = false;
            $res['message'] = 'Operazione fallita!';
            $res['data'] = $e->getMessage();
            echo json_encode($res);
        }
    }


    public function manageImport(Request $request)
    {
        try{

            $res = array();

            $record = CollettivoAcconti::where('id_collettivo', $request->id_collettivo)->where('id_cliente', $request->id_allievo)->first();

            if($record == null ){
                $record = new CollettivoAcconti();
            }

            if(isset($request->chiuso)){
                $record->id_collettivo = $request->id_collettivo;
                $record->id_cliente = $request->id_allievo;
                $record->chiuso = 1;
            }
            else{
                $record->id_collettivo = $request->id_collettivo;
                $record->id_cliente = $request->id_allievo;
                $record->importo = $request->importo;
                $record->acconto1 = $request->accontouno;
                $record->acconto2 = $request->accontodue;
                $record->note =  $request->note;
            }

            $record->save();

            $res['code'] = true;
            $res['message'] = 'Studente creato con successo!!';
            $res['data'] = null;
            echo json_encode($res);           
        }
        catch(Exception $e) {
            $res['code'] = false;
            $res['message'] = 'Operazione fallita!';
            $res['data'] = $e->getMessage();
            echo json_encode($res);
        }
    }


    public function moveStudent(Request $request){
        try{
            $res = array();           

            $student = CollettivoAllievi::find($request->id_row_collettivo_allievi);
            $student->id_maestro =  $request->id_maestro_a; 
            $student->giorno =  $request->data_a; 

            $student->save();

            $res['code'] = true;
            $res['message'] = 'Successo!';
            $res['data'] = null;
            echo json_encode($res);
        }
        catch(Exception $e) {
            $res['code'] = false;
            $res['message'] = 'Operation failed!!';
            $res['data'] = $e->getMessage();;
            echo json_encode($res);

        }
    }
}
