<div class="direct-chat-messages allNotes" style="height:auto">
@foreach($company->notes as $note)
    @include('areaseb::core.notes.show')
@endforeach
</div>
