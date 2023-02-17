@extends('areaseb::layouts.app')


@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}newsletters">Newsletter</a></li>
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Report '.$newsletter->nome])


@section('content')

    <div class="row">

        <div class="col-md-3">

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Dettagli</h3>
                </div>
                <div class="card-body">
                    <p><b>Inizio spedizione</b><br> {{$stats->inizio->created_at->format('d/m/Y H:i:s')}}</p>
                    <p><b>Fine spedizione</b><br> {{$stats->fine->created_at->format('d/m/Y H:i:s')}}</p>
                    <p><b>Totale contatti</b><br> {{$stats->totali}}</p>
                    <p><b>Totale errore</b><br> {{$stats->errore}}</p>
                    <p><b>Totale inviate</b><br> {{$stats->inviate}}</p>
                    <p><b>Totale aperte</b><br> {{$stats->aperte}}</p>
                    <p><b>Totale unsubscribed</b><br> {{$stats->unsubscribed}}</p>
                </div>
            </div>

        </div>

        <div class="col-md-9">
            <div class="row">

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Aperture </h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <input type="text" class="knob"
                                    value="{{round(($stats->aperte/$stats->inviate*10000)/100)}}"
                                    data-width="100" data-height="100" data-fgColor="#007bff"
                                    data-readonly="true">

                                <div class="knob-label">{{$stats->aperte}} su {{$stats->inviate}}</div>
                            </div>
                        </div>
                        @if($stats->aperte)
                            <div class="card-footer text-center">
                                <a href="{{url('newsletters/'.$newsletter->id.'/reports/aperte')}}" class="btn btn-sm btn-primary">Vedi contatti</a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Errore </h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <input type="text" class="knob"
                                    value="{{round(($stats->errore/$stats->totali*10000)/100)}}"
                                    data-width="100" data-height="100" data-fgColor="#e0a800"
                                    data-readonly="true">

                                <div class="knob-label">{{$stats->errore}} su {{$stats->totali}}</div>
                            </div>
                        </div>
                        @if($stats->errore)
                            <div class="card-footer text-center">
                                <a href="{{url('newsletters/'.$newsletter->id.'/reports/errore')}}" class="btn btn-sm btn-warning">Vedi email con errore</a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Disiscritti </h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <input type="text" class="knob"
                                    value="{{round(($stats->unsubscribed/$stats->inviate*10000)/100)}}"
                                    data-width="100" data-height="100" data-fgColor="#dc3545"
                                    data-readonly="true">

                                <div class="knob-label">{{$stats->unsubscribed}} su {{$stats->inviate}}</div>
                            </div>
                        </div>
                        @if($stats->unsubscribed)
                            <div class="card-footer text-center">
                                <a href="{{url('newsletters/'.$newsletter->id.'/reports/unsubscribed')}}" class="btn btn-sm btn-danger">Vedi chi si è cancellato</a>
                            </div>
                        @endif
                    </div>
                </div>

                @if($clicks)

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Clicks</h3>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    @foreach($clicks as $click)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <a href="{{url('newsletters/'.$newsletter->id.'/reports/clicked')}}" target="_BLANK">{{$click->url}}</a>
                                            <span class="badge badge-primary badge-pill">{{$click->total}}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="card-footer text-center">
                                <a href="{{url('newsletters/'.$newsletter->id.'/reports/clicked')}}" class="btn btn-sm btn-success">Vedi chi e cosa è stato cliccato</a>
                            </div>
                        </div>
                    </div>

                @endif



            </div>
        </div>
    </div>

@stop

@section('scripts')
    <script src="{{asset('plugins/jquery-knob/jquery.knob.min.js')}}"></script>
    <script>
        $("#table").DataTable(window.tableOptions);


        $('.knob').knob({

            format: function(value) {
               return value + '%';
            },
          draw: function () {
            if (this.$.data('skin') == 'tron') {

              var a = this.angle(this.cv),
              sa  = this.startAngle,
              sat = this.startAngle,
              ea,eat = sat + a,r = true

              this.g.lineWidth = this.lineWidth

              this.o.cursor
              && (sat = eat - 0.3)
              && (eat = eat + 0.3)

              if (this.o.displayPrevious) {
                ea = this.startAngle + this.angle(this.value)
                this.o.cursor
                && (sa = ea - 0.3)
                && (ea = ea + 0.3)
                this.g.beginPath()
                this.g.strokeStyle = this.previousColor
                this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false)
                this.g.stroke()
              }

              this.g.beginPath()
              this.g.strokeStyle = r ? this.o.fgColor : this.fgColor
              this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false)
              this.g.stroke()

              this.g.lineWidth = 2
              this.g.beginPath()
              this.g.strokeStyle = this.o.fgColor
              this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false)
              this.g.stroke()

              return false
            }
          }
        })

    </script>
@stop
