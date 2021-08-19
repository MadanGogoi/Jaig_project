<table>
    <thead>
    <tr>
        <th>Month/Year</th>
         
         <th>No Of Attacks</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $value)
        <tr>
            <td>{{ $value[0] }}</td>
            <td>{{ $value[1] }}</td>
            
        </tr>
    @endforeach
    </tbody>
</table>
 
