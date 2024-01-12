@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Segnaposto'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">

                    <div class="row">

                        <div class="col-12 text-right">

                            <div class="card-tools">

                                <div class="btn-group" role="group">
                                  <a class="btn btn-primary" href="{{route('labels.create')}}"><i class="fas fa-plus"></i> Crea Segnaposto</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-body">


                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-php">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Colore</th>
                                    <th data-orderable="false" style="width:320px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($labels as $item)
                                    <tr id="row-{{$item->id}}">
                                        <td>
                                            {{$item->nome}}
                                        </td>
                                        <td style="background-color: {{$item->colore}}">
                                            &nbsp;
                                        </td>


                                        <td class="pl-2" style="position:relative;">
                                        		<a title="modifica segnaposto" href="{{route('labels.edit', $item->id)}}" class="btn btn-warning btn-sm btn-icon"><i class="fa fa-edit"></i></a>
                                                {!! Form::open(['method' => 'delete', 'url' => 'labels/'.$item->id, 'id' => "form-".$item->id, 'style' => 'display: inline']) !!}
                                                    <button type="submit" id="{{$item->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                                {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <p class="text-left text-muted">{{$labels->count()}} of {{ $labels->total() }} label</p>
                    {{ $labels->appends(request()->input())->links() }}
                </div>

            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/js/autoComplete.min.js"></script>
@stop
