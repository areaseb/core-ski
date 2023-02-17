<div class="card card-outline card-info" id="extra-ddt" style="display:none;">
    <div class="card-header">
        <h3 class="card-title">Extra DDT</h3>
    </div>
    <div class="card-body">

        <div class="row">
            <div class="col-sm-12 col-xl-6">

                <div class="form-group row">
                    <label class="col-sm-4 col-xl-8 col-form-label">Num. DDT*</label>
                    <div class="col-sm-8 col-xl-4">
                        {!! Form::text('ddt_n_doc', null, ['class' => 'form-control', 'maxlength' => '50']) !!}
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-xl-6">
                <div class="form-group row">
                    <label class="col-sm-4 col-xl-7 col-form-label">Data DDT*</label>
                    <div class="col-sm-8 col-xl-5">
                        @php
                            if(isset($invoice))
                            {
                                $ddt_data_doc = is_null($invoice->ddt_data_doc) ? null : $invoice->ddt_data_doc->format('d/m/Y');
                            }
                            else
                            {
                                $ddt_data_doc = null;
                            }
                        @endphp
                        {!! Form::text('ddt_data_doc', $ddt_data_doc, ['class' => 'form-control', 'maxlength' => '10', 'data-inputmask-alias' => 'datetime', 'data-inputmask-inputformat' => 'dd/mm/yyyy']) !!}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
    <script>

    //add extra fields for DDT
    const extraDDTFields = (t) => {
        var data = $('input[name="ddt_data_doc"]');
        if(t == 'D')
        {
            $('#extra-ddt').css({display: 'block'});
            if(data.val() == '')
            {
                data.val(moment().format('DD/MM/YYYY'));
            }
            data.inputmask();
        }
        else
        {
            data.val('');
            $('#extra-ddt').css('display', 'none');
        }
    }

    extraDDTFields($('select[name="tipo"]').find(':selected').val());

    </script>
@endpush
