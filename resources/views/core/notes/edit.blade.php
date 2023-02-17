{!! Form::model($note, ['url' => route('notes.update', $note->id), 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'noteForm', 'files' => true]) !!}
    @include('areaseb::core.notes.form')
{!! Form::close() !!}
