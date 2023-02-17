<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Disiscrizione | {{config('app.name')}}</title>
    <link rel="stylesheet" href="{{asset('plugins/fontawesome-free/css/all.min.css')}}">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/adminlte.min.css')}}">
</head>
<body class="hold-transition login-page">

    <div class="login-box">
        <div class="login-logo">
            <img class="img-fluid" src="{{Areaseb\Core\Models\Setting::DefaultLogo()}}" alt="{{config('app.name')}}"/>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                Gentile <b>{{$contact->fullname}}</b>, <br>ci dispiace che hai lasciato la newsletter di {{config('app.name')}}.
                <br>
                <small>
                    Per qualsiasi chiarimento in merito non esiti a contattarci.
                </small>
            </div>
        </div>
    </div>


<script src="{{asset('plugins/jquery/jquery.min.js')}}"></script>
<script src="{{asset('plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('js/adminlte.min.js')}}"></script>

</body>
</html>
