<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sales Order #{{ $salesOrder->salesorder_no }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            /* padding: 40px; */
            color: #333;
            font-size: 12px;
        }

        .header {
            text-align: left;
            border-bottom: 2px solid #141311;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .header img {
            height: 100px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
        }

        .company-info {
            margin-top: 10px;
            font-size: 14px;
            color: #555;
        }

        .meta, .client-info {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .meta div, .client-info div {
            width: 48%;
        }

        .meta strong, .client-info strong {
            display: block;
            margin-bottom: 5px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            text-align: center;
            border: 1px solid #ddd;
        }

        .table td {
            border: 1px solid #ddd;
            padding: 0px 5px;
        }

        .status {
            margin-top: 30px;
            font-size: 18px;
            font-weight: bold;
            color: green;
            text-align: center;
        }

        footer{
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        .footer-note {
            margin-top: 50px;
            font-size: 12px;
            text-align: center;
            color: #666;
            border-top: 1px dashed #ccc;
            padding-top: 15px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <table width="100%">
                <tr>
                    <td width="50%">
                        <span class="title">BREAKDOWN</span>
                    </td>
                    <td align="right">
                        {{ date('l, j F Y')}}
                    </td>
                </tr>
                <tr>
                    <td>
                        No. Sales Order : {{ $salesOrder->salesorder_no }}
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        Customer : <strong>{{$salesOrder->customer->name}}</strong>
                    </td>
                    <td></td>
                </tr>
            </table>
        </div>

        <h2>Breakdown per Produk</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Produk</th>
                    <th>Qty Order</th>
                    <th>Qty Ready</th>
                    <th>Qty Tambah</th>
                    <th>Satuan</th>
                    <th>Raw Material</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $qtyToReplenishTotal = 0;
                @endphp
                @foreach($salesOrder->items as $key => $salesOrderItem)
                <tr>
                    <td align="center">{{ $key + 1 }}.</td>
                    <td>
                        {{-- @if(!empty($salesOrderItem->product->productCategory))
                        {{ $salesOrderItem->product->productCategory->name.' - '.$salesOrderItem->product->name }}
                        @else
                        {{ 'Custom - '.$salesOrderItem->product->name }}
                        @endif --}}
                        {{ $salesOrderItem->product->code.' - '.$salesOrderItem->product->name }}
                    </td>
                    <td align="center">{{ $salesOrderItem->qty }}</td>
                    <td align="center">{{ $salesOrderItem->stocks->sum('qty') ?? 0 }}</td>
                    @php
                        $qtyToReplenish = $salesOrderItem->qty - ($salesOrderItem->stocks->sum('qty') ?? 0);
                        $qtyToReplenishTotal += ($qtyToReplenish > 0) ? $qtyToReplenish : 0;
                    @endphp
                    <td align="center">{{ ($qtyToReplenish > 0) ? $qtyToReplenish : '' }}</td>
                    <td align="center">{{ $salesOrderItem->uom }}</td>
                    <td align="center">
                        @foreach ($salesOrderItem->breakdowns as $breakdown)
                            {{ $breakdown->product->productCategory->name.' - '.$breakdown->product->name }} : {{ $breakdown->qty }} {{ $breakdown->uom }} <br>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td align="right" colspan="2"><strong>Total Item</strong></td>
                    <td align="center"><strong>{{ $salesOrder->items->sum('qty') }}</strong></td>
                    <td></td>
                    <td align="center"><strong>{{ $qtyToReplenishTotal }}</strong></td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        {{-- <div class="page-break"></div>
        <h2>Breakdown per Raw Material (to Replenish)</h2>
        @if($breakdowns->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Raw Material</th>
                    <th>Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($breakdowns as $key => $breakdown)
                <tr>
                    <td align="center">{{ $key + 1 }}.</td>
                    <td>{{ $breakdown->product->productCategory->name.' - '.$breakdown->product->name }}</td>
                    <td align="center">{{ $breakdown->total_qty }} {{ $breakdown->uom }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div>
        Tidak ada breakdown per Raw Material.
        </div>
        @endif --}}

        <footer>
            <div class="footer-note">
                Dokumen ini dicetak oleh sistem dan dianggap sah tanpa tanda tangan.
            </div>
        </footer>
    </div>

</body>
</html>
