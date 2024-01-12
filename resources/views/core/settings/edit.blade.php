@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}settings">Settings</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Modifica Settings'])


@section('content')

    {!! Form::model($setting, ['url' => $setting->url, 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'settingForm', 'files' => true]) !!}
        <div class="row">
            @include('areaseb::components.errors')
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">

                        <div class="form-group row">
                            <label for="Modello" class="col-sm-3 col-form-label">@lang('forms.model')</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="model"  value="{{$setting->model}}" disabled>
                            </div>
                        </div>
                        @if($setting->model == 'Dashboard')

                            @foreach ($setting->fields as $key => $value)
                                <div class="form-group row">
                                    <label for="{{$key}}" class="col-sm-3 col-form-label">@lang('areaseb::forms.'.$key)</label>
                                    <div class="col-sm-9">
                                        {{-- <input type="text" class="form-control" name="{{$key}}" value="{{$value}}"> --}}

                                        <select class="custom-select" name="{{$key}}">
                                            <option value="" @if($value == '') selected="selected" @endif>Nascondi</option>
                                            <option value="1" @if($value != '') selected="selected" @endif>Mostra</option>
                                        </select>

                                    </div>
                                </div>
                            @endforeach

                        @elseif($setting->model == 'Lingue')

                            @include('areaseb::core.settings.components.Lingue')

                        @elseif($setting->model == 'Email Trattative')

                            @include('areaseb::core.settings.components.Trattative')

                        @elseif($setting->model == 'Email Fatture')

                            @include('areaseb::core.settings.components.Fatture')
                        @elseif($setting->model == 'Email Preventivi')

                            @include('areaseb::core.settings.components.Preventivi')

						@elseif($setting->model == 'Fe')

							@include('areaseb::core.settings.components.Fe')

                    @elseif($setting->model == 'SMTP')
                                @php
                                    $c = 1;
                                @endphp
                            @foreach ($setting->fields as $smtp => $values)
                                    @foreach ($values as $key => $value)
                                        <div class="form-group row">
                                            <label for="{{$key}}" class="col-sm-3 col-form-label">
                                                #{{$c}} @lang('areaseb::forms.'.$key)
                                            </label>
                                            <div class="col-sm-9">
                                                @if($key === 'MAIL_ENCRYPTION')

                                                    <select class="custom-select" name="smtp[{{$smtp}}][{{$key}}]">
                                                        <option value="" @if(is_null($value)) selected="selected" @endif>non-protetto</option>
                                                        <option value="SSL" @if($value == 'SSL') selected="selected" @endif>SSL</option>
                                                        <option value="TLS" @if($value == 'TLS') selected="selected" @endif>TLS</option>
                                                    </select>
                                                @elseif($key == 'MAIL_DRIVER')
                                                    <select class="custom-select" name="smtp[{{$smtp}}][{{$key}}]">
                                                        <option value="smtp" @if($value == 'smtp') selected="selected" @endif>smtp</option>
                                                        <option value="localhost" @if($value == 'localhost') selected="selected" @endif>localhost</option>
                                                    </select>
                                                @else
                                                    <input type="text" class="form-control" name="smtp[{{$smtp}}][{{$key}}]" value="{{$value}}">
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="col-12" style="background:#000;min-height:2px; margin-bottom:20px;"></div>
                                    @php
                                        $c++;
                                    @endphp
                            @endforeach

                        @else

                            @foreach ($setting->fields as $key => $value)
                                @if(strpos($key, '_img'))

                                    <div class="form-group row">
                                        <label for="{{$key}}" class="col-sm-3 col-form-label" style="line-height:16px;">@lang('areaseb::forms.'.$key)</label>
                                        <div class="col-sm-9">

                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="{{$key}}" lang="it" name="{{$key}}">
                                                <label class="custom-file-label" for="{{$key}}" data-browse="Sfoglia">
                                                    @if($value != "")
                                                        {{$value}}
                                                    @else
                                                        Scegli file
                                                    @endif
                                                </label>
                                            </div>

                                            <script>
                                                document.querySelector("#{{$key}}").addEventListener('change',function(e){
                                                    var fileName = document.getElementById("{{$key}}").files[0].name;
                                                    var nextSibling = e.target.nextElementSibling
                                                    nextSibling.innerText = fileName
                                                });
                                            </script>

                                        </div>
                                    </div>

                                @else

                                    @if(strpos($key, 'regime') !== false)

                                        <div class="form-group row">
                                            <label for="{{$key}}" class="col-sm-3 col-form-label">@lang('areaseb::forms.'.$key)</label>
                                            <div class="col-sm-9">
                                                {!! Form::select('regime', config('invoice.regime'), $value, ['class' => 'custom-select']) !!}
                                            </div>
                                        </div>

                                    @elseif($key == 'connettore')
                                        <div class="form-group row">
                                            <label for="{{$key}}" class="col-sm-3 col-form-label">@lang('areaseb::forms.'.$key)</label>
                                            <div class="col-sm-9">
                                                <select class="custom-select" name="{{$key}}">
                                                    <option value="" @if(is_null($value)) selected="selected" @endif></option>
                                                    <option value="Aruba" @if($value == 'Aruba') selected="selected" @endif>Aruba</option>
                                                    <option value="Fatture in Cloud" @if($value == 'Fatture in Cloud') selected="selected" @endif>Fatture in Cloud</option>
                                                </select>
                                            </div>
                                        </div>

                                    @elseif($key == 'default_color')
                                        <div class="form-group row">
                                            <label for="{{$key}}" class="col-sm-3 col-form-label">@lang('areaseb::forms.'.$key)</label>
                                            <div class="col-sm-9">
                                                <input type="color" class="form-control" name="{{$key}}" value="{{$value}}">
                                            </div>
                                        </div>

                                    @else
                                        <div class="form-group row">
                                            <label for="{{$key}}" class="col-sm-3 col-form-label">@lang('areaseb::forms.'.$key)</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="{{$key}}" value="{{$value}}">
                                            </div>
                                        </div>
                                    @endif

                                @endif

                            @endforeach

                        @endif

                        <div class="form-group">
                            <div class="row">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-success" id="submitForm"><i class="fa fa-save"></i> Salva</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}

@stop
