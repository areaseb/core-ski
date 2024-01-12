<div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">

                    <div class="row">
                    </div>

                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-php" id="accounting">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Partecipante</th>
                                    <th>Importo</th>
                                    <th>Acconto 1</th>
                                    <th>Acconto 2</th>
                                    <th style="width:10%;">Saldo</th>
                                    <th>Note</th>
                                    <th data-orderable="false" style="width:20px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
	                                @php
		                                $saldo = 0;
		                                $data = $student->getImportoData($student->partecipante,$collective->id);
		                                $disabled_button = false;
		                                if($data != null){
		                                    
		                                    if($data->acconto1 != null || $data->acconto1 != '')
		                                        $saldo = $data->importo - $data->acconto1;
		                                    if($data->acconto2 != null || $data->acconto1 != '')
		                                        $saldo = $saldo - $data->acconto2;

		                                    if($data->chiuso != null)
		                                        $disabled_button = true;
		                                }
	                                @endphp
                                    <tr>
                                        <td><a href="/companies/{{$student->id_cliente}}">{{ $student->customerName($student->id_cliente) }}</a></td>
                                        <td>{{ $student->studentName($student->partecipante) }} ({{Areaseb\Core\Models\CollettivoAllievi::where('partecipante', $student->partecipante)->where('id_collettivo', $collective->id)->count()}}gg)</td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-2 ">
                                                    €
                                                </div>
                                                <div class="col-md-10">
                                                    <input type="number" class="form-control" id="importo-{{$student->partecipante}}"  value="{{ $student->getImportoData($student->partecipante,$collective->id) != null ? number_format($student->getImportoData($student->partecipante,$collective->id)->importo, 2)  : 0}}">
                                                </div>
                                                
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-2 ">
                                                    €
                                                </div>
                                                <div class="col-md-10">
                                                    <input type="number" class="form-control" id="accontouno-{{$student->partecipante}}"  value="{{ $student->getImportoData($student->partecipante,$collective->id) != null ? number_format($student->getImportoData($student->partecipante,$collective->id)->acconto1,2)  : 0}}">
                                                </div>
                                                
                                            </div>

                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-2 ">
                                                    €
                                                </div>
                                                <div class="col-md-10">
                                                    <input type="number" class="form-control" id="accontodue-{{$student->partecipante}}"  value="{{ $student->getImportoData($student->partecipante,$collective->id) != null ? number_format($student->getImportoData($student->partecipante,$collective->id)->acconto2, 2)  : 0}}">
                                                </div>
                                                
                                            </div>
                                        </td>
                                        <td @if($saldo == 0 && isset($student->getImportoData($student->partecipante,$collective->id)->importo) && $student->getImportoData($student->partecipante,$collective->id)->importo > 0) class="bg-success" @endif id="saldo-{{$student->partecipante}}">
                                            € {{number_format($saldo,2)}}
                                        </td>
                                        <td>
                                            <textarea id="note-{{$student->partecipante}}" class="form-control" rows="4" cols="20">{{ $student->getImportoData($student->partecipante,$collective->id) != null ? $student->getImportoData($student->partecipante,$collective->id)->note  : 0}}</textarea>
                                        </td>

                                        <td class="pl-2" style="position:relative;">
                                            <button onclick="manageImport({{$student->partecipante}})"  {{$disabled_button ? 'disabled' : ''}} class="btn btn-success btn-icon btn-sm"><i class="fa fa-save"></i> </button>
                                            <button onclick="closeImport({{$student->partecipante}})" {{$disabled_button ? 'disabled' : ''}} class="btn btn-danger btn-icon btn-sm"><i class="fa fa-ban"></i> </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <p class="text-left text-muted">{{$students->count()}} {{-- of {{ $students->total() }} --}} allievi</p>
                    {{-- {{ $students->appends(request()->input())->links() }} --}}
                </div>

            </div>
        </div>
    </div>
 

<script>

    var collective_id = '<?php echo $collective->id; ?>';


    function reloadDataStudent(){
        
        jQuery.ajax('/api/get-student',
        {
            method: 'POST',
            data: {
            "_token": "{{csrf_token()}}",
            "contact_id": $('select#contact_id').val()
            },
            complete: function (resp) {
                if(resp !== null)
                {
                    var result = JSON.parse(resp.responseText);
                    $('#livello').prop('disabled', false)
                    console.log(result)
                    $('#eta').text(result.eta)
                    $('#livello').val(result.livello)
                }
            }
        });
    }


    

  /*  $('select#company_id').on('change', function(){
        console.log($(this).val())
        reloadDropDowns();
    });
*/


    function manageImport(student_id)
    {
        var importo = $('#importo-' + student_id).val();
        var accontouno = $('#accontouno-' + student_id).val();
        var accontodue = $('#accontodue-' + student_id).val();

/*        if((parseFloat(accontouno) + parseFloat(accontodue)) > parseFloat(importo))
        {
            alert('I due acconti non possono superare l\'importo totale')
            return;
        }*/

        jQuery.ajax('/api/manage-import',
        {
                method: 'POST',
                data: {
                "_token": "{{csrf_token()}}",
                "importo": importo,
                "accontouno": accontouno,
                "accontodue": accontodue,
                "note": $('#note-' + student_id).val(),
                "id_allievo": student_id,
                "id_collettivo": collective_id
                },

                complete: function (resp) {
                    var s = '#accounting tr #saldo-' + student_id;
                    console.log(s)
                    var saldo = parseFloat(importo) - (parseFloat(accontouno) + parseFloat(accontodue));
                    console.log(saldo)
                    $(s).html('€ ' + saldo.toFixed(2))
                    if(saldo == 0){
                    	$(s).addClass('bg-success')
                    } else {
                    	$(s).removeClass('bg-success')
                    }
                }
        });
    }


    function closeImport(student_id)
    {

        var importo = $('#importo-' + student_id).val();
        var accontouno = $('#accontouno-' + student_id).val() != '' ? $('#accontouno-' + student_id).val() : 0;
        var accontodue = $('#accontodue-' + student_id).val() != '' ? $('#accontodue-' + student_id).val() : 0;
        console.log(parseFloat(importo),parseFloat(accontouno),parseFloat(accontodue));


        /*if((parseFloat(accontouno) + parseFloat(accontodue)) < parseFloat(importo))
        {
            alert('Non puoi chiudere l\'importo perchè il totale versato non è sufficiente!')
            return;
        }*/

        jQuery.ajax('/api/manage-import',
        {
                method: 'POST',
                data: {
                "_token": "{{csrf_token()}}",
                "chiuso": 1,
                "id_allievo": student_id,
                "id_collettivo": collective_id
                },

                complete: function (resp) {
                   
                    //location.reload()
                }
        });
    }

</script>

