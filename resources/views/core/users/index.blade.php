@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Utenti'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Utenti</h3>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <div class="btn-group" role="group">
                                <button id="filter" type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" data-display="static">
                                    Filtra
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    @foreach($roles as $role)
                                        <a class="dropdown-item" href="{{url('users')}}?role={{$role->id}}">{{$role->name}}</a>
                                    @endforeach
                                    @if(request()->input())
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="{{url('users')}}">Tutti</a>
                                    @endif
                                </div>
                            </div>
                            @can('users.write')
                                <a href="{{url('users/create')}}"class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Crea Utente</a>
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
                                    <th>Ruolo</th>
                                    <th>Email</th>
                                    <th>Data</th>
                                    @can('users.write')
                                        <th data-orderable="false"></th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr id="row-{{$user->id}}">
                                        <td>{{$user->contact != null ? $user->contact->fullname : ''}}</td>
                                        <td>{{$user->roles()->first() != null ? $user->roles()->first()->name : ''}}</td>
                                        <td>{{$user->email}}</td>
                                        <td data-sort="{{$user->created_at->timestamp}}">{{$user->created_at->format('d/m/Y')}}</td>
                                        @can('users.write')
                                            <td class="pl-2">
                                                {!! Form::open(['method' => 'delete', 'url' => $user->url, 'id' => "form-".$user->id]) !!}
                                                        <a href="{{$user->url}}/edit" title="modifica" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                                        <a href="{{route('users.edit.password', $user->id)}}" title="cambia password" class="btn-sm btn btn-secondary"><i class="fa fa-key"></i></a>
                                                        <a href="{{$user->url}}/permissions" title="permessi" class="btn btn-secondary btn-icon btn-sm"><i class="fas fa-user-tag"></i></a>
                                                    @can('users.delete')
                                                        <button type="submit" id="{{$user->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
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
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
    $("#table").DataTable(window.tableOptions);
</script>
@stop
