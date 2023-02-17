<div class="card card-outline card-info" id="extra-spese" style="display:none;">
    <div class="card-header">
        <h3 class="card-title">Extra Spese</h3>
    </div>
    <div class="card-body">

        <div class="row">
            <div class="col-sm-12 col-xl-6">

                <div class="form-group row">
                    <label class="col-sm-4 col-xl-8 col-form-label">Imballo</label>
                    <div class="col-sm-8 col-xl-4">
                        {!! Form::text('spese_imballo', null, ['class' => 'form-control input-decimal', 'maxlength' => '50']) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-xl-8 col-form-label">Trasporto</label>
                    <div class="col-sm-8 col-xl-4">
                        {!! Form::text('spese_trasporto', null, ['class' => 'form-control input-decimal', 'maxlength' => '50']) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-xl-8 col-form-label">Varie</label>
                    <div class="col-sm-8 col-xl-4">
                        {!! Form::text('spese_varie', null, ['class' => 'form-control input-decimal', 'maxlength' => '50']) !!}
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
