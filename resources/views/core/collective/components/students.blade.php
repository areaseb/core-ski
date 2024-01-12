<div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">

                    <div class="row">
                    </div>

                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-php">
                            <thead>
                                <tr>
                                    <th width="20%">Cliente</th>
                                    <th width="20%">Partecipante</th>
                                    <th width="5%">Età</th>
                                    <th width="20%">Livello</th>
                                    <th width="25%">Maestro</th>
                                    <th width="5%">Totale</th>
                                    <th data-orderable="false" width="5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                    <tr id="tr-creazione">
                                        <td>

                                            <div class="row">                                             
                                                <div class="col-md-9">
                                                    {!! Form::select('company_id', $companies, null, ['class' => 'form-control', 'id' => 'company_id', 'placeholder' => 'Cliente']) !!}
                                                </div>
                                                <div class="col-md-3">
                                                    <a href="/companies/create" class="btn btn-sm btn-primary btn-block"><b> <i class="fa fa-plus"></i></b></a>
                                                </div>                                           
                                            </div>

                                            
                                            
                                        </td>
                                        <td>
                                            <select id="contact_id" class='form-control'>
                                                <option value="">Contatto</option>
                                            </select>
                                        </td>
                                        <td>
                                            <label id="eta"></label>
                                        </td>
                                        <td>
                                            <select id="livello" class='form-control'>
                                                <option value="PRA">Primo Approccio</option>
                                                <option value="ELE">Elementare</option>
                                                <option value="BAS">Base</option>
                                                <option value="INT">Intermedio</option>
                                                <option value="AVA">Avanzato</option>
                                            </select>
                                        </td>
                                        <td colspan="3">
                                            <a href="#" onclick="createStudent()"  class="btn btn-sm btn-primary btn-block"><b> <i class="fa fa-plus"></i> Aggiungi</b></a>
                                        </td>

                                    </tr>

                                @foreach($students as $student)
                            
                                    <tr>
                                        <td><a href="/companies/{{$student->id_cliente}}">{{ $student->customerName($student->id_cliente) }}</a></td>
                                        <td>{{ $student->studentName($student->partecipante) }}</td>
                                        <td id="eta-{{$student->id}}">{{ $student->age($student->partecipante) }}</td>
                                        <td>
                                            <select id="{{$student->id}}" class='form-control'>
                                                <option {{ $student->livello == 'PRA' ? 'selected' : '' }} value="PRA">Primo Approccio</option>
                                                <option {{ $student->livello == 'ELE' ? 'selected' : '' }} value="ELE">Elementare</option>
                                                <option {{ $student->livello == 'BAS' ? 'selected' : '' }} value="BAS">Base</option>
                                                <option {{ $student->livello == 'INT' ? 'selected' : '' }} value="INT">Intermedio</option>
                                                <option {{ $student->livello == 'AVA' ? 'selected' : '' }} value="AVA">Avanzato</option>
                                            </select>
                                            </td>

                                        <td>
                                        	
											@foreach($availabilities as $av )
	                                            <div class="row">
	                                                <div class="col-md-6 ">
                                                        @foreach($av as $a )
                                                            {{ date('d/m/Y', strtotime($a[0]->data)) }}
                                                        @endforeach
	                                                </div>
	                                                <div class="col-md-6">
	                                                    <select class="av-{{$student->id}} form-control">
	                                                        <option value=""></option>
	                                                        @foreach($av as $a )
	                                                            @foreach($a as $r )
		                                                            @php
		                                                                //$selected = $student->selectedByMasterAndDate($collective->id, $r->data, $r->id_maestro, $student->partecipante) ? 'selected' : '';
		                                                                if(isset($students_avail[$student->partecipante][$r->data]) && $students_avail[$student->partecipante][$r->data] == $r->id_maestro){
		                                                                	$selected = 'selected';
		                                                                } else {
		                                                                	$selected = '';
		                                                                }
		                                                            @endphp
		                                                                <option value="{{$r->data.'_'.$r->id_maestro}}"  {{$selected}}>{{$r->nome .' ' .$r->cognome }}</option>
	                                                            @endforeach
	                                                        @endforeach
	                                                    </select>                                             
	                                                </div>
	                                                
	                                            </div>
                                            @endforeach
                                        </td>
                                        <td></td>
                                        <td class="pl-2" style="position:relative;">
                                                <button onclick="updateStudent({{$student->id}}, {{$student->id_cliente}})" class="btn btn-success btn-icon btn-sm"><i class="fa fa-save"></i></button>

                                                {{-- {!! Form::open(['method' => 'delete', 'url' => 'delete-student/'.$student->id, 'id' => "form-".$student->id]) !!}
                                                    <button type="submit" id="{{$student->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                               {!! Form::close() !!} --}}
                                               <a href="/delete-student/{{$student->id}}" onClick="confirm('Sicuro di voler procedere ?')" id="{{$student->id}}" class="btn btn-danger btn-icon btn-sm delete_"><i class="fa fa-trash"></i></a>
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
 
@push('scripts')
<script>
	
	$('select#company_id').select2({theme: 'bootstrap4', width: '100%', placeholder: 'Cliente'});
	
    var collective_id = '<?php echo $collective->id; ?>';
    
    
    function reloadDropDown(){
        jQuery.ajax('/api/list-students',
        {
            method: 'POST',
            data: {
            "_token": "{{csrf_token()}}",
            "company_id": $('select#company_id').val()
            },
            complete: function (resp) {
                $("#contact_id").empty();
                var str ='<option value="">Contatto</option>';
                if(resp !== null)
                {
                    var result = JSON.parse(resp.responseText);
                    console.log(result)
                    
                    result.forEach(element => {
                        console.log(element)
                        str = str + '<option value="'+element.id+'">'+element.nome + ' ' + element.cognome + '</option>';
                        console.log('str: ', str)
                    });

                    $('#contact_id').append(str)
                }
            }
        });
    }


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


    function createStudent()
    {
        jQuery.ajax('/api/create-student',
        {
                method: 'POST',
                data: {
                "_token": "{{csrf_token()}}",
                "id_cliente": $('#company_id').val(),
                "partecipante": $('#contact_id').val(),
                "livello": $('#livello').val(),
                "eta": $('#eta').val() != '' ? $('#eta').val() : $('#eta').text(),
                "id_collettivo": collective_id
                },

                complete: function (resp) {
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        location.reload(true)
                    }
                    
                }
        });
    }


    function updateStudent(student_id, id_cliente)
    {
        var maestri = [];
        console.log(student_id);
        var className = '.av-' + student_id;
        $(className).each(function(index, element) {
            maestri.push($(this).val())
        });
        console.log(maestri);

        jQuery.ajax('/api/update-student',
        {
                method: 'POST',
                data: {
                "_token": "{{csrf_token()}}",
                "id_allievo": student_id,
                "id_cliente": id_cliente,
                "livello": $('#' + student_id).val(),
                "eta": $('#eta-' + student_id).val() != '' ? $('#eta-' + student_id).val() : $('#eta-' + student_id).text(),
                "maestri": maestri,
                },

                complete: function (resp) {
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        location.reload();
                    }
                }
        });
    }


    



</script>
@endpush
