<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LOGIN | {{config('app.name')}}</title>
    <link rel="stylesheet" href="{{asset('plugins/fontawesome-free/css/all.min.css')}}">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/adminlte.min.css')}}">

    <script type="module">
		import 'https://cdn.jsdelivr.net/npm/@pwabuilder/pwaupdate';
		const el = document.createElement('pwa-update');
		document.body.appendChild(el);
	</script>
	<link rel="manifest" href="{{asset('manifest.json')}}">
	<meta name="theme-color" content="#3C8DBC"/>
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="msapplication-starturl" content="/">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, initial-scale=1, maximum-scale=5, minimum-scale=1, height=device-height">


</head>
<body class="hold-transition login-page">

    <div class="login-box">
        <div class="login-logo">
            <a href="#">{{config('app.name')}}</a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">


                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('error'))

                    <div class="alert alert-danger">
                        <ul>
                            <li>{{session('error')}}</li>
                        </ul>
                    </div>

                @endif


                <p class="login-box-msg">Login nella tua sessione</p>
                <form action="{{url('login')}}" method="post">
                    @csrf
                    <div class="input-group mb-3">
                        <input name="email" type="email" class="form-control" placeholder="Email" value="{{old('email')}}">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input name="password" type="password" class="form-control" placeholder="Password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <center><pwa-install>Installa APP</pwa-install></center>
    </div>


<script src="{{asset('plugins/jquery/jquery.min.js')}}"></script>
<script src="{{asset('plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('js/adminlte.min.js')}}"></script>
<script type="module" src="{{asset('pwa_install.js')}}"></script>

</body>
</html>
