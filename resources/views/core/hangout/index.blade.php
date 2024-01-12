@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Ritrovi'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">

                    <div class="row">

                        <div class="col-12 text-right">

                            <div class="card-tools">

                                <div class="btn-group" role="group">
                                  <a class="btn btn-primary" href="{{route('hangout.create')}}"><i class="fas fa-plus"></i> Crea Ritrovo</a>
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
                                    <th>Luogo</th>
                                    <th>Posto</th>
                                    <th>Sede</th>
                                    <th data-orderable="false" style="width:320px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ritrovi as $item)
                                    <tr id="row-{{$item->id}}">
                                        <td>
                                            {{$item->luogo}}
                                        </td>
                                        <td>
                                            {{$item->posto}}
                                        </td>
                                        <td>
                                        	{!! $item->getBranchDesc() !!}
                                        </td>

                                        <td class="pl-2" style="position:relative;">
                                        	<a title="modifica ritrovo" href="{{route('hangout.edit', $item->id)}}" class="btn btn-warning btn-sm btn-icon"><i class="fa fa-edit"></i></a>
                                            {!! Form::open(['method' => 'delete', 'url' => 'hangout/'.$item->id, 'id' => "form-".$item->id, 'style' => 'display: inline']) !!}
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
                    <p class="text-left text-muted">{{$ritrovi->count()}} of {{ $ritrovi->total() }} ritrovi</p>
                    {{ $ritrovi->appends(request()->input())->links() }}
                </div>

            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/js/autoComplete.min.js"></script>
@stop
