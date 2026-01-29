<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $salesOrders[0]->sales->name }}</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>Tgl. Invoice</th>
                <th>FakturID</th>
                <th>StockID</th>
                <th>ItemDesc</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Harga</th>
                <th>Disc</th>
                <th>GrandTotal</th>
                <th>CompanyName</th>
            </tr>
        </thead>
        <tbody>
        @foreach($salesOrders as $salesOrder)
        @php
            $total_omset = 0;
        @endphp
            @foreach($salesOrder->items as $item)
            <tr>
                <td>{{ $salesOrder->invoice_date->format('Y-m-d') }}</td>
                <td>{{ $salesOrder->invoice_no }}</td>
                <td>{{ $item->product->code }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->uom }}</td>
                <td>{{ $item->master_price }}</td>
                <td>{{ $salesOrder->discount_sales }}%</td>
                <td>{{ (100 - $salesOrder->discount_sales)/100 * $item->master_total_price }}</td>
                <td>{{ $salesOrder->customer->name }}</td>
            </tr>
            @php
                $total_omset += (100 - $salesOrder->discount_sales)/100 * $item->master_total_price;
            @endphp
            @if ($loop->iteration == $loop->count)
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td bgcolor="yellow">{{ $total_omset }}</td>
                <td></td>
            </tr>
            @endif
            @endforeach
        @endforeach
        </tbody>
    </table>
</body>
</html>
