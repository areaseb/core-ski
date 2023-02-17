<div class="col-12 col-md-8 offset-md-2">
    <div class="card card-outline card-success">
        <div class="card-header">
            <div class="form-group">
                <label>Nome del ruolo</label>
                {!!Form::text('name', null, ['class' => 'form-control', 'required', 'autocomplete' => 'off'])!!}
                @include('areaseb::components.add-invalid', ['element' => 'name'])
            </div>
        </div>
        <div class="card-body">

            <fieldset>
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
            </fieldset>
        </div>
        <div class="card-footer p-0">
            <button type="submit" class="btn btn-block btn-success">Salva</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $('input[type="checkbox"]').on('click', function(){
            let order = parseInt($(this).attr('data-order'));
            let id = parseInt($(this).attr('data-order'));
            let model = $(this).attr('data-model');
            let status = $(this).prop('checked');


            if(order === 0)
            {

                $.each( $('input[data-model='+model+']'), function(){
                    $(this).prop('checked', status);
                });
            }
            else
            {
                $.each( $('input[data-model='+model+']'), function(){
                    if( parseInt($(this).attr('data-order')) === 0)
                    {
                        if( ($(this).prop('checked') === true) && !status )
                        {
                            $(this).prop('checked', false);
                        }
                    }
                });
                if( order === 2 )
                {
                    $.each( $('input[data-model='+model+']'), function(){

                        if( parseInt($(this).attr('data-order')) === 1)
                        {
                            if( ($(this).prop('checked') === false) && status )
                            {
                                $(this).prop('checked', true);
                            }
                        }

                    })
                }
                if( order === 3 )
                {
                    $.each( $('input[data-model='+model+']'), function(){

                        if( ($(this).prop('checked') === false) && status )
                        {
                            $(this).prop('checked', true);
                        }

                    })
                }

            }


        });
    </script>
@endpush
