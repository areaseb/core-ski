@section('css')
    <link rel="stylesheet" href="{{asset('plugins/summernote/summernote-bs4.css')}}">
@stop

@include('areaseb::core.accounting.invoices.form-components.intestazione')
@include('areaseb::core.accounting.invoices.form-components.dettagli')
@include('areaseb::core.accounting.invoices.form-components.corpo')


<div class="col-md-6">

    @include('areaseb::core.accounting.invoices.form-components.extra-pa')
    @include('areaseb::core.accounting.invoices.form-components.extra-ddt')    
    @include('areaseb::core.accounting.invoices.form-components.extra-estero')
    
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Voci inserite</h3>
			@if(isset($invoice))
            	<a href="#" class="btn btn-danger btn-block col-md-3" id="btnDelItems" style="float:right">Cancella tutte le voci</a>
            @endif
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped voci">
                <thead>
                <tr>
                    <th class="pl-2">Codice</th>
                    <th>Qt</th>
                    <th>Prezzo<small>(€)</small></th>
                    <th>Sconto<small>(%)</small></th>
                    <th>Iva<small>(€)</small></th>
                    <th class="pr-2">Subtotale<small>(€)</small></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                	<tr>
                		<td colspan="5"><b>Totale</b></td>
                		<td colspan="2" class="tot_voci"></td>
                	</tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-footer p-0">

            @if(isset($invoice))
                @if($invoice->items()->exists())
                    <textarea class="d-none" name="itemsToForm" id="itemsToForm">{!!$items!!}</textarea>
                @else
                    <textarea class="d-none" name="itemsToForm" id="itemsToForm"></textarea>
                @endif
            @else
                <textarea class="d-none" name="itemsToForm" id="itemsToForm"></textarea>
            @endif

            @if(isset($invoice))
                <button class="btn btn-success btn-block" id="save"><i class="fa fa-save"></i> Salva</button>
            @else
                <button class="btn btn-success btn-block" id="save" disabled><i class="fa fa-save"></i> Salva</button>
            @endif


            <button class="d-none" type="submit">Real</button>
        </div>
    </div>
</div>
