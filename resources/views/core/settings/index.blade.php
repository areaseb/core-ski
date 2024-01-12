@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Settings'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Settings</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Modulo</th>
                                    <th>Dati</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($settings as $setting)
                                    <tr>
                                        <td>{{$setting->model}}</td>
                                        <td>{{$setting->count_fields}} campi</td>
                                        <td>
                                            <a href="{{$setting->url}}/edit" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                                    <tr>
                                        <td>Lista Sedi</td>
                                        <td></td>
                                        <td>
                                            <a href="{{route('offices.index')}}" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Lista Alloggi</td>
                                        <td></td>
                                        <td>
                                            <a href="{{route('housing.index')}}" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Lista Ritrovi</td>
                                        <td></td>
                                        <td>
                                            <a href="{{route('hangout.index')}}" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Lista Segnaposto</td>
                                        <td></td>
                                        <td>
                                            <a href="{{route('labels.index')}}" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
