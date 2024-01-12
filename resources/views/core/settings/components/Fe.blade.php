@php
	// Define fields
	$connettore['Aruba'] = [
		'connettore',
		'connettore_uid',
		'connettore_key',
		'user_sdi',
		'pwd_sdi',
		'domain_sdi',

		'nazione',
		'piva',
		'rag_soc',
		'regime',
		'indirizzo',
		'cap',
		'citta',
		'prov',
		'tel',
		'email',
		'web',
		'banca',
		'IBAN',
		'last_receive',
		'max_receive',
		'last_sync',
		'max_sync',
	];

	$connettore['Fatture in Cloud'] = [
		'token',
		'company_id',

		'nazione',
		'piva',
		'rag_soc',
		'regime',
		'indirizzo',
		'cap',
		'citta',
		'prov',
		'tel',
		'email',
		'web',
		'banca',
		'IBAN',
		'last_receive',
		'max_receive',
		'last_sync',
		'max_sync',
	];
@endphp

@foreach ($setting->fields as $key => $value)

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
    @else
        <div class="form-group row input-fe" for="{{$key}}">
            <label for="{{$key}}" class="col-sm-3 col-form-label">@lang('areaseb::forms.'.$key)</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="{{$key}}" value="{{$value}}">
            </div>
        </div>
    @endif

@endforeach

@push('scripts')
    <script>

		let connettoreAruba = [@foreach($connettore['Aruba'] as $connector) '{{$connector}}', @endforeach];
		let connettoreFeic = [@foreach($connettore['Fatture in Cloud'] as $connector) '{{$connector}}', @endforeach];


		function updateFields() {
			let connettore = $('select[name="connettore"]').val();

			let fields = (connettore == 'Aruba') ? connettoreAruba : connettoreFeic;

			$('.input-fe').each(function() {
				let fieldName = $(this).attr('for');

				if (!fields.includes(fieldName))
					$(this).hide()
				else
					$(this).show()

			});
		}

		updateFields()

		let connettore = $('select[name="connettore"]').change(function() {
			updateFields()
		});

    </script>
@endpush