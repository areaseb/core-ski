@php
	$cache_key = microtime();
@endphp

@if($user->can('planning.read') || $user->can('collective.read'))
	<li class="nav-header text-uppercase">Planning</li>
	@can('planning.view')
	    <li class="nav-item">
	        <a href="{{url('planning')}}" class="nav-link" >
	            <i class="nav-icon fas fa-calendar-alt"></i>
	            <p>Planning</p>
	        </a>
	    </li>
	@endcan
    @can('collective.read')
		<li class="nav-item">
		    <a href="{{url('collective')}}?cache={{$cache_key}}" class="nav-link">
		        <i class="nav-icon fas fa-users"></i>
		        <p>Collettivi</p>
		    </a>
		</li>
	@endcan
@endif

@if($user->can('companies.read') || $user->can('notes.read') || $user->can('masters.read'))
    <li class="nav-header text-uppercase">Anagrafiche</li>
    @can('companies.read')
        <li class="nav-item">
            <a href="{{url('companies')}}" class="nav-link">
                <i class="nav-icon fas fa-user-tie"></i>
                <p>Clienti</p>
            </a>
        </li>
	@endcan
	@can('contacts-master.read')
        <li class="nav-item">
            <a href="{{url('contacts-master')}}" class="nav-link">
                <i class="nav-icon fas fa-skiing"></i>
                <p>Maestri</p>
            </a>
        </li>
	@endcan
	@can('notes.read')
        <li class="nav-item">
            <a href="{{route('notes.index')}}" class="nav-link">
                <i class="nav-icon fas fa-calendar-check"></i>
                <p>Richieste</p>
            </a>
        </li>
    @endcan
@endif


@can('calendars.view')
    <li class="nav-header text-uppercase">calendario</li>
    <li class="nav-item">
        <a href="{{url('calendars')}}/{{$user->default_calendar->id}}" class="nav-link" id="menu-cal">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>Calendario</p>
        </a>
    </li>
@endcan

    <li class="nav-header text-uppercase">contabilità</li>

    @if(\View::exists('agents.site-nav'))
        @include('agents.site-nav')
    @else
        @includeIf('agents::site-nav-commission')
    @endif
    @includeif('referrals::site-nav-commission')

    @includeIf('deals::side-nav-deal')
    @includeIf('rider-deals::side-nav-deal')

    @includeIf('killerquote::side-nav')
    @includeIf('riderquote::side-nav')

    @includeIf('deals::side-nav-conf-order')
    @includeIf('riderdeals::side-nav-conf-order')

    @if( $user->can('invoices.read') || $user->can('costs.read') )
        <li class="nav-item has-treeview fatture_papa">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-file-invoice"></i>
                <p>Fatture<i class="fas fa-angle-left right"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @can('invoices.read')
                    <li class="nav-item">
                        <a href="{{url('invoices')}}?tipo=F-A" class="nav-link fatture">
                            <i class="far fa-circle nav-icon text-success"></i>
                            <p>Fatture</p>
                        </a>
                    </li>
                    <li class="nav-item">
                    	@php
                    		$data = date('d/m/Y');
                    	@endphp
                        <a href="{{url('invoices')}}?tipo=R&range={{urlencode("$data").'+-+'.urlencode("$data")}}" class="nav-link ricevute">
                            <i class="far fa-circle nav-icon text-success"></i>
                            <p>Ricevute</p>
                        </a>
                    </li>

                @endcan
                @can('costs.read')
                    <li class="nav-item">
                        <a href="{{url('costs')}}" class="nav-link">
                            <i class="far fa-circle nav-icon text-danger"></i>
                            <p>Acquisti</p>
                        </a>
                    </li>
                @endcan
                @can('invoices.read')
                    <li class="nav-item">
                        <a href="{{url('insoluti')}}" class="nav-link">
                            <i class="far fa-circle nav-icon text-warning"></i>
                            <p>Insoluti</p>
                        </a>
                    </li>
                @endcan

                <li class="nav-item">
                    <a href="{{url('prima_nota')}}" class="nav-link">
                        <i class="nav-icon fas fa-file-invoice"></i>
                        <p>Prima nota</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{url('ore_aperte')}}" class="nav-link">
                        <i class="nav-icon fas fa-file-invoice"></i>
                        <p>Ore aperte</p>
                    </a>
                </li>


                <li class="nav-item">
                    <a href="{{url('collettivi_aperti')}}" class="nav-link">
                        <i class="nav-icon fas fa-file-invoice"></i>
                        <p>Collettivi aperti</p>
                    </a>
                </li>

                @includeif('renewals::site-nav')

                @includeif('rates.site-nav')

            </ul>
        </li>
    @endif



    @can('products.read')
        <hr style="background:rgba(255,255,255,.5); margin-top:0; margin-bottom: 2%; margin-left:2.5%;margin-right: 2.5%; height:.005rem; width:95%;">
        <li class="nav-item">
            <a href="{{url('products')}}" class="nav-link">
                <i class="nav-icon fas fa-tags"></i>
                <p>Prodotti</p>
            </a>
        </li>
    @endcan
    @can('expenses.read')
        <li class="nav-item">
            <a href="{{url('expenses')}}" class="nav-link">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>Categorie di spesa</p>
            </a>
        </li>
    @endcan
    @can('stats.read')
        <hr style="background:rgba(255,255,255,.5); margin-top:0; margin-bottom: 2%; margin-left:2.5%;margin-right: 2.5%; height:.005rem; width:95%;">
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-chart-line"></i>
                <p>Statistiche<i class="fas fa-angle-left right"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{url('stats/aziende')}}" class="nav-link" id="menu-stats-aziende">
                        <i class="far fa-circle nav-icon text-success"></i>
                        <p>Clienti</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="@if(date('m') >= 6) {{url('stats/categorie?year='.date('Y'))}} @else {{url('stats/categorie?year='.(date('Y')-1))}} @endif" class="nav-link" id="menu-stats-categorie">
                        <i class="far fa-circle nav-icon text-danger"></i>
                        <p>Categorie Prodotti</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{url('stats/balance')}}" class="nav-link" id="menu-stats-bilancio">
                        <i class="far fa-circle nav-icon text-info"></i>
                        <p>Bilancio</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{url('stats/maestri')}}" class="nav-link" id="menu-stats-maestri">
                        <i class="far fa-circle nav-icon text-warning"></i>
                        <p>Maestri</p>
                    </a>
                </li>
            </ul>
        </li>
    @endcan



    @includeIf('projects::side-nav')
    @includeIf('menus.side-nav-tools')
    @includeIf('maps::side-nav')

{{-- @endif --}}

@if($user->can('newsletters.read') || $user->can('lists.read') || $user->can('templates.read') )

    <li class="nav-header text-uppercase CampagnaLink">Campagne</li>

    @can('lists.read')
        <li class="nav-item CampagnaLink">
            <a href="{{url('lists')}}" class="nav-link">
                <i class="nav-icon fas fa-list"></i>
                <p>Liste</p>
            </a>
        </li>
    @endcan
    @can('lists.create')
        <li class="nav-item CampagnaLink">
            <a href="{{url('create-list')}}?sort=updated_at|desc" class="nav-link">
                <i class="nav-icon fas fa-user-plus"></i>
                <p>Crea Lista</p>
            </a>
        </li>
    @endcan
    @can('templates.read')
        <li class="nav-item CampagnaLink">
            <a href="{{url('templates')}}" class="nav-link">
                <i class="nav-icon fas fa-drafting-compass"></i>
                <p>Templates</p>
            </a>
        </li>
    @endcan
    @can('templates.read')
        <li class="nav-item CampagnaLink">
            <a href="{{url('newsletters')}}" class="nav-link">
                <i class="nav-icon fas fa-paper-plane"></i>
                <p>Newsletters</p>
            </a>
        </li>
    @endcan
@endif


@can('counters.read')
<li class="nav-header text-uppercase">Conta Ore</li>
    <li class="nav-item">
        <a href="{{url('counters')}}" class="nav-link" >
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>Conta Ore</p>
        </a>
</li>
@endcan

@if( $user->can('users.read') || $user->can('roles.read') || $user->can('referrals.read') || $user->can('agents.read'))
    <li class="nav-header text-uppercase">UTENTI</li>

    @can('users.read')
        <li class="nav-item">
            <a href="{{url('users')}}" class="nav-link">
                <i class="nav-icon fas fa-user"></i>
                <p>Utenti</p>
            </a>
        </li>
    @endcan

    @includeif('agents::site-nav')
    @includeif('referrals::site-nav')


    @can('roles.read')
        <li class="nav-item">
            <a href="{{url('roles')}}" class="nav-link">
                <i class="nav-icon fas fa-user-tag"></i>
                <p>Ruoli</p>
            </a>
        </li>
    @endcan

@endif
