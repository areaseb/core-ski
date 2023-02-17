<nav class="main-header navbar navbar-expand navbar-white navbar-light">

<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>

    @includeif('top-search')

</ul>

<ul class="navbar-nav ml-auto">

    <li>
        <a class="nav-link" title="istruzioni e faq" href="{{route('faqs.index')}}">
            <i class="far fa-question-circle"></i>
        </a>
    </li>

    @include('areaseb::layouts.elements.notifications')

    <li>
        <a class="nav-link" data-toggle="dropdown" href="#" onclick="window.print()">
            <i class="fas fa-print"></i>
        </a>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
            {{$user->fullname}}
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="#" class="dropdown-item" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Logout
                <form id="logout-form" action="{{ url('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </a>
            @if($user->hasRole('super'))
                <a href="{{url('settings')}}" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a>
                <a href="#" class="dropdown-item emptyCache"><i class="fas fa-redo-alt"></i> Svuota Cache</a>
            @endif


         </div>
    </li>
  </ul>
</nav>
@push('scripts')
    <script>

        $('a.emptyCache').on('click', function(e){
            e.preventDefault();

            $.post(baseURL+'api/clear-cache', {_token: "{{csrf_token()}}"}).done(function(response){
                new Noty({
                    text: "Svuotamento della cache eseguito",
                    type: 'success',
                    theme: 'bootstrap-v4',
                    timeout: 2500,
                    layout: 'topRight'
                }).show();
            });
        });

    </script>
@endpush
