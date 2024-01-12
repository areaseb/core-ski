@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Acconti'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">

                    <div class="row">
                    </div>

                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-php">
                            <thead>
                                <tr>
                                    <th>Maestro</th>
                                    <th>Data</th>
                                    <th>Importo</th>
                                    <th>Tipo</th>
                                    <th data-orderable="false" style="width:320px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                    <tr>
                                        <td>
                                        <select class="form-control" id="contact_id">
                                            <option></option>
                                            @foreach($contacts as $cal)
                                                <option value="{{$cal->id}}">{{$cal->nome}} {{$cal->cognome}}</option>
                                            @endforeach
                                        </select>

                                        </td>
                                        <td>
                                            <input type="date" class="form-control" id="created_at" name="created_at" >
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="amount" name="amount" >
                                        </td>
                                        <td>
                                        {!! Form::select('payment_type_id', $payment_type_id, null, ['class' => 'form-control', 'id' => 'payment_type_id', 'placeholder' => 'Tipo']) !!}
                                        </td>
                                        <td class="pl-2" style="position:relative;">
                                            <button onclick="create()" class="btn btn-success btn-icon btn-sm"><i class="fa fa-plus"></i></button>
                                        </td>
                                    </tr>

                                @foreach($records as $record)
                                    <tr>
                                        <td>{{ $record->contact($record->contact_id) }}</td>
                                        <td>{{ $record->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $record->amount }} €</td>
                                        <td>
                                            @if($record->payment_type_id == 0)
                                                Bonifico
                                            @endif
                                            @if($record->payment_type_id == 1)
                                                Contanti
                                            @endif
                                            @if($record->payment_type_id == 2)
                                                Assegno
                                            @endif
                                        </td>
                                        <td class="pl-2" style="position:relative;">
                                                {!! Form::open(['method' => 'delete', 'url' => 'downpayments/'.$record->id, 'id' => "form-".$record->id]) !!}
                                                    <button type="submit" id="{{$record->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                               {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                        <td><strong>Totale</strong></td>
                                        <td colspan="4"><strong>{{ $total }} €</strong></td>

                                </tr>
                                
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <p class="text-left text-muted">{{$records->count()}} of {{ $records->total() }} acconti</p>
                    {{ $records->appends(request()->input())->links() }}
                </div>

            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/js/autoComplete.min.js"></script>

<script>
  function create()
    {
        jQuery.ajax('/downpayments',
        {
                method: 'POST',
                data: {
                "_token": "{{csrf_token()}}",
                "payment_type_id": $('#payment_type_id').val(),
                "amount": $('#amount').val(),
                "created_at": $('#created_at').val(),
                "contact_id": $('#contact_id').val()
                },

                complete: function (resp) {
                    var result = JSON.parse(resp.responseText);
                    if(result.code){
                        $('.alert-success').show();                   
                        setInterval(function () {location.reload()}, 1000);
                    }
                    else{
                        $('.alert-danger').show();
                    }         
                }
        });
    }



</script>


@stop
