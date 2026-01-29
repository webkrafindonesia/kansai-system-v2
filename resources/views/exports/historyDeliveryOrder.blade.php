<html>
<head>
    <meta charset="UTF-8">
    <title>Export History Delivery Order</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Tgl. Pengiriman</th>
                <th>Customer</th>
                <th>No. Sales Order</th>
                <th>Dari Gudang</th>
                <th>Tgl. Proses</th>
                <th>Diproses Oleh</th>
                <th>Kode</th>
                <th>Nama Produk</th>
                <th>Qty</th>
                <th>Uom</th>
            </tr>
        </thead>
        <tbody>
        @php
            $index = 1;
        @endphp
        @foreach($deliveryOrders as $deliveryOrder)
            @foreach($deliveryOrder->items as $item)
            <tr>
                <td>{{ $index }}</td>
                <td>{{ $deliveryOrder->delivery_date }}</td>
                <td>{{ $deliveryOrder->salesOrder->customer->name }}</td>
                <td>{{ $deliveryOrder->salesOrder->salesorder_no }}</td>
                <td>{{ $deliveryOrder->warehouse->name }}</td>
                <td>{{ $deliveryOrder->processed_at }}</td>
                <td>{{ $deliveryOrder->processed_by }}</td>
                <td>{{ $item->product->code }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->uom }}</td>
            </tr>
            @php
                $index++;
            @endphp
            @endforeach
        @endforeach
        </tbody>
    </table>
</body>
</html>
