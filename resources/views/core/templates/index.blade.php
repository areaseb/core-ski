@extends('areaseb::layouts.app')

@include('areaseb::layouts.elements.title', ['title' => 'Templates'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary-light">
                    <h3 class="card-title">Templates</h3>
                    @can('templates.write')
                        <div class="card-tools">
                            <a href="{{url('create-template-builder')}}" class="btn btn-sm btn-primary">Nuovo Template</a>
                        </div>
                    @endcan

                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Modificato</th>
                                    <th>Creato</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                    <tr id="row-{{$template->id}}" data-model="{{$template->class}}" data-id="{{$template->id}}">
                                        <td class="editable" data-field="nome">{{$template->nome}}</td>
                                        <td data-sort="{{$template->updated_at->timestamp}}">{{$template->updated_at->format('d/m/Y')}}</td>
                                        <td data-sort="{{$template->created_at->timestamp}}">{{$template->created_at->format('d/m/Y')}}</td>
                                        <td class="pl-2">
                                            {!! Form::open(['method' => 'delete', 'url' => $template->url, 'id' => "form-".$template->id]) !!}
                                                <a href="{{$template->url}}" target="_BLANK" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-eye"></i></a>
                                                @if($template->nome != 'Default')
                                                    @can('templates.write')
                                                        <a href="{{url('edit-template-builder/'.$template->id)}}" class="btn btn-warning btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                                        <a href="#" class="btn btn-secondary btn-icon btn-sm btn-clone" data-id="{{$template->id}}" title="clona/duplica"><i class="fa fa-clone"></i></a>



                                                    @endcan
                                                    @can('templates.delete')
                                                        <button type="submit" id="{{$template->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                                    @endcan
                                                @endif
                                            {!! Form::close() !!}
                                            {!! Form::open(['url' => url('templates/'.$template->id.'/duplicate'), 'id' => "dform-".$template->id, 'class' => 'd-none']) !!}
                                                <button type="submit" class="d-none">DUPLICATE</button>
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
<script>
    $('a.btn-clone').on('click', function(e){
        e.preventDefault();
        let f = $('form#dform-'+$(this).attr('data-id'))[0];

        f.submit();
    })
</script>
@endpush
