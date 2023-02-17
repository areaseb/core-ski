<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Errore | {{config('app.name')}}</title>
    <link rel="stylesheet" href="{{asset('plugins/fontawesome-free/css/all.min.css')}}">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/adminlte.min.css')}}">
</head>
<body class="hold-transition login-page">

    <div class="login-box" style="width:auto;">
        <div class="login-logo">
            <a href="#"><img width="290" height="auto" src="{{Areaseb\Core\Models\Setting::DefaultLogo()}}" /></a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <b>Errore</b>
                <p>{{$message}}</p>
            </div>
            <div class="card-footer p-0">
                <a href="{{url()->previous()}}" class="btn btn-block btn-primary">Torna Indietro</a>
            </div>
        </div>
    </div>


<script src="{{asset('plugins/jquery/jquery.min.js')}}"></script>
<script src="{{asset('plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('js/adminlte.min.js')}}"></script>

</body>
</html>
