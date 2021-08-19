<table>
    <thead>
    <tr>
        <th>Month</th>
        <th>Technique</th>
         <th>Compliance</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $value)
        <tr>
            <td>{{ $value[0] }}</td>
            <td>{{ $value[1] }}</td>
            <td>{{ $value[2] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
 
