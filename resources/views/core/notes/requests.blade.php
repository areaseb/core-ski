@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Richieste'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">
                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-php">
                            <thead>
                                <tr>
                                    <th data-field="rag_soc" data-order="asc">Ragione Sociale <i class="fas fa-sort"></i></th>
                                    <th>Contatti</th>
                                    <th data-field="created_at" data-order="asc" style="width:83px;">Data <i class="fas fa-sort"></i></th>
                                    <th>Messaggio</th>
                                    <th>Origine</th>
                                    <th data-orderable="false" style="width:230px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)

                                    @php
                                        $company = $request->company;
                                        $contact = null;
                                        $wan = null;
                                        if($company->contacts)
                                        {
                                            $contact = $company->contacts->first();
                                            if($contact)
                                            {
                                                $wan = $contact->int_number;
                                            }

                                        }
                                        $wan = $wan ?? $company->int_mobile;
                                    @endphp

                                    <tr id="row-{{$request->id}}" @if($request->is_new) class="table-info" @endif>
                                        <td>
                                            {{$company->rag_soc}}
                                            @if($contact)
                                                @if(strtolower($contact->fullname) != strtolower($company->rag_soc))
                                                    <br><i>{{$contact->fullname}}</i>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{$company->email}}</small><br>{{$wan}}
                                        </td>
                                        <td class="text-center">
                                            {{$request->created_at->format('d/m/Y')}}<br><small>ore {{$request->created_at->format('H:i')}}</small>
                                        </td>
                                        <td class="p-3">
                                            <span class="txt truncate">{!!$request->description!!}</span>
                                        </td>
                                        <td class="text-center">
                                            {!!$company->origin!!}
                                        </td>
                                        @can('companies.write')
                                            <td class="pl-2 requestAction">

                                                {!! Form::open(['method' => 'delete', 'url' => route('notes.destroyAjax', $request->id), 'id' => "form-".$request->id]) !!}

                                                    @includeIf('deals::quick-btn.link-company')
                                                    @includeIf('killerquote::quick-btn.link-company')

                                                    <a data-type="email" href="mailto:{{$company->email}}" title="invia email" target="_BLANK" class="btn btn-orange btn-icon btn-sm"><i class="fas fa-at" style="color:#fff;"></i></a>

                                                    <button id="{{$request->id}}" type="submit" class="btn btn-danger btn-sm delete"><i class="fa fa-trash"></i></button>

                                                    @if(!is_null($wan))
                                                        <a data-type="wa" target="_BLANK" href="https://web.whatsapp.com/send?phone={{$wan}}&text=Buongiorno {{is_null($contact) ? $company->rag_soc : $contact->fullname}}{{config('core.wa') ?? ''}}" class="btn btn-sm btn-success"><i class="fab fa-whatsapp"></i></a>
                                                    @endif

                                                {!! Form::close() !!}
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <p class="text-left text-muted">{{$requests->count()}} of {{ $requests->total() }} richieste</p>
                    {{ $requests->appends(request()->input())->links() }}
                </div>

            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
 $('[data-toggle="tooltip"]').tooltip();


 @if(config('core.LTP'))
     $('a.waClicked').on('click', function(e){
         e.preventDefault();
         let redirect = $(this).attr('href');
         let data = {};
         data = {
             model: 'Company',
             id: $(this).attr('data-id'),
             _token: "{{csrf_token()}}",
             field: 'client_id',
             value: '1'
         }
         $.post(baseURL+'update-field', data).done(function( response ) {
             console.log(response);
             window.open(redirect, '_blank').focus();
         });
     });
 @endif

$('.requestAction a').on('click', function(e){

    let request_id = $(this).closest('form').attr('id').replace('form-', '');
    let type = $(this).attr('data-type');
    let href = $(this).attr('href');
    let data = {};
    data = {
        model: 'Note',
        id: request_id,
        _token: "{{csrf_token()}}",
        field: 'updated_at',
        value: "{{Carbon\Carbon::now()->format('Y-m-d H:i:s')}}"
    }
    $.post(baseURL+'update-field', data).done(function( response ) {
        if((type == 'wa') || (type == 'email'))
        {
            window.open(href, '_blank').focus();
        }
        else
        {
            window.location.href = href;
        }

    });
});


 $('[data-toggle="tooltip"]').tooltip();

</script>


@stop
