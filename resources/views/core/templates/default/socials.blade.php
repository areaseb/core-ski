@foreach(Areaseb\Core\Models\Setting::socials() as $social => $link)
    @if($link != '')
        <a href="{{$link}}"><img alt="{{$social}}" src="{{asset('editor/assets/images/social-icons/'.$social.'-03.png')}}"style="width:30px;" /></a>
    @endif
@endforeach
