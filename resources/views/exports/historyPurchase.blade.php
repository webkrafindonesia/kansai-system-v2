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
                <th>Qty</th>
                <th>Harga</th>
                <th>Supplier</th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->uom }}</td>
                <td>{{ $item->total_price_items }}</td>
                <td>{{ $item->name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
