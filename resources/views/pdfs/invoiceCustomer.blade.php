<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $salesOrder->invoice_no }}</title>
    <style>
        @page{
            size: 241.3mm 279.4mm portrait;
            margin: 0px;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px 130px 10px 30px;
            color: #333;
            font-size: 12px;
        }

        .header {
            text-align: left;
        }

        h1{
            margin: 0px;
            padding: 0px;
            font-size: 24px;
        }

        h3{
            margin: 0px;
            padding: 0px;
        }

        hr{
            width: 25%;
            margin: 5px 0px;
            padding: 0px;
            border-width:1px;
            text-align: left;
        }

        .header img {
            height: 100px;
        }

        .company-info {
            /* margin-top: 10px; */
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
            /* width: 100%; */
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            font-weight: bold;
            color: black;
            padding: 2px;
            text-align: center;
        }

        .table td {
            /* border: 1px solid #ddd; */
            padding: 0px;
        }

        .status {
            margin-top: 30px;
            font-size: 18px;
            font-weight: bold;
            color: green;
            text-align: center;
        }

        footer{
            /* position: absolute;
            bottom: 20px; */
            width: 90%;
        }

        .footer-note {
            margin-top: 50px;
            font-size: 12px;
            text-align: center;
            color: #666;
            border-top: 1px dashed #ccc;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <table width="100%" class="table company-info" border="0">
                <tr>
                    <td>
                        <div style="float:left">
                            <img src="data:image/jpeg;base64,{{ $logo }}" style="width:75px; height: 40px">
                        </div>
                        <div style="float:left; margin-left: 10px">
                            <strong>PT Kansai Indopart Mandiri</strong><br/>
                            Jl. Prepedan Raya No 23, RT.8/RW.9, Prepedan,<br/>
                            Jakarta Barat, 11820 (WA: 081387684102)<br/>
                        </div>
                    </td>
                    <td colspan="2" align="right">
                        <h1 style="float:right">FAKTUR JUAL / INVOICE</h1>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><br/><br/><br/><hr/></td>
                </tr>
                <tr>
                    <td width="55%"><em>{{(!is_null($salesOrder->sales_id))?$salesOrder->sales->name:''}}</em></td>
                    <td width="15%">No. Faktur</td>
                    <td>{{ $salesOrder->invoice_no }}</td>
                </tr>
                <tr valign="top">
                    <td rowspan="2">
                        <strong>{{$salesOrder->customer->name}}</strong><br>
                        {!! nl2br($salesOrder->customer->address) !!}
                    </td>
                    <td>Tanggal</td>
                    <td>{{ $salesOrder->invoice_date->format('l, j F Y') }}</td>
                </tr>
                <tr>
                    <td>Term of Payment</td>
                    <td>60 hari ({{ $salesOrder->term_of_payment->format('F Y') }})</td>
                </tr>
            </table>
        </div>

        <table class="table" width="100%">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Item Description</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesOrder->items as $item)
                <tr>
                    <td align="center">{{ $loop->iteration }}.</td>
                    <td>{{ $item->product->code.' - '.$item->product->name }}</td>
                    <td align="right">{{ numberFormat($item->qty) }} {{ $item->uom }}</td>
                    <td align="right">{{ numberFormat(moneyFormat($item->price)) }}</td>
                    <td align="right">{{ $salesOrder->discount_company }}%</td>
                    <td align="right">{{ numberFormat(moneyFormat($item->total_price * (100 - $salesOrder->discount_company)/100)) }}</td>
                </tr>
                @endforeach
                @if($salesOrder->items->count() < 15)
                    @for($i = $salesOrder->items->count(); $i < 15; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endfor
                @endif
            </tbody>
            <tfoot>
                <tr valign="top" style="border-top: 1px black solid">
                    @php
                        $total_with_discount = $salesOrder->items->sum('total_price') * (100 - $salesOrder->discount_company)/100;
                    @endphp
                    <td colspan="4" rowspan="2">
                        <strong>TERBILANG</strong> : {{ ucwords(Number::spell(moneyFormat($total_with_discount), locale: 'id')) }} Rupiah<br/>
                        PERHATIAN!!! Barang-barang yang sudah dibeli tidak dapat dikembalikan/ditukar.<br/>
                        <strong>BCA A/N WANG CHUN A/C 370 302 1111</strong>
                    </td>
                    <td align="right"><h3>TOTAL</h3></td>
                    <td align="right"><h3>Rp {{ numberFormat(moneyFormat($total_with_discount)) }}</h3></td>
                </tr>
                <tr>
                    <td align="right"><h3>Total Qty</h3></td>
                    <td align="right"><h3>{{ numberFormat($salesOrder->items->sum('qty')) }} PCS</h3></td>
                </tr>
            </tfoot>
        </table>

        <footer>
            <table class="table" width="80%">
                <tr>
                    <td width="33%" align="center">HORMAT KAMI<br/><br/><br/><br/><br/></td>
                    <td width="33%" align="center">SALES<br/><br/><br/><br/><br/></td>
                    <td width="33%" align="center">TANDA TERIMA<br/><br/><br/><br/><br/></td>
                </tr>
            </table>
        </footer>
    </div>
</body>
</html>
