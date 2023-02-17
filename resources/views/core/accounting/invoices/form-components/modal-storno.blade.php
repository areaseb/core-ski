
<div class="modal" tabindex="-1" role="dialog" id="invoice-modal">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleziona almeno una Fattura da stornare</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                {!! Form::open(['url' => '#']) !!}
                    <div class="form-group">
                        <label for="name" class="col-form-label">Fatture:</label>
                        {!! Form::select('invoice_id[]', $invoices, null, ['class' => 'form-control selectMultiple', 'data-placeholder' => 'Seleziona Fatture', 'id' => 'invoices', 'data-fouc', 'style' => 'width:100%', 'multiple' => 'multiple']) !!}
                    </div>
                {!! Form::close() !!}

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                <button type="submit" class="btn btn-primary btn-save-rif">Inserisci nel riferimento</button>
            </div>
        </div>
    </div>
</div>
