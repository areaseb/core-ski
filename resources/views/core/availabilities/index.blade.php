@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Disponibilità'])


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
                                    <th>Dal</th>
                                    <th>Al</th>
                                    <th>Dove</th>
                                    <th data-orderable="false" style="width:320px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                    <tr>
                                        <td>
                                            <input type="date" id="data_start" name="data_start" >
                                        </td>
                                        <td>
                                            <input type="date" id="data_end" name="data_end" >
                                        </td>
                                        <td>
                                        {!! Form::select('branch_id', $branches, null, ['class' => 'form-control', 'id' => 'branch_id', 'placeholder' => 'Luogo']) !!}
                                        </td>
                                        <td class="pl-2" style="position:relative;">
                                            <button onclick="create()" class="btn btn-success btn-icon btn-sm"><i class="fa fa-plus"></i></button>
                                        </td>
                                    </tr>

                                @foreach($availabilities as $availability)
                                    <tr>
                                        <td>{{ $availability->data_start }}</td>
                                        <td>{{ $availability->data_end }}</td>
                                        <td>{{ $availability->branch_id }}</td>
                                        <td class="pl-2" style="position:relative;">
                                                {!! Form::open(['method' => 'delete', 'url' => 'availabilities/'.$availability->id, 'id' => "form-".$availability->id]) !!}
                                                    <button type="submit" id="{{$availability->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                               {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <p class="text-left text-muted">{{$availability->count()}} of {{ $availability->total() }} disponibiltà</p>
                    {{ $availability->appends(request()->input())->links() }}
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
        jQuery.ajax('/availabilities',
        {
                method: 'POST',
                data: {
                "_token": $('meta[name="csrf-token"]').attr('content'),
                "data_start": $('#data_start').val(),
                "data_end": $('#data_end').val(),
                "branch_id": $('#branch_id').val(),
                "contact_id": $('#branch_id').val()
                },

                complete: function (resp) {
                    $('.alert').hide();
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
