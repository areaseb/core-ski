<div class="row">

<div class="col-lg-9 col-sm-8 col-12">
    <div class="small-box bg-info">
        <div class="inner">
            <div class="row">
                <div class="col-3">
                    <h3 class="mb-0">{{$totQuery->imponibile}}</h3>
                    <p>Imponibile</p>
                </div>
                <div class="col-9">
                </div>
            </div>

        </div>
        <div class="icon"><i class="ion ion-bag" style="top:38%"></i></div>
        <a href="#" class="small-box-footer">&nbsp;&nbsp;</a>
    </div>
</div>

<div class="col-lg-3 col-sm-4 col-12">
    <div class="small-box bg-success">
        <div class="inner">
            <h3 class="mb-0">{{$totQuery->totale}}</h3>
            <p>{{$totQuery->perc}}% del totale</p>
        </div>
        <div class="icon"><i class="ion ion-stats-bars" style="top:38%"></i></div>
        <a href="#" class="small-box-footer">&nbsp;&nbsp;</a>
    </div>
</div>



</div>
