@section('css')
    <link rel="stylesheet" href="{{asset('plugins/summernote/summernote-bs4.css')}}">
@stop

<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Dettagli</h3>
        </div>
        <div class="card-body">

            <div class="form-group">
                <label>Nome</label>
                {!! Form::text('nome', null, ['class' => 'form-control', 'required', 'placeholder' => 'Per referenza interna']) !!}
            </div>
            <div class="form-group">
                <label>Oggetto</label>
                {!! Form::text('oggetto', null, ['class' => 'form-control', 'placeholder' => "Oggetto dell'email"]) !!}
            </div>

            <div class="form-group">
                <label>SMTP</label>
                {!! Form::select('smtp_id', $smtps, null, ['class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                <label id="html-editor-title">Descrizione</label>
                {!!Form::textarea('descrizione', null, ['class' => 'form-control html-editor', 'placeholder' => "testo per l'anteprima"])!!}
            </div>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Associazioni</h3>
        </div>
        <div class="card-body">

            <div class="form-group">
                <label>Liste</label>

                @isset($newsletter)
                    {!! Form::select('list_id[]', $lists, $newsletter->lists, [
                        'class' => 'form-control select2bs4',
                        'multiple' => 'multiple',
                        'data-placeholder' => 'Seleziona almeno una lista',
                        'style' => 'width:100%',
                        'required']) !!}
                @else
                    {!! Form::select('list_id[]', $lists, old('list_id') ?? null, [
                        'class' => 'form-control select2bs4',
                        'multiple' => 'multiple',
                        'data-placeholder' => 'Seleziona almeno una lista',
                        'style' => 'width:100%',
                        'required']) !!}
                @endisset
                <small class="form-text text-muted">Seleziona i destinatari della newsletter, <a target="_BLANK" href="{{url('contacts/lists')}}">Vedi liste</a></small>
            </div>

            <div class="form-group">
                <label>Template</label>
                @isset($newsletter)
                    {!! Form::select('template_id', $templates, null, ['class' => 'custom-select', 'id' => 'template_id']) !!}
                @else
                    {!! Form::select('template_id', $templates, Areaseb\Core\Models\Template::Default()->id, ['class' => 'custom-select', 'id' => 'template_id']) !!}
                @endif
                <small class="form-text text-muted">Seleziona un template, <a target="_BLANK" href="{{url('templates')}}">Vedi templates</a></small>
            </div>

        </div>
    </div>

    <div class="card">
        <button type="submit" class="btn btn-block btn-primary btn-lg" id="submitForm"><i class="fa fa-save"></i> Salva</button>
    </div>

</div>

@push('scripts')
    <script src="{{asset('plugins/summernote/lang/summernote-it-IT.js')}}"></script>
    <script>

    var HelloButton = function (context) {
    var ui = $.summernote.ui;

    // create button
    var button = ui.button({
        contents: '<i class="fa fa-user"/> Nome',
        tooltip: 'hello',
        click: function () {
        // invoke insertText method with 'hello' on editor module.
        context.invoke('editor.insertText', ' %%%contact.fullname%%% ');
        }
    });

        return button.render();   // return button as jquery object
    }

    function updateTextArea(select)
    {
        let editor = $('textarea.html-editor');
        if($(select).find('option:selected').text() == 'Default')
        {
            $('#html-editor-title').text('Testo');
            editor.summernote({
                lang: 'it-IT',
                toolbar: [
                    ['mybutton', ['hello']],
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview']],
                ],
                buttons: {
                    hello: HelloButton
                }
            });
        }
        else
        {
            $('#html-editor-title').text('Descrizione');
            editor.summernote('destroy');
            editor.html('');
        }
    }

    updateTextArea($('select#template_id'))

    $('select#template_id').on('change', function(){
        updateTextArea($(this));
    });


    </script>
@endpush
