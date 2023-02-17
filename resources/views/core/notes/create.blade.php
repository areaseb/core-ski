{!! Form::open(['url' => route('notes.store'), 'autocomplete' => 'off', 'id' => 'noteForm', 'files' => true]) !!}
    @include('areaseb::core.notes.form')
{!! Form::close() !!}
