<table>
    <thead>
    <tr>
        <th>Nominativo</th>
        <th>Colore</th>
        <th>Sede</th>
        <th>Ordine</th>
        <th>Attivo</th>
        <th>Email</th>
        <th>Cellulare</th>
    </tr>
    </thead>
    <tbody>
    @foreach($masters as $master)

        <tr>
            <td>{{$master->fullname}}</td>
            <td>{{$master->dataMaster($master->id)->color}}</td>
            <td>{{$master->branchName($master->id)}}</td>
            <td>{{$master->dataMaster($master->id)->ordine}}</td>
            <td>{{$master->attivo == 1 ? 'Si' : 'No'}}</td>
            <td>{{$master->email}}</td>
            <td>{{$master->cellulare}}</td>
        </tr>

    @endforeach
    </tbody>
</table>
