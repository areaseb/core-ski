<div class="row">

<div class="col-lg-2 col-2">
    <div class="small-box bg-info">
        <div class="inner">
            <h3 class="mb-0">{{$totQuery->imponibile}}</h3>
            <p>Imponibile</p>
        </div>
        <div class="icon"><i class="ion ion-bag"></i></div>
    </div>
</div>

<div class="col-lg-2 col-2">
    <div class="small-box bg-warning">
        <div class="inner">
            <h3 class="mb-0">{{$totQuery->imposte}}</h3>
            <p>Imposte</p>
        </div>
        <div class="icon"><i class="ion ion-stats-bars"></i></div>
    </div>
</div>

<div class="col-lg-2 col-2">
    <div class="small-box bg-success">
        <div class="inner">
            <h3 class="mb-0">€ {{number_format($ritenuta, 2, ',', '.')}}</h3>
            <p>Ritenuta d'acconto</p>
        </div>
        <div class="icon"><i class="ion ion-cash"></i></div>
    </div>
</div>

<div class="col-lg-4 col-4">
    <div class="small-box bg-success">
        <div class="inner">
            <h3 class="mb-0">{{$totQuery->totale}}</h3>
            <p>Totale</p>
        </div>
        <div class="icon"><i class="ion ion-cash"></i></div>
    </div>
</div>

<div class="col-lg-2 col-2">
    <div class="small-box bg-danger">
        <div class="inner">
            <h3 class="mb-0">{{$daSaldare}}</h3>
            <p>Da Saldare</p>
        </div>
        <div class="icon"><i class="ion ion-alert"></i></div>
    </div>
</div>

{{-- @dd($esenzioni, $esenzioni_autofatture, $ritenuta) --}}

<div class="col-lg-6 col-6">
    <div class="small-box bg-info">
        <div class="inner">
        	@foreach($esenzioni as $esenzione => $valori)
	        	<div class="row">
	        		<div class="col-8"><br><b>{{$esenzione}}</b></div>
	        		<div class="col-2 text-center"><b>Imponibile</b><br>€ {{number_format($valori['imponibile'], 2, ',', '.')}}</div>
	        		<div class="col-2 text-center"><b>IVA</b><br>€ {{number_format($valori['iva'], 2, ',', '.')}}</div>
	        	</div>
        	@endforeach
            
            <p>Riepilogo IVA</p>
        </div>
        <!--<div class="icon"><i class="ion ion-cash"></i></div>-->
    </div>
</div>

<div class="col-lg-6 col-6">
    <div class="small-box bg-info">
        <div class="inner">
        	@foreach($esenzioni_autofatture as $esenzione => $valori)
	        	<div class="row">
	        		<div class="col-8"><br><b>{{$esenzione}}</b></div>
	        		<div class="col-2 text-center"><b>Imponibile</b><br>€ {{number_format($valori['imponibile'], 2, ',', '.')}}</div>
	        		<div class="col-2 text-center"><b>IVA</b><br>€ {{number_format($valori['iva'], 2, ',', '.')}}</div>
	        	</div>
        	@endforeach
            
            <p>Riepilogo IVA autofatture / integrazioni</p>
        </div>
        <!--<div class="icon"><i class="ion ion-cash"></i></div>-->
    </div>
</div>


</div>
