<html>
<head>
    <meta charset="UTF-8">
    <title>Export History Purchase</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Raw Material</th>
                <th>Qty Penggunaan</th>
                <th>Satuan</th>
                <th>Sisa Stok</th>
                <th>Total Harga Stok</th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ abs($item->qty) }}</td>
                <td>{{ $item->uom }}</td>
                <td>{{ $item->stock->qty }}</td>
                <td>{{ $item->stock->qty * $item->avg_price }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
