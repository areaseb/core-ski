@section('meta_title')
    <title>{{$title}}</title>
@stop

@section('title')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{$title}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{config('app.url')}}">Home</a></li>
                        @if(strstr($title, 'Sedi') || strstr($title, 'Sede') || strstr($title, 'Alloggi') || strstr($title, 'Ritrovi') || strstr($title, 'Ritrovo') || strstr($title, 'Segnaposto'))
                        	<li class="breadcrumb-item"><a href="{{config('app.url')}}settings">Settings</a></li>
                        @endif
                        @yield('breadcrumbs')
                        <li class="breadcrumb-item active">{{$title}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
@stop
