@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Calendari'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">I tuoi calendari</h3>
                    @can('calendars.write')
                        <div class="card-tools">
                            <a href="{{url('calendars/bind')}}" class="btn btn-primary"><i class="fas fa-link"></i> Collega calendario</a>
                            <a href="{{url('calendars/create')}}" data-toggle="modal" data-title="Crea Calendario" data-target="#modal" class="btn btn-primary btn-modal"><i class="fas fa-plus"></i> Crea calendario</a>
                        </div>
                    @endcan
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Privato</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($calendars as $calendar)
                                <tr id="row-{{$calendar->id}}" data-model="{{$calendar->class}}" data-id="{{$calendar->id}}">
                                    <td class="editable" data-field="nome">{{$calendar->nome}} {{$calendar->user->contact->fullname}}</td>
                                    <td>@if($calendar->privato) Sì @else No @endif</td>
                                    <td class="text-center">
                                        @if($calendar->nome == 'global')
                                            <a href="{{$calendar->url}}" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-eye"></i></a>
                                        @else

                                            {!! Form::open(['method' => 'delete', 'url' => $calendar->url, 'id' => "form-".$calendar->id]) !!}
                                                <a href="{{$calendar->url}}" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-eye"></i></a>
                                                @can('calendars.write')
                                                    <a href="{{$calendar->url}}/edit" data-toggle="modal" data-target="#modal" data-title="Modifica Calendario" class="btn-modal btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                                    @can('calendars.delete')
                                                        <button type="submit" id="{{$calendar->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                                    @endcan
                                                @endcan
                                            {!! Form::close() !!}

                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
