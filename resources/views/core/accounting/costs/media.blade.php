@extends('areaseb::layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{route('costs.index')}}">Acquisti</a></li>
@stop

@section('css')
<link rel="stylesheet" href="{{asset('plugins/dropzone/min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/popup/min.css')}}">
@stop

@include('areaseb::layouts.elements.title', ['title' => 'Aggiungi media'])


@section('content')

    <div class="col-10 offset-1 mt-5">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Aggiungi media a: {{$model->nome}}</h3>
            </div>
            <div class="card-body">
                <form action="{{route('media.add')}}" class="dropzone" id="dropzoneForm">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="fallback">
                            <input name="file" type="file" multiple />
                            <input name="mediable_type" type="hidden" value="{{$model->class}}" />
                            <input name="mediable_id" type="hidden" value="{{$model->id}}" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

	<div class="clearfix"></div>

    @include('areaseb::components.media.images')





    @if($model->media()->where('mime', 'doc')->exists())
        <div class="card card-outline card-warning mt-5">
            <div class="card-header">FILES</div>
            <table class="table table-sm table-bordered doc-table mb-0 pb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">descrizione file</th>
                        <th class="text-center">preview</th>
                        <th class="text-center">size</th>
                        <th class="text-center"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($model->media()->where('mime','doc')->get() as $file)

    {{-- @if( ( strpos($file->description, 'Fattura XML') !== false) && (strpos($file->description, 'Fattura PDF') !== false )) --}}

                        <tr>
                            <td class="align-middle text-center" style="min-width: 50px;">
                                {{$loop->index+1}}
                            </td>
                            <td class="align-middle text-center">
                                <form method="POST" action="{{url('api/media/update')}}" class="col-sm-12 form-description">
                                    {{csrf_field()}}
                                    <input type="hidden" name="id" value="{{$file->id}}">
                                    <div class="input-group">
                                        <input type="text" name="description" class="form-control" value="{{$file->description}}" />
                                        <button class="btn btn-primary tbr0" id="{{$file->id}}"><i class="fa fa-save"></i></button>
                                    </div>
                                </form>
                            </td>
                            <td class="align-middle text-center">

                                @if(strpos($file->filename, '.xml') !== false)
                                    <a class="btn btn-sm btn-primary" target="_BLANK" href="{{ asset('storage/fe/ricevute/'.$file->filename) }}" >
                                        <i class="fa fa-disk"></i>{{$file->filename}}
                                    </a>
                                @elseif(strpos($file->filename, '/') !== false)
                                    <a class="btn btn-sm btn-primary" target="_BLANK" href="{{ asset('storage/fe/pdf/ricevute/'.$file->filename) }}" >
                                        <i class="fa fa-disk"></i>{{$file->filename}}
                                    </a>
                                @else
                                    <a class="btn btn-sm btn-primary" target="_BLANK" href="{{$file->doc}}" >
                                        <i class="fa fa-disk"></i>{{$file->filename}}
                                    </a>
                                @endif


                            </td>
                            <td class="align-middle text-center">
                                <small>{{$file->kb}}</small>
                            </td>

                            <td class="align-middle text-center">
                                <form method="POST" action="{{url('api/media/delete')}}">
                                    {{csrf_field()}}
                                    {{method_field('DELETE')}}
                                    <input type="hidden" name="id" value="{{$file->id}}">
                                    <button class="btn btn-sm btn-danger" type="submit"><i class="fa fa-trash" style="width: 20px;height: 25px;padding-top: 4px;"></i> </button>
                                </form>
                            </td>
                        </tr>
{{-- @endif --}}
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif












    <div class="mx-auto mt-5 text-center">
        <a class="btn btn-outline-primary" href="{{url($model->directory)}}"><i class="fa fa-arrow-left"></i> Torna indietro</a>
    </div>

@stop

@include('areaseb::components.media.script')
