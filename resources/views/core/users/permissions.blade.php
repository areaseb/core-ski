@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Permessi ' . $utente->contact->fullname])


@section('content')
    <div class="row">
        <div class="col-12 col-md-8 offset-md-2">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Permessi</h3>
                </div>
                <div class="card-body">
                    @foreach($permissions as $model => $permission)
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-2"><label class="text-capitalize pt-1">@lang('permissions.'.$model)</label></div>
                                <div class="col-sm-10">

                                    @foreach($permission as $data)
                                        <div class="form-check form-check-inline pr-3">
                                            <input
                                                name="permission_id[]"
                                                value="{{$data['id']}}"
                                                class="form-check-input"
                                                type="checkbox"
                                                data-id="{{$data['id']}}"
                                                data-order="{{$loop->index}}"
                                                data-model="{{$model}}"
                                                @isset($role)
                                                    @if($role->hasPermissionTo($model.'.'.$data['action']))
                                                        checked="checked"
                                                        disabled
                                                    @endif
                                                @endisset
                                                >
                                            <label class="form-check-label">@lang('permissions.'.$data['action'])</label>
                                        </div>
                                    @endforeach
                                    @if(!$loop->last)
                                        <hr>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>

$('div.form-check').on('click', function(){
    if($(this).find('input[type="checkbox"]').is(':disabled'))
    {
        new Noty({
            text: "Non puoi cambiare i permessi del ruolo",
            type: 'error',
            theme: 'bootstrap-v4',
            timeout: 2500,
            layout: 'topRight'
        }).show();

        new Noty({
            text: "Cambiali dalla pagina ruolo",
            type: 'success',
            theme: 'bootstrap-v4',
            timeout: 2500,
            layout: 'topRight'
        }).show();
    }
});

$('input[type="checkbox"]').on('click', function(){
    let order = parseInt($(this).attr('data-order'));
    let id = parseInt($(this).attr('data-id'));
    let model = $(this).attr('data-model');
    let status = $(this).prop('checked');

    if(order === 0)
    {

        $.each( $('input[data-model='+model+']'), function(){
            if($(this).is(':disabled') === false)
            {
                $(this).prop('checked', status);
                $.post( "{{url('api/direct-permissions/'.$utente->id)}}", { _token: "{{csrf_token()}}", id: $(this).attr('data-id') }).done(function( data ) {
                    console.log(data)
                  });
            }

        });
    }
    else
    {
        $.post( "{{url('api/direct-permissions/'.$utente->id)}}", {
                _token: "{{csrf_token()}}",
                id: id,
                add: status
            }).done(function( data ) {
                new Noty({
                    text: data,
                    type: 'success',
                    theme: 'bootstrap-v4',
                    timeout: 2500,
                    layout: 'topRight'
                }).show();
          });
    }

});
</script>
@stop
