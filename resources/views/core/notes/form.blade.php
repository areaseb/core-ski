@php
    $hidefile = '';
    if(request('type'))
    {
        $hidefile = 'd-none';
    }
@endphp

<div class="row">
    {!!Form::hidden('user_id',$user->id)!!}
    {!!Form::hidden('company_id', isset($company) ? $company->id : request('company_id') )!!}
    {!!Form::hidden('type', request('type') )!!}
    <div class="col-12">
        <div class="form-group">
            <label>Nota</label>
            {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'required']) !!}
        </div>
    </div>
    <div class="col-12 {{$hidefile}}">
        <div class="form-group">
            <div class="input-group">
                <div class="custom-file">
                    <input name="filename" type="file" class="custom-file-input" id="uploadFile" lang="it">
                    @if(isset($note))
                        <label class="custom-file-label" for="uploadFile" data-browse="Cambia">{{$note->filename}}</label>
                    @else
                        <label class="custom-file-label" for="uploadFile" data-browse="Cerca">Seleziona File</label>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>


<script>
    document.querySelector('.custom-file-input').addEventListener('change', function (e) {
        console.log(document.getElementById("uploadFile").files[0]);
        var name = document.getElementById("uploadFile").files[0].name;
        var nextSibling = e.target.nextElementSibling
        nextSibling.innerText = name
    })
</script>
