<div class="col">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recapiti</h3>
        </div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex justify-content-between align-items-center"><b>Ragione Sociale</b> <span><a href="{{route('companies.show',$invoice->company->id )}}">{{$invoice->company->rag_soc}}</a></span></li>
              <li class="list-group-item d-flex justify-content-between align-items-center"><b>Email</b> <span>{{$invoice->company->email}}</span></li>
              @if(!is_null($invoice->company->phone) || !is_null($invoice->company->mobile)) <li class="list-group-item d-flex justify-content-between align-items-center"><b>Telefono</b> <span>{{$invoice->company->phone ?? $invoice->company->mobile}}</span></li> @endif
              @if($invoice->company->address) <li class="list-group-item d-flex justify-content-between align-items-center"><b>Indirizzo</b> <span>{{$invoice->company->address}}</span></li> @endif
              @if($invoice->company->province) <li class="list-group-item d-flex justify-content-between align-items-center"><b>Provincia</b> <span>{{$invoice->company->province}}</span></li> @endif
              @if($invoice->company->contacts()->exists())
                  @php
                    $contact = $invoice->company->contacts()->first();
                  @endphp
                  <li class="list-group-item d-flex justify-content-between align-items-center"><b>Contatto</b> <span>{{$contact->fullname}}</span></li>
              @endif
            </ul>
        </div>
    </div>
</div>
