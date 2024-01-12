
<div class="row">
    <div class="col">
		<b>Note</b><br><br>
        <form action="/contacts/note/update" method="post">
            @csrf
            <input type="text" name="contact_id" value="{{$contact->id}}" hidden>
            <input type="text" name="prev" value="contacts" hidden>
            <textarea  name="note" rows="4" cols="50" class="form-control" >{{$contact->note}}</textarea>
            <br>
            <button type="submit" class="btn btn-block btn-success btn-lg col-md-3" style="float:right" >Salva</button>
        </form>
	</div>
	<div class="col">
		<b>Note segreteria</b><br><br>
        <form action="/contacts/note/update" method="post">
            @csrf
            <input type="text" name="contact_id" value="{{$contact->id}}" hidden>
            <input type="text" name="prev" value="contacts" hidden>
            <textarea  name="note_segreteria" rows="4" cols="50" class="form-control" >{{$contact->note_segreteria}}</textarea>
            <br>
            <button type="submit" class="btn btn-block btn-success btn-lg col-md-3" style="float:right" >Salva</button>
        </form>
	</div>
</div>

