@php
    $totList = Areaseb\Core\Models\NewsletterList::count();
@endphp
@if($totList > 0)
    @foreach(Areaseb\Core\Models\NewsletterList::latest()->take(3)->get() as $list)
        <a class="dropdown-item" href="{{$list->url}}">{{$list->nome}}</a>
    @endforeach
    <div class="dropdown-divider"></div>
@endif
@if(request()->input())
    <a href="{{url('lists/create?'.request()->getQueryString())}}" data-toggle="modal" data-target="#modal" class="dropdown-item btn-modal">Crea Lista</a>
@else
    <a href="{{url('lists/create')}}" data-toggle="modal" data-target="#modal" class="dropdown-item btn-modal">Crea Lista</a>
@endif
@if($totList > 3)
    <a href="{{url('lists')}}" class="dropdown-item">Tutte le liste ({{$totList}})</a>
@endif
