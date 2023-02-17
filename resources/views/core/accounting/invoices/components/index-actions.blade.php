<div class="btn-group" role="group">
    <a href="{{$invoice->url}}" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>

    @if(!$invoice->is_consegnata)

        <button id="create" type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" data-display="static"></button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="create">
            @can('invoices.write')
                <a href="{{$invoice->url}}/edit" class="dropdown-item"><i class="fa fa-edit"></i> Modifica</a>
                @if(config('core.modules')['fe'])
                    <a href="#" class="dropdown-item sendFe" data-id="{{$invoice->id}}"><i class="fa fa-cloud-upload-alt"></i> Invia FE</a>
                    {!!Form::open(['url' => url('api/invoices/'.$invoice->id.'/send-fe'), 'class' => 'd-none', 'id' => 'sendFe'.$invoice->id])!!}
                        <button type="submit"></button>
                    {!!Form::close()!!}
                @endif

                @if(Areaseb\Core\Models\Setting::feIsSet())
                    <a href="{{route('invoices.export', $invoice->id)}}" class="dropdown-item exportFe" data-id="{{$invoice->id}}"><i class="fas fa-file-invoice"></i> scarica Xml</a>
                    {!!Form::open(['url' => route('invoices.export', $invoice->id), 'class' => 'd-none', 'id' => 'exportFe'.$invoice->id, 'method'=>'GET'])!!}
                        <button type="submit"></button>
                    {!!Form::close()!!}
                @endif


            @endcan


                <a href="{{url('pdf/invoices/'.$invoice->id)}}" target="_blank" class="dropdown-item"><i class="fa fa-file-pdf"></i> PDF</a>

            @can('invoices.write')
                @if($invoice->company->email)
                    <a href="#" class="dropdown-item sendToClient" data-id="{{$invoice->id}}" title="invia un'email al cliente con in allegato la fattura in pdf"><i class="fa fa-envelope"></i> Invia PDF</a>
                @endif
                <a href="#" data-id="{{$invoice->id}}" class="dropdown-item duplicate"><i class="far fa-clone"></i> Duplica</a>
                {!! Form::open(['url' => url('api/invoices/'.$invoice->id.'/duplicate'), 'id' => "dform-".$invoice->id, 'class' => 'd-none']) !!}
                    <button type="submit">DUPLICATE</button>
                {!! Form::close() !!}

            @endcan
            @can('invoices.delete')
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item delete" data-id="{{$invoice->id}}"><i class="fa fa-trash"></i> Elimina</a>
            @endcan
        </div>
        @can('invoices.delete')
            {!! Form::open(['method' => 'delete', 'url' => $invoice->url, 'id' => "form-".$invoice->id, 'class' => 'd-none']) !!}
                <button type="submit" id="{{$invoice->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
            {!! Form::close() !!}
        @endcan

    @else

        <button id="edit" type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" data-display="static"></button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="edit">
            @can('invoices.write')
                @if($invoice->has_item_default)
                    <a href="{{$invoice->url}}/edit" class="dropdown-item"><i class="fa fa-edit"></i> Modifica</a>
                @endif
            @endcan

            @if(Areaseb\Core\Models\Setting::feIsSet())
                <a href="{{route('invoices.export', $invoice->id)}}" class="dropdown-item exportFe" data-id="{{$invoice->id}}"><i class="fas fa-file-invoice"></i> scarica Xml</a>
                {!!Form::open(['url' => route('invoices.export', $invoice->id), 'class' => 'd-none', 'id' => 'exportFe'.$invoice->id, 'method'=>'GET'])!!}
                    <button type="submit"></button>
                {!!Form::close()!!}
            @endif

            @if(!$invoice->saldato)
                <a href="#" data-id="{{$invoice->id}}" class="dropdown-item notice"><i class="fas fa-exclamation-triangle"></i> Sollecita</a>
                {!! Form::open(['url' => route('invoices.sendNotice', $invoice->id), 'id' => "noticeForm-".$invoice->id, 'class' => 'd-none']) !!}
                    <button type="submit">DUPLICATE</button>
                {!! Form::close() !!}
            @endif


                <a href="{{url('pdf/invoices/'.$invoice->id)}}" target="_blank" class="dropdown-item"><i class="far fa-file-pdf"></i> scarica Pdf</a>

            @can('invoices.write')
                @if($invoice->company->email)
                    <a href="#" class="dropdown-item sendToClient" data-id="{{$invoice->id}}" title="invia un'email al cliente con in allegato la fattura in pdf"><i class="fa fa-envelope"></i> Invia</a>
                @endif
                <a href="#" data-id="{{$invoice->id}}" class="dropdown-item duplicate"><i class="far fa-clone"></i> Duplica</a>
                {!! Form::open(['url' => url('api/invoices/'.$invoice->id.'/duplicate'), 'id' => "dform-".$invoice->id, 'class' => 'd-none']) !!}
                    <button type="submit">DUPLICATE</button>
                {!! Form::close() !!}
            @endcan
        </div>

    @endif

</div>
