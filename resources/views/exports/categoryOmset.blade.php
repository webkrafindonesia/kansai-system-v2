<html>
<head>
    <meta charset="UTF-8">
    <title>Export Category Omset</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Kategori</th>
                <th>Qty</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->omset }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
