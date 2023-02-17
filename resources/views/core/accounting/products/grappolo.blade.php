<div class="row">
    <div class="col-md-6">

            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title">Prodotti da aggiungere</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-sm table-xs tlav">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th data-sortable="false"></th>
                                    <th data-sortable="false"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $id => $text)
                                    <tr class="row-input-{{$id}}">
                                        <td id="prod-code-{{$id}}">{{$text}}</td>
                                        <td>
                                            <div class="form-group" style="width:100px; margin-top:5px;margin-bottom:5px;">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <a href="#" class="subP input-group-text input-group-text-sm bg-danger"><i class="fas fa-minus"></i></a>
                                                    </div>
                                                    <input type="number" class="form-control form-control-sm" min="1" value="1" id="prod-{{$id}}">
                                                    <div class="input-group-append">
                                                        <a href="#" class="addP input-group-text input-group-text-sm bg-primary"><i class="fas fa-plus"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-success addToCart" data-prod="{{$id}}"><i class="fas fa-cart-plus"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


    </div>

    <div class="col-md-6">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">Prodotti aggiunti</h3>
            </div>
            <div class="card-body">

                <div class="row addingProduct">

                    @if(isset($product))
                        @if(!is_null($product->children))
                            @foreach($product->children as $child)
                                <div class="col-12">
                                    <div class="form-group">
                                        @if($loop->index == 0)
                                            <label>Input*</label>
                                        @endif
                                        <div class="input-group">
                                            {!! Form::select('input[id][]', $products, $child['id'], ['class' => 'form-control']) !!}
                                            <div class="input-group-append" style="width:100px">
                                                {!!Form::number('input[qta][]', $child['qta'], ['class' => 'form-control', 'placeholder' => 'qta'])!!}
                                                <a href="#" class="btn btn-danger remove" title="elimina"><i class="fas fa-times"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endif
                </div>

            </div>
        </div>
    </div>


</div>

@push('scripts')
<script>
let topt = window.tableOptions;
topt.pageLength = 10;
$("#table").DataTable(topt);

let data = {!!json_encode($select)!!};
data.unshift({text:"", id:""});


$('select.input').select2({placeholder: "Input per la lavorazione",width:'70%'});

$('.addingProduct').on('click', 'a.remove', function(e){
    e.preventDefault();
    $(this).parent('div').parent('div').parent('div').parent('div').remove();
    console.log('clicked');
})

function randomInteger(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

$('.tlav').on('click', 'a.addP', function(e){
    e.preventDefault();
    let val = $(this).parent('div').siblings('input').val();
    $(this).parent('div').siblings('input').val(parseInt(val)+1);
});

$('.tlav').on('click', 'a.subP',function(e){
    e.preventDefault();
    let val = $(this).parent('div').siblings('input').val();
    if(parseInt(val)>0)
    {
        $(this).parent('div').siblings('input').val(parseInt(val)-1);
    }
});

$('.tlav').on('click', 'a.addToCart', function(e){
    e.preventDefault();
    let prodId = $(this).attr('data-prod');
    let code = $('td#prod-code-'+prodId).text();
    let name = $('td#prod-nome-'+prodId).text();
    let qta = $('input#prod-'+prodId).val();
    if(parseInt(qta) > 0)
    {
        let htl ='<div class="col-12">'+
                        '<div class="form-group">'+
                            '<div class="input-group">'+
                                '<select class="form-control" id="regex" name="input[id][]"></select>'+
                                '<div class="input-group-append" style="width:30%">'+
                                    '<input class="form-control" placeholder="qta" name="input[qta][]" type="number" value="'+qta+'">'+
                                    '<a href="#" class="btn btn-danger remove" title="elimina"><i class="fas fa-times"></i></a>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>';
        let newInput = "input-"+randomInteger(1, 1000);
        let newHtml = htl.replace("regex", newInput);
        $('.addingProduct').append(newHtml);
        $('#'+newInput ).select2();
        $('#'+newInput ).select2({data:data});
        $('#'+newInput ).val(prodId).trigger('change');
        console.log($(this).parent('td').parent('tr').remove())
    }
})

</script>
@endpush
