<div class="card card-outline card-info" id="extra-estero">
    <div class="card-header">
        <h3 class="card-title">Bollo</h3>
    </div>
    <div class="card-body">

        <div class="row">
            <div class="col-sm-12 col-xl-6">
                <div class="form-group row">
                    <label class="col-sm-4 col-xl-8 col-form-label">Importo bollo</label>
                    <div class="col-sm-8 col-xl-4">
                        {!! Form::text('bollo', 2, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-xl-6">
                <div class="form-group row">
                    <label class="col-sm-4 col-xl-6 col-form-label">Da imputare a</label>
                    <div class="col-sm-8 col-xl-6">
                        {!! Form::select('bollo_a', ["" => "", "cliente" => "Cliente", "azienda" => "Azienda"], null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


@push('scripts')
    <script>
	
	/*$('#extra-estero').css({display: 'none'});
	$('input[name="bollo"]').prop("disabled", true);*/
	
	$.get(baseURL+"api/companies/"+$('select[name="company_id"]').val()+'/discount-exemption', function( response ) {
        extraEsteroFields(response);
        defaultPaymentMethod(response.pagamento)

    });
    
    $.get(baseURL+"api/contacts/"+$('select[name="contact_id"]').val()+'/discount-exemption', function( response ) {
        extraEsteroFields(response);
        defaultPaymentMethod(response.pagamento)

    });
	
	
    const extraEsteroFields = (t) => {
        
        console.log(t.nation);
        
        if(t.nation == 'IT' && t.private == 0)
        {
        	$('#extra-estero').css({display: 'block'});
        	$("[name='perc_ritenuta']").val(20);
			$('input[name="bollo"]').prop("disabled", false);
        }
        if(t.nation == 'IT' && t.private == 1)
        {
        	//$('#extra-estero').css({display: 'none'});
        	$("[name='perc_ritenuta']").val(0);
			//$('input[name="bollo"]').prop("disabled", true);
        }
        if(t.nation != 'IT' && t.private == 0)
        {
        	$('#extra-estero').css({display: 'block'});
        	$("[name='perc_ritenuta']").val(0);
			$('input[name="bollo"]').prop("disabled", false);
        }
        if(t.nation != 'IT' && t.private == 1)
        {
        	//$('#extra-estero').css({display: 'none'});
        	$("[name='perc_ritenuta']").val(0);
			//$('input[name="bollo"]').prop("disabled", true);
        }
        
        
/*        if(t.nation != 'IT')
        {
            $('#extra-estero').css({display: 'block'});
        }
        else if(t.exemption_id != '')
        {
        	$('#extra-estero').css({display: 'block'});
        }
        else
        {
            $('#extra-estero').css('display', 'none');
        }*/
    }
   

    const defaultPaymentMethod = (p) => {
        if(p == '' || p == null)
            return;

        $('select[name="pagamento"]').val(p);
        $('select[name="pagamento"]').trigger('change');
    }


    $('select[name="company_id"]').on('select2:select', function(){
        console.log($(this).find(':selected').val());
        $.get(baseURL+"api/companies/"+$(this).find(':selected').val()+'/discount-exemption', function( response ) {
            extraEsteroFields(response);
            defaultPaymentMethod(response.pagamento)

        });
    });
    
    $('select[name="contact_id"]').on('select2:select', function(){
        console.log($(this).find(':selected').val());
        $.get(baseURL+"api/contacts/"+$(this).find(':selected').val()+'/discount-exemption', function( response ) {
            extraEsteroFields(response);
            defaultPaymentMethod(response.pagamento)

        });
    });

    @if(isset($invoice))
        @if($invoice->company != null && ($invoice->company->nation != 'IT' || $invoice->company->exemption_id != null))
            extraEsteroFields('XX');
        @endif
    @endif




    </script>
@endpush
