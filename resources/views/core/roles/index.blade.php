@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Ruoli'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ruoli</h3>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            @can('roles.write')
                                <a href="{{url('roles/create')}}"class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Crea Ruolo</a>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    @can('roles.write')
                                        <th data-orderable="false"></th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                    @if($role->name != 'super')
                                        <tr id="row-{{$role->id}}">
                                            <td>{{$role->name}}</td>
                                            @can('roles.write')
                                            <td class="pl-2">
                                                {!! Form::open(['method' => 'delete', 'url' => url('roles/'.$role->id), 'id' => "form-".$role->id]) !!}
                                                    <a href="{{url('roles/'.$role->id.'/edit')}}" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                                    @can('roles.delete')
                                                        <button type="submit" id="{{url('roles/'.$role->id)}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                                    @endcan
                                                {!! Form::close() !!}
                                            </td>
                                            @endcan
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
<script>
    $("#table").DataTable(window.tableOptions);
</script>
@stop
