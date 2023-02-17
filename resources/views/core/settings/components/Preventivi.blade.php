@section('css')
    <link rel="stylesheet" href="{{asset('plugins/summernote/summernote-bs4.css')}}">
@stop

@foreach ($setting->fields as $key => $value)
    <div class="form-group row">
        <label for="{{$key}}" class="col-sm-3 col-form-label">@lang('areaseb::forms.'.$key)</label>
        <div class="col-sm-9">
            @if(strlen($key) == 2)
                <textarea class="form-control textarea" name="{{$key}}">{!!$value!!}</textarea>
            @else
                <input class="form-control" name="{{$key}}" value="{{$value}}">
            @endif
        </div>
    </div>
@endforeach

@push('scripts')
    <script src="{{asset('plugins/summernote/summernote-bs4.min.js')}}"></script>
    <script src="{{asset('plugins/summernote/lang/summernote-it-IT.js')}}"></script>
    <script>


        var nomeCliente = function (context) {
            var ui = $.summernote.ui;
            var button = ui.button({
                contents: '<i class="fa fa-user"/> Nome',
                tooltip: 'nome cliente',
                class: 'btn-primary',
                click: function () {
                    context.invoke('editor.insertText', ' %%%nome_azienda%%% ');
                }
            });
            return button.render();
        }



            let editor = $('textarea.textarea');
            editor.summernote({
                lang: 'it-IT',
                toolbar: [
                    ['mybutton', ['nome']],
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['view', ['fullscreen', 'codeview']],
                ],
                buttons: {
                    nome: nomeCliente
                }
            });


    </script>
@endpush
