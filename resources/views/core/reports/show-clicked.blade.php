@extends('areaseb::layouts.app')


@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}newsletters">Newsletter</a></li>
    <li class="breadcrumb-item"><a href="{{config('app.url')}}newsletters/{{$newsletter->id}}/reports">Report</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Clicks '.$newsletter->nome])


@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Contatti</h3>
                </div>
                <div class="card-body">
                    <table id="table" class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Click</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contacts as $contact)
                                <tr>
                                    <td><a href="{{$contact->url}}" class="defaultColor">{{$contact->fullname}}</a></td>
                                    <td>{{$contact->email}}</td>
                                    <td>
                                        @foreach ($contact->reports()->where('newsletter_id', $newsletter->id)->first()->clicks as $key => $value)
                                            <span class="badge badge-primary badge-pill">{{$value['url']}}</span>
                                        @endforeach
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

@section('scripts')
<script>
    $("#table").DataTable(window.tableOptions);
</script>
@stop
