<div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">

                    <div class="row">
                    </div>

                </div>
                <div class="card-body">
					
					<div class="row">
						<div class="col-md-8">
		                    <div class="table-responsive">
		                        <table class="table table-sm table-bordered table-striped table-php" id="availability">
		                            <thead>
		                                <tr>
		                                    <th>Dal</th>
		                                    <th>Al</th>
		                                    <th>Dove</th>
		                                    <th data-orderable="false"></th>
		                                </tr>
		                            </thead>
		                            <tbody>
		                                    <tr id="tr-creazione">
		                                        <td>
		                                            <input type="date" class="form-control" id="data_start" name="data_start" >
		                                        </td>
		                                        <td>
		                                            <input type="date" class="form-control" id="data_end" name="data_end" >
		                                        </td>
		                                        <td>
		                                        {!! Form::select('branch_id', $branches, null, ['class' => 'form-control', 'id' => 'branch_id', 'placeholder' => 'Luogo']) !!}
		                                        </td>
		                                        <td class="pl-2" style="position:relative;">
		                                            <button onclick="createAvailability()" class="btn btn-success btn-icon btn-sm"><i class="fa fa-plus"></i></button>
		                                        </td>
		                                    </tr>

		                                @foreach($availabilities as $availability)
		                                    <tr id="row-{{$availability->id}}">
		                                        <td>{{ date('d/m/Y', strtotime($availability->data_start))}}</td>
		                                        <td>{{ date('d/m/Y', strtotime($availability->data_end)) }}</td>
		                                        <td>{{ $availability->branch_desc }}</td>
		                                        <td class="pl-2" style="position:relative;">
		                                                {!! Form::open(['method' => 'delete', 'url' => 'availabilities/'.$availability->id, 'id' => "form-".$availability->id]) !!}
		                                                    <button type="submit" id="{{$availability->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
		                                               {!! Form::close() !!}
		                                        </td>
		                                    </tr>
		                                @endforeach
		                            </tbody>
		                        </table>
		                        
		                    </div>
		                </div>
		                <div class="col-md-4 text-center" style="padding-left: 4%;">
		                	<div id="myCalendar" data-language="it" data-day-format="DDD" data-month-format="month YYYY" data-first-day-of-the-week="2"></div>
		                </div>
	                </div>
                </div>

                <div class="card-footer text-center">
                    <p class="text-left text-muted">{{$availabilities->count()}} of {{ $availabilities->total() }} disponibiltà</p>
                    {{ $availabilities->appends(request()->input())->links() }}
                </div>

            </div>
        </div>
    </div>

<link rel="stylesheet" type="text/css" href="{{asset('plugins/jscalendar/jsCalendar.css')}}">
<style>
	.jsCalendar tbody td.jsCalendar-selected {
		background-color: orange;
	}
	.jsCalendar tbody td.jsCalendar-current {
		background-color: #83D8FF;
	}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="{{asset('plugins/jscalendar/jsCalendar.js')}}"></script>
<script type="text/javascript" src="{{asset('plugins/jscalendar/jsCalendar.lang.it.js')}}"></script>

<script>

    var contact_id = <?php echo $contact->id; ?>;

    function createAvailability()
    {
        var data_start = $('#data_start').val();
        var data_end = $('#data_end').val();
        
        if(data_start == '' || data_end == '' || $('#branch_id').val() == ''){
            alert('Compila tutti i campi per inserire una nuova disponibilità!')
            return;
        }

        const start = new Date(data_start);
        const end = new Date(data_end);

        if(start > end ){
            alert('La data finale non può essere maggiore di quella iniziale!')
            return;
        }


        jQuery.ajax('/availabilities',
        {
                method: 'POST',
                data: {
                "_token": "{{csrf_token()}}",
                "data_start": data_start,
                "data_end": data_end,
                "branch_id": $('#branch_id').val(),
                "contact_id": contact_id
                },

                complete: function (resp) {
                    $('.alert').hide();
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        location.reload()
                        /*$('#data_start').val('');
                        $('#data_end').val('');
                        $('#branch_id').val('');

                        var data = result.data;
                        let html = '<tr><td>'+data.data_start+'</td><td>'+data.data_end+'</td><td>'+data.branch_desc+'</td>';
                        html = html+'<td class="pl-2" style="position:relative;">' + 
                                               ' <form method="POST" action="/availabilities/' + data.id +'" accept-charset="UTF-8" id="form-' + data.id +'"><input name="_method" type="hidden" value="DELETE"><input name="_token" type="hidden" value="{{csrf_token()}}">'+
                                                    '<button type="submit" id="' + data.id +'" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>'+
                                              ' </form>'+
                                        '</td></tr>';

                        $('table#availability').find('tbody').append(html);*/

                        //$('.alert-success').show();                   
                        //setInterval(function () {location.reload()}, 1000);
                    }
                    else{
                        alert(result.message);
                    }         
                }
        });
    }
	
	function createAvaiList()
    {
    			
		jQuery.ajax('/availist',
        {
                method: 'GET',
                data: {
                "id": contact_id
                },

                complete: function (resp) {
                	var response = JSON.parse(resp.responseText);
                	var dates = [];
                	                	                	 
                	let dispo = response.map(function(element){
                		
                		var now = moment(element.data_start).clone();

					    while (now.isSameOrBefore(moment(element.data_end))) {
					        dates.push(now.format('DD/MM/YYYY'));
					        now.add(1, 'days');
					    }
					    
					});         
									
					
					// Create the calendar
					var myCalendar = jsCalendar.new("#myCalendar");
					// Set date
					myCalendar.select(dates); 
                	
                }
                
        });
		
    }
    
    createAvaiList();


</script>

