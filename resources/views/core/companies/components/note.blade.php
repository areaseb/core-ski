<div class="row">
    @if($company->s1)
        <div class="col col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h5 class="mb-0">Sconto</h5>
                    <p class="mb-0">{{$company->sconto}}% <span style="font-size:80%">({{$company->s1}}% + {{$company->s2}}% + {{$company->s3}}%)</span></p>
                </div>
            </div>
        </div>
    @endif

    @if($company->exemption_id)
        @php
            $ex = Areaseb\Core\Models\Exemption::find($company->exemption_id);
        @endphp
        <div class="col col-md-4">
            <div class="small-box bg-warning" style="min-height:88px;">
                <div class="inner">
                    <h6 class="mb-0">Esenzione {{$ex->perc}}%</h6>
                    <p class="mb-0"></p>
                    <p class="mb-0" style="font-size:80%;">{{$ex->nome}}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="col col-md-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Note</h6>
            </div>
            <div class="card-body">

                @include('areaseb::core.notes.index')

                <div class="row">
                    <div class="col"></div>
                    <div class="col"><a href="{{route('notes.create')}}?company_id={{$company->id}}" data-title="Aggiungi nota" class="btn btn-primary btn-block btn-modal"> Aggiungi Nota</a></div>
                    <div class="col"></div>
                </div>


                {{-- @include('areaseb::core.notes.create') --}}

            </div>
        </div>
    </div>

</div>
