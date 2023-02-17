@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}users">Utenti</a></li>
    <li class="breadcrumb-item"><a href="{{route('users.edit', $userToC->id)}}">{{$userToC->contact->fullname}}</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica password'])


@section('content')

    {!! Form::open(['url' => route('users.update.password', $userToC->id), 'autocomplete' => 'off', 'id' => 'passwordForm']) !!}
        <div class="row">
            @include('areaseb::components.errors')
            <div class="col-sm-6 offset-sm-3">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Cambio Password</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-group">
                                <input name="password" type="text" class="form-control" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Ripeti Password</label>
                            <div class="input-group">
                                <input name="password-confirmation" type="text" class="form-control" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="form-group">

                        </div>
                    </div>
                    <div class="card-footer p-0">
                        <input type="hidden" name="origin" value="{{url()->previous()}}">
                        <button type="submit" class="btn btn-sm btn-success btn-block">Aggiorna</button>
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}

@stop

@section('scripts')
<script>
    $('button.btn-success').on('click', function(e){
        e.preventDefault();

        if($('input[name="password"]').val().length < 8)
        {
            err('La password deve avere almeno 8 caratteri');
            return false;
        }

        if($('input[name="password"]').val() != $('input[name="password-confirmation"]').val())
        {
            err('Le password non combaciano')
            return false;
        }

        $('form#passwordForm').submit();

    });
</script>
@stop
