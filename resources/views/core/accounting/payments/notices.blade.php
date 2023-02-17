<div class="row">
    <div class="col">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">Solleciti/Note</h3>
            </div>
            <div class="card-body">
                <div class="row">



                    <div class="col-sm-4">
                        <div class="card card-outline card-warning">
                            <div class="card-header">
                                Crea Nuovo
                            </div>
                            {!! Form::open(['url' => route('invoices.notices.store', $invoice->id)]) !!}
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="input-group" id="date-notice" data-target-input="nearest">
                                            {!! Form::text('date', date('d/m/Y'), ['class' => 'form-control', 'data-target' => '#date-notice', 'data-toggle' => 'datetimepicker']) !!}
                                            <div class="input-group-append" data-target="#date-notice" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        {!! Form::select('type', ['' => '','email' => 'Invio Email', 'telefono' => 'Telefonata', 'posta' => 'Posta', 'nota' => 'Nota'], null, ['class' => 'form-control', 'required']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::textarea('response',  null, ['class' => 'form-control', 'placeholder' => 'Esito ...', 'required', 'rows' => 4]) !!}
                                    </div>
                                </div>
                                <div class="card-footer p-0">
                                    <button type="submit" class="btn btn-block btn-sm btn-success">Salva</button>
                                </div>
                            {!! Form::close() !!}
                        </div>
                    </div>

                    @if($invoice->notices())

                            <div class="col-sm-8">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Data</th>
                                                <th>Tipo</th>
                                                <th>Esito</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($invoice->notices as $notice)
                                                <tr>
                                                    <td>{{$notice->date->format('d/m/Y')}}</td>
                                                    <td>{{ucfirst($notice->type)}}</td>
                                                    <td>{{$notice->response}}</td>
                                                    <td>
                                                        {!! Form::open(['url' => route('invoices.notices.delete', $notice->id), 'method' => 'delete']) !!}
                                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                                        {!! Form::close() !!}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                </div>
                            </div>

                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
