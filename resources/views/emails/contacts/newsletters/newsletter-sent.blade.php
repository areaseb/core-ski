@component('mail::message')
# Invio Terminato

L'invio della newsletter ({{$newsletter->oggetto}}) è terminato. Entra per vedere le statistiche.

@component('mail::button', ['url' => url('newsletters')])
    Vedi report
@endcomponent

{{ config('app.name') }}
@endcomponent
