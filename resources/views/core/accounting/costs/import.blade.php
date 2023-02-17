@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Crea da Fattura'])



@section('content')

    <div class="row">
        @include('areaseb::components.errors')
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="header-title">Crea Fattura d'acquisto da .xml</h3>
                </div>
                <div class="card-body">
                    {!! Form::open(['url' => url('api/costs/import'), 'files' => true, 'id' => 'xmlImport']) !!}
                        <div class="form-group">
                            <label for="xml">File input</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input name="file" multiple type="file" class="custom-file-input" id="xml" accept=".xml, .p7m">
                                    <label class="custom-file-label" for="xml" data-browse="Scegli">Scegli il file</label>
                                </div>
                                <div class="input-group-append">
                                    <button class="btn btn-default" id="upload" disabled>Carica</button>
                                </div>
                            </div>
                        </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>

@stop

@section('scripts')
    <script type="text/javascript">
        bsCustomFileInput.init();

        $('input#xml').on('change', function(){
            $('button#upload').prop('disabled', false);
            $('button#upload').removeClass('btn-default');
            $('button#upload').addClass('btn-success');
        })

    </script>
@stop
