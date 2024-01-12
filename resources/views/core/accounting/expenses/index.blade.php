@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Categorie di spesa'])



@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">
                    <h3 class="card-title">Categorie di spesa</h3>
                    @can('expenses.write')
                        <div class="card-tools">
                            <a class="btn btn-warning btn-sm" href="{{url('expenses/modify')}}"> <i class="fas fa-edit"></i> Modifica Categorie</a>
                            <a class="btn btn-primary btn-sm" href="{{url('expenses/create')}}"> <i class="fas fa-plus"></i> Crea Categoria di spesa</a>
                        </div>
                    @endcan

                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Macro categoria</th>
                                    <th>Categoria</th>
                                    @can('expenses.write')
                                        <th></th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenses as $expense)
                                    <tr id="row-{{$expense->id}}">

                                        <td>
                                            @foreach($expense->categories as $category)
                                                @if($loop->last)
                                                    {{$category->nome}}
                                                @else
                                                    {{$category->nome}},
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            @if($user->can('costs.read'))
                                                <a class="defaultColor" href="{{url('costs?company_id=&category_id='.$expense->categories()->first()->id.'&anno=&mese=')}}">{{$expense->nome}}</a>
                                            @else
                                                {{$expense->nome}}
                                            @endif
                                        </td>
                                        @can('expenses.write')
                                            <td class="pl-3">
                                                {!! Form::open(['method' => 'delete', 'url' => url('expenses').'/'.$expense->id, 'id' => "form-".$expense->id]) !!}
                                                    <a href="{{route('expenses.edit', $expense->id)}}" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                                    @can('expenses.write')
                                                        <button type="submit" id="{{$expense->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                                    @endcan
                                                {!! Form::close() !!}
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
