<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-xl-none">
                <div class="card-tools w100">

                    <div class="btn-group" role="group" style="float:right;display:inline;">
                        <a href="{{url('calendars')}}" class="btn btn-warning btn-sm"><i class="fas fa-calendar-alt"></i> Gestisci calendari</a>
                    </div>
                    @if($user->hasRole('super'))
                        <div class="btn-group w60 selectCalandarWrapper" role="group">
                            <div class="input-group">
                                <select class="form-control form-control-sm selectCalendar2" name="calendars[]" multiple="multiple">
                                    <option></option>
                                    @foreach($user->calendars as $cal)
                                        <option value="{{$cal->id}}">{{$cal->nome}} </option>
                                    @endforeach
                                    @foreach(Areaseb\Core\Models\Calendar::allNoCurrentUser() as $cal)
                                        <option value="{{$cal->id}}">{{$cal->nome}} {{$cal->user->fullname}}</option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button class="input-group-text overlay2 btn btn-sm" style="padding:3px 9px;font-size:15px;">Vedi</button>
                                </div>
                            </div>
                        </div>
                    @else

                        @include('areaseb::core.calendars.quick-link')


                    @endif
                </div>
            </div>

            <div class="card-body p-0">
                <div class="row">
                    <div class="col-xl-3 d-none d-xl-block">
                        <div class="text-center mt-5">
                            <div class="btn-group" role="group">
                                <a href="{{url('calendars')}}" class="btn btn-warning"><i class="fas fa-calendar-alt"></i> Gestisci calendari</a>
                            </div>
                        </div>
                        @if($user->hasRole('super'))
                            <div class="row text-center mt-5 ml-2">
                                <div class="col">
                                    <div class="input-group">
                                        <select class="form-control selectCalendar" name="calendars[]" multiple="multiple" style="width:80%;">
                                            <option></option>
                                            @foreach($user->calendars as $cal)
                                                <option value="{{$cal->id}}">{{$cal->nome}} </option>
                                            @endforeach
                                            @foreach(Areaseb\Core\Models\Calendar::allNoCurrentUser() as $cal)
                                                <option value="{{$cal->id}}">{{$cal->nome}} {{$cal->user->fullname}}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button class="input-group-text overlay btn">Vedi</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center mt-3 ml-2">
                                @isset($calendar)
                                    @foreach(Areaseb\Core\Models\Calendar::where('user_id', '!=', $user->id)->get() as $c)
                                        @if($c->id != $calendar->id)
                                            <a class="btn btn-secondary m-1" href="{{$c->id}}"><i class="fa fa-eye"></i>
                                                @if($c->nome == 'global')
                                                    {{$c->user->fullname}}
                                                @else
                                                    {{$c->nome}}
                                                @endif
                                             </a>
                                        @endif
                                    @endforeach
                                @endisset
                            </div>
                        @else
                            @include('areaseb::core.calendars.quick-link')
                        @endif
                    </div>
                    <div class="col-xl-9">
                        <div id="calendar"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
