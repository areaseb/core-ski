<div class="row text-center mt-3 ml-2">
@if(isset($calendar))
    @foreach(Areaseb\Core\Models\Calendar::where('nome', '!=', 'global')->get() as $c)
        @if($c->id != $calendar->id)
            @if($c->nome == 'preventivi')
                @if($user->hasPermissionTo('killerquotes.read'))
                    <a class="btn btn-secondary m-1" href="{{$c->id}}"><i class="fa fa-eye"></i> {{$c->nome}}</a>
                @endif
            @endif
            @if($c->nome == 'scadenze')
                @if($user->hasPermissionTo('costs.read'))
                    <a class="btn btn-secondary m-1" href="{{$c->id}}"><i class="fa fa-eye"></i> {{$c->nome}}</a>
                @endif
            @endif
        @endif
    @endforeach

    @foreach(Areaseb\Core\Models\Calendar::where('nome', 'global')->where('privato', false)->get() as $c)
        @if($calendar->id == $c->id)
            <a class="btn btn-primary m-1" href="{{$c->id}}"><i class="fa fa-eye"></i> {{$c->user->name}}</a>
        @else
            <a class="btn btn-secondary m-1" href="{{$c->id}}"><i class="fa fa-eye"></i> {{$c->user->name}}</a>
        @endif
    @endforeach
    
    @foreach(Areaseb\Core\Models\Calendar::where('nome', '!=', 'global')->where('privato', true)->where('user_id', auth()->user()->id)->get() as $c)
        @if($calendar->id == $c->id)
            <a class="btn btn-primary m-1" href="{{$c->id}}"><i class="fa fa-eye"></i> {{$c->nome}}</a>
        @else
            <a class="btn btn-secondary m-1" href="{{$c->id}}"><i class="fa fa-eye"></i> {{$c->nome}}</a>
        @endif
    @endforeach
    
    @foreach(\DB::table('calendars_user')->where('user_id', auth()->user()->id)->get() as $c)
    	
    	<a class="btn btn-secondary m-1" href="{{$c->calendar_id}}"><i class="fa fa-eye"></i> {{Areaseb\Core\Models\Calendar::find($c->calendar_id)->nome}}</a>
    	
    @endforeach


@else
    @foreach(Areaseb\Core\Models\Calendar::where('nome', '!=', 'global')->get() as $c)

            @if($c->nome == 'preventivi')
                @if($user->hasPermissionTo('killerquotes.read'))
                    <a class="btn btn-secondary m-1" href="{{$c->id}}"><i class="fa fa-eye"></i> {{$c->nome}}</a>
                @endif
            @endif
            @if($c->nome == 'scadenze')
                @if($user->hasPermissionTo('costs.read'))
                    <a class="btn btn-secondary m-1" href="{{$c->id}}"><i class="fa fa-eye"></i> {{$c->nome}}</a>
                @endif
            @endif

    @endforeach

    @foreach(Areaseb\Core\Models\Calendar::where('nome', 'global')->where('privato', false)->get() as $c)

        <a class="btn btn-secondary m-1" href="{{$c->id}}"><i class="fa fa-eye"></i>{{$c->user->name}}</a>


    @endforeach
    
    @foreach(Areaseb\Core\Models\Calendar::where('nome', '!=', 'global')->where('privato', true)->where('user_id', auth()->user()->id)->get() as $c)
        
        <a class="btn btn-secondary m-1" href="{{$c->id}}"><i class="fa fa-eye"></i> {{$c->nome}}</a>
        
    @endforeach
    
    @foreach(\DB::table('calendars_user')->where('user_id', auth()->user()->id)->get() as $c)
    	
    	<a class="btn btn-secondary m-1" href="{{$c->calendar_id}}"><i class="fa fa-eye"></i> {{Areaseb\Core\Models\Calendar::find($c->calendar_id)->nome}}</a>
    	
    @endforeach


@endif

</div>
