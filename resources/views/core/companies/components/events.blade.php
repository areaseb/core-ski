@if($company->events()->exists())
    <div class="row">
        <table id="tableE" class="table table-sm table-bordered table-striped" style="width:100%;">
            <thead>
                <tr>
                    <th>Titolo</th>
                    <th>Da</th>
                    <th>A</th>
                    <th>Creato</th>
                    <th data-orderable="false"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($company->events()->orderBy('starts_at', 'DESC')->groupBy(\DB::raw('DATE(created_at)'))->get() as $event)

                    @php
                        $bg = '#ddd';
                        if($event->ends_at->gte(\Carbon\Carbon::now()))
                        {
                            $bg = 'transparent';
                        }
                    @endphp

                    <tr style="background:{{$bg}}">
                        <td>{{$event->title}}</td>
                        <td data-ordinable="{{$event->starts_at->timestamp}}"><small>{{$event->starts_at->format('d/m/Y')}}</small> <b>{{$event->starts_at->format('H:i')}}</b></td>
                        <td data-ordinable="{{$event->ends_at->timestamp}}"><small>{{$event->ends_at->format('d/m/Y')}}</small> <b>{{$event->ends_at->format('H:i')}}</b></td>
                        <td data-ordinable="{{$event->created_at->timestamp}}"><small>{{$event->created_at->format('d/m/Y')}}</small></td>
                        <td data-orderable="false">

                            {!! Form::open(['method' => 'delete', 'url' => $event->url, 'id' => "form-".$event->id]) !!}
                                <a href="#" data-eventId="{{$event->id}}" class="btn btn-primary btn-icon btn-sm showEvent"><i class="fa fa-eye"></i> </a>
                                <button type="submit" id="{{$event->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i> </button>
                            {!! Form::close() !!}

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('areaseb::core.events.show-event')

@endif

@push('scripts')
<script>
    $("#tableE").DataTable(window.tableOptions);
</script>
@endpush
