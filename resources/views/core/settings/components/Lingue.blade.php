@php
$array = $setting->fields;
@endphp
<div class="row">
@foreach ($array as $lang => $gruppo)

    @php
        if($gruppo['LANG_ACTIVE'])
        {
            $bg = 'card-primary';
            $active = true;
        }
        else
        {
            $bg = 'card-danger collapsed-card';
            $active = false;
        }
    @endphp

<div class="col-sm-4">
    <div class="card {{$bg}}" id="lang-{{$lang}}">
        <div class="card-header">
            <h3 class="card-title">{{$gruppo['LANG_NAME']}}</h3>
            @if(!$active)
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i>
                    </button>
                </div>
            @endif
        </div>
        <div class="card-body">
            @foreach ($gruppo as $key => $value)

                @php
                    $hide = 'd-none';
                    if($key === 'LANG_ACTIVE')
                    {
                        $hide = '';
                    }
                @endphp

                <div class="form-group row {{$hide}}">
                    <label for="{{$key}}" class="col-sm-3 col-form-label">
                        @lang('forms.'.$key)
                    </label>
                    <div class="col-sm-9">
                        @if($key === 'LANG_ACTIVE')
                            <select class="custom-select activateLang" data-key="{{$lang}}" name="lang[{{$lang}}][{{$key}}]">
                                <option value="1" @if(intval($value) === 1) selected="selected" @endif>Sì</option>
                                <option value="0" @if(intval($value) === 0) selected="selected" @endif>No</option>
                            </select>
                        @else
                            <input type="text" class="form-control inputLang-{{$lang}}" name="lang[{{$lang}}][{{$key}}]" value="{{$value}}">
                        @endif
                    </div>
                </div>

            @endforeach
        </div>
    </div>
</div>
@endforeach
</div>


@push('scripts')
<script>
    $('select.activateLang').on('change', function(){
        let status = parseInt($(this).val());
        let key = parseInt($(this).attr('data-key'));

        if(status === 1)
        {
            $('#lang-'+key).removeClass('card-danger');
            $('#lang-'+key).addClass('card-primary');
        }
        else
        {
            $('#lang-'+key).addClass('card-danger');
            $('#lang-'+key).removeClass('card-primary');
        }

    });
</script>
@endpush
