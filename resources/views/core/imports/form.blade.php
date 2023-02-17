@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Import '.$type])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0">Importazione</h5>
                    <p class="mb-0">Dopo aver inserto il file, premi carica e vedrai un'anteprima dell'importazione. Questa ti permetterà di controllare che l'importazione carichi i valori nelle giuste colonne.</p>
                </div>
                <div class="card-body">
                    {!! Form::open(['url' => url('imports/peek'), 'files' => true, 'id' => 'csvPeeker']) !!}
                        <input type="text" name="header" class="d-none">
                        <div class="form-group">
                            <label for="csv">File input</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input name="file" type="file" class="custom-file-input" id="csv" accept=".csv">
                                    <label class="custom-file-label" for="csv" >Scegli il file</label>
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
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0">Campi da caricare</h5>
                    <p class="mb-0">Prepara un .csv (o un excel salvato in formato csv), con questi campi. Qui sotto potrai cambiare l'ordine delle colonne prima di importare il file.</p>
                    @if($type == 'Contatti')
                        <p class="mb-0">Per associare un contatto ad un'azienda, si dovranno prima caricare le aziende e poi aggiungere il campo partita iva nel file .csv</p>
                    @else
                        <p>Il campo <code>piva</code> è da intendersi come Partita Iva e/o Codifce fiscale. Nell'importazione se è un codice fiscale l'azienda verrà salvata come privato.</p>
                    @endif
                </div>
                <div class="card-body">

                        @php
                        foreach($fields as $field)
                        {
                            $arr[$field] = $field;
                        }
                        @endphp
                        <div class="row mb-2">
                            @foreach($fields as $key => $field)
                                <div class="col-sm-2 mb-2">
                                    {!! Form::select($field, $arr, null, ['class' => 'custom-select', 'placeholder' => '-- Vuoto -- ']) !!}
                                    <div style="font-size:70%;text-align:center;" class="result-{{$loop->index}}"></div>
                                </div>
                            @endforeach
                        </div>


                </div>
                <div class="card-footer d-none text-center" id="hiddenFooter">
                    {!! Form::open(['url' => url($formUrl), 'files' => true, 'id' => 'finalImport']) !!}
                        <button class="btn btn-success btn-xl" id="final">Importa Contatti</button>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="mb-0">Legenda</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Valore</th>
                                    <th>Definizione | Regola</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($desc as $key => $def)
                                    @if($def != '')
                                        <tr>
                                            <td>{{$fields[$key]}}</td>
                                            <td>{{$def}}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@stop

@section('scripts')
    <script type="text/javascript">

        bsCustomFileInput.init();

        $('input#csv').on('change', function(){
            $('button#upload').prop('disabled', false);
            $('button#upload').removeClass('btn-default');
            $('button#upload').addClass('btn-success');
        })

        $("form#csvPeeker").submit(function(e) {
            e.preventDefault();
            var form = $(this)[0];
            var formData = new FormData(form);

            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: $(form).attr('action'),
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                success: function (data) {
                    $.each(data, function(key, value){
                        $.each(value, function(k,v){
                            $('.result-'+k).append(v+'<br>');
                        })
                    });
                    $('#hiddenFooter').removeClass('d-none');
                }
            });
        });

    $("form#finalImport").submit(function(e) {
        e.preventDefault();

        $(document).Toasts('create', {
            title: 'Importazione in corso',
            autohide: true,
            class: 'bg-info',
            delay: 4000,
            body: 'Stiamo importando i contatti. Ti notificheremo ad operazione conclusa'
        });


        let headers = '';
        $.each($('select[class="custom-select"]'), function(){
            headers += $(this).find('option:selected').text()+',';
        })
        headers = headers.slice(0,-1);

        $('input[name="header"]').val(headers);

        var form = $("form#csvPeeker")[0];
        var formData = new FormData(form);

        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: $(this).attr('action'),
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 600000,
            success: function (data) {
                if(data === 'done')
                {
                    $(document).Toasts('create', {
                        title: 'Importazione completata',
                        autohide: true,
                        class: 'bg-success',
                        delay: 3000,
                        body: 'Tutti i nuovi contatti sono stati importati'
                    });
                    $('button#upload').prop('disabled', true);
                    $('button#final').prop('disabled', true);
                }
                console.log(data);
            }
        });
    });


    </script>
@stop
