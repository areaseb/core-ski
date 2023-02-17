@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Video CRM'])


@section('content')
    <div class="row">
        @foreach($videos as $title => $code)
            <div class="col-xl-3 col-sm-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{$title}}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="embed-responsive embed-responsive-16by9">
                          <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{{$code}}?rel=0" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
@stop
