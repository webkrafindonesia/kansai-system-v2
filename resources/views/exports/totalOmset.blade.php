<html>
<head>
    <meta charset="UTF-8">
    <title>Export Total Omset</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Customer</th>
                <th>Total Omset</th>
            </tr>
        </thead>
        <tbody>
        @php
            $index = 1;
        @endphp
        @foreach($salesOrders as $salesOrder)
            <tr>
                <td>{{ $index }}</td>
                <td>{{ $salesOrder->customer->name }}</td>
                <td>{{ $salesOrder->omset }}</td>
            </tr>
            @php
                $index++;
            @endphp
        @endforeach
        </tbody>
    </table>
</body>
</html>
