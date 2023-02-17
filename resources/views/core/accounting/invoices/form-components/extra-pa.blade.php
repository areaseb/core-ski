<div class="card card-outline card-info" id="extra-pa" style="display:none;">
    <div class="card-header">
        <h3 class="card-title">Extra Pubblica Amministrazione</h3>
    </div>
    <div class="card-body">

        <div class="row">
            <div class="col-sm-12 col-xl-6">

                <div class="form-group row">
                    <label class="col-sm-4 col-xl-8 col-form-label">Num. Doc*</label>
                    <div class="col-sm-8 col-xl-4">
                        {!! Form::text('pa_n_doc', null, ['class' => 'form-control', 'maxlength' => '50']) !!}
                    </div>
                </div>

            </div>
            <div class="col-sm-12 col-xl-6">

                <div class="form-group row">
                    <label class="col-sm-4 col-xl-7 col-form-label">Data Doc*</label>
                    <div class="col-sm-8 col-xl-5">
                        @php
                            if(isset($invoice))
                            {
                                $pa_data_doc = is_null($invoice->pa_data_doc) ? null : $invoice->pa_data_doc->format('d/m/Y');
                            }
                            else
                            {
                                $pa_data_doc = null;
                            }
                        @endphp
                        {!! Form::text('pa_data_doc', $pa_data_doc, ['class' => 'form-control', 'maxlength' => '10', 'data-inputmask-alias' => 'datetime', 'data-inputmask-inputformat' => 'dd/mm/yyyy']) !!}
                    </div>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-xl-6">

                <div class="form-group row">
                    <label class="col-sm-4 col-xl-8 col-form-label">CUP*</label>
                    <div class="col-sm-8 col-xl-4">
                        {!! Form::text('pa_cup', null, ['class' => 'form-control', 'maxlength' => '50']) !!}
                    </div>
                </div>

            </div>
            <div class="col-sm-12 col-xl-6">

                <div class="form-group row">
                    <label class="col-sm-4 col-xl-7 col-form-label">CIG*</label>
                    <div class="col-sm-8 col-xl-5">
                        {!! Form::text('pa_cig', null, ['class' => 'form-control', 'maxlength' => '50']) !!}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


@push('scripts')
    <script>

    const extraPAFields = (t) => {
        var data = $('input[name="pa_data_doc"]');
        if(t == 'Pr' || t == '')
        {
            $('#extra-pa').css('display', 'none');
            data.val('');
        }
        else
        {
            $('#extra-pa').css({display: 'block'});
            if(data.val() == '')
            {
                data.val(moment().format('DD/MM/YYYY'));
            }
            data.inputmask();
        }
    }

    $('select[name="tipo_doc"]').on('change', function(){
        var tipo_doc = $(this).find(':selected').val();
        extraPAFields(tipo_doc);
    });

    extraPAFields($('select[name="tipo_doc"]').find(':selected').val());
    </script>
@endpush
