<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Dati Prodotto</h3>
        </div>
        <div class="card-body">

            <div class="form-group">
                <label>Nome*</label>
                {!! Form::text('nome', null, ['class' => 'form-control', 'required']) !!}
                @include('areaseb::components.add-invalid', ['element' => 'nome'])
            </div>

            <div class="form-group">
                <label>Codice</label>
                {!! Form::text('codice', null, ['class' => 'form-control']) !!}
                @include('areaseb::components.add-invalid', ['element' => 'codice'])
            </div>


            <div class="form-group">
                <label>Descrizione</label>
                {!! Form::textarea('descrizione', null, ['class' => 'form-control', 'rows' => 2]) !!}
            </div>


            <div class="row">
                <div class="col-sm-8">
                    <div class="form-group">
                        <label>Prezzo</label>
                        <div class="input-group">
                            {!! Form::text('prezzo', null, ['class' => 'form-control input-decimal']) !!}
                            <div class="input-group-append">
                                <span class="input-group-text input-group-text-sm" id="basic-addon2">00.00 €</span>
                            </div>
                        </div>
                        @include('areaseb::components.add-invalid', ['element' => 'prezzo'])
                    </div>

                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label>IVA Prodotto</label>
                        <div class="input-group">
                            @php
                                $perc = 22;
                                if(isset($product))
                                {
                                    if(!is_null($product->perc_iva))
                                    {
                                        $perc = $product->perc_iva;
                                    }
                                }
                            @endphp

                            {!! Form::number('perc_iva', $perc, ['class' => 'form-control']) !!}
                            <div class="input-group-append">
                                <span class="input-group-text input-group-text-sm" id="basic-addon2">00 %</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Costo</label>
                <div class="input-group">
                    {!! Form::text('costo', null, ['class' => 'form-control input-decimal']) !!}
                    <div class="input-group-append">
                        <span class="input-group-text input-group-text-sm" id="basic-addon2">00.00 €</span>
                    </div>
                </div>
                @include('areaseb::components.add-invalid', ['element' => 'costo'])
            </div>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="card card-outline card-danger">
        <div class="card-header">
            <h3 class="card-title">Caratteristiche Extra</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Periodo</label>
                {!! Form::select('periodo',[''=> '', '12' => 'Annuale', '1' => 'Mensile', '3' => 'Trimestrale', '6' => 'Semestrale'], null, ['class' => 'form-control']) !!}
            </div>


            <div class="form-group">
                <label>Categoria</label>
                {!! Form::select('categorie[]', $categorie, $selectedCategories, ['class' => 'form-control add-select', 'multiple' => 'multiple']) !!}
            </div>
        </div>
    </div>

    @if(\Illuminate\Support\Facades\Schema::hasTable('testimonials'))
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Commisione Agenti/Referenti</h3>
            </div>
            <div class="card-body py-1">
                <div class="form-group mt-1 mb-2">
                    <label>Percentuale</label>
                    <div class="input-group">
                        {!! Form::text('perc_agente', null, ['class' => 'form-control input-decimal']) !!}
                        <div class="input-group-append">
                            <span class="input-group-text input-group-text-sm" id="basic-addon2">00.00 %</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif


    @php
        $langs = Areaseb\Core\Models\Setting::ActiveLangsArray();
        unset($langs['it']);
    @endphp

    @if(count($langs))
        <div class="card card-outline card-warning collapsed-card">
            <div class="card-header">
                <h3 class="card-title">Multilingua</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i>
                  </button>
                </div>
            </div>
            <div class="card-body">

                @foreach($langs as $locale => $nome)
                    <div class="form-group">
                        <label>Nome {{$nome}}</label>
                        {!! Form::text('name_'.$locale, null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        <label>Desc {{$nome}}</label>
                        {!! Form::textarea('desc_'.$locale, null, ['class' => 'form-control', 'rows' => 2]) !!}
                    </div>
                @endforeach
            </div>
        </div>
    @endif


    <div class="card">
        <button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm"><i class="fa fa-save"></i> Salva</button>
    </div>

</div>

<div class="col-md-12">
    @include('areaseb::core.accounting.products.grappolo')
</div>

@section('scripts')
<script>
$('.add-select').select2({tags: true});
</script>
@stop
