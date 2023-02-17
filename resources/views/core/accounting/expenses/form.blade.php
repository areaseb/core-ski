<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-body">

            <div class="form-group">
                <label>Nome*</label>
                {!! Form::text('nome', null, ['class' => 'form-control', 'required']) !!}
                @include('areaseb::components.add-invalid', ['element' => 'nome'])
            </div>

            {{-- <div class="form-group">
                <label>Prezzo</label>
                <div class="input-group">
                    {!! Form::text('prezzo', null, ['class' => 'form-control input-decimal']) !!}
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2">00.00 €</span>
                    </div>
                </div>
                @include('areaseb::components.add-invalid', ['element' => 'prezzo'])
            </div> --}}

            <div class="form-group">
                <label>Categoria</label>
                {!! Form::select('categorie[]', $categorie, $selectedCategories, ['class' => 'form-control add-select', 'multiple' => 'multiple']) !!}
                <p class="text-muted"><small>Scrivi per creare una nuova categoria di spesa</small></p>
            </div>

        </div>
        <div class="card-footer p-0">
            <button type="submit" class="btn btn-block btn-success btn-lg" id="submitForm" style="border-top-left-radius:0;border-top-right-radius:0;"><i class="fa fa-save"></i> Salva</button>
        </div>
    </div>
</div>


@section('scripts')
<script>
$('.add-select').select2({tags: true, placeholder: 'Associa spesa ad una categoria'});
</script>
@stop
