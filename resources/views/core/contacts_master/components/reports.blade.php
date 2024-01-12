@if($contact->reports()->exists())
    <div class="row">
        <div class="col-md-6">
            <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
        </div>
        <div class="col-md-6">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Data</th>
                        <th>Aperta</th>
                        <th>Clicks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contact->reports as $report)
                        <tr>
                            <td><small>{{$report->newsletter->nome}}</small></td>
                            <td><small>{{$report->created_at->format('d/m/Y H:i')}}</small></td>
                            @if($report->opened)
                                <td><small>Sì</small></td>
                            @else
                                <td><small>No</small></td>
                            @endif
                            @if($report->clicks)
                                <td><small>Sì</small></td>
                            @else
                                <td><small>No</small></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endif

@if($contact->reports()->exists())
    @push('scripts')

        <script src="{{asset('plugins/chart.js/Chart.min.js')}}"></script>
        <script>
            var cd = JSON.parse('{!!json_encode($contact->newsletter_stats)!!}');
            var pieData = {
                  labels: [
                      'Click',
                      'Aperte',
                      'Non Aperte',
                  ],
                  datasets: [
                    {
                      data: [cd.clicks,cd.aperte,(cd.inviate - cd.aperte)],
                      backgroundColor : ['#00c0ef', '#3c8dbc', '#f36767'],
                    }
                  ]
                }
            var pieChartCanvas = $('#pieChart').get(0).getContext('2d')
            var pieOptions = {maintainAspectRatio : false,responsive : true,}
            var pieChart = new Chart(pieChartCanvas, {
              type: 'pie',
              data: pieData,
              options: pieOptions
            })
        </script>

    @endpush
@endif
