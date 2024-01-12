{{$event->summary}}
@if($event->companies()->exists())
    - {{$event->companies()->first()->rag_soc}}
@endif
