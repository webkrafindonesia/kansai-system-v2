<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Receipt Customer#{{ $receipt->customer->name }}</title>
    <style>
        @page{
            margin: 0px;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px;
            color: #333;
            font-size: 13px;
        }

        .header {
            text-align: left;
        }

        h1{
            margin: 0px;
            padding: 0px;
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
            font-size: 12px;
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
            clear: both;
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
            padding: 5px;
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
            bottom: 20px;
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
            <table width="100%" class="table company-info" style="font-size:11px">
                <tr>
                    <td>
                        <div style="float:left">
                            <img src="data:image/jpeg;base64,{{ $logo }}" style="width:56.25px; height: 30px">
                        </div>
                        <div style="float:left; margin-left: 10px">
                            <strong>PT Kansai Indopart Mandiri</strong><br/>
                            Jl. Prepedan Raya No.23, <br/>
                            RT.8/RW.9, Prepedan,<br/>
                            Jakarta Barat, 11820<br/>
                            (WA: 081387684102)<br/>
                            <hr width="300px"/>
                        </div>
                    </td>
                    <td align="right">
                        <h1 style="float:right">TANDA TERIMA</h1>
                    </td>
                </tr>
            </table>
            <table width="100%" class="table" border="0">
                <tr>
                    <td width="35%">Kepada Toko</td>
                    <td width="1%">:</td>
                    <td>{{$receipt->customer->name}}</td>
                </tr>
                <tr valign="top">
                    <td>Jml. Faktur</td>
                    <td>:</td>
                    <td>
                        @php
                            $count = $receipt->items()->where('reference','Sales Order')->count();
                            echo $count.' ('.ucwords(Number::spell($count, locale: 'id')).') Lembar';
                        @endphp
                    </td>
                </tr>
                <tr>
                    <td>Bulan</td>
                    <td>:</td>
                    <td>{{ $receipt->receipt_date->subMonth()->format('F') }}</td>
                </tr>
            </table>
        </div>

        <table class="table" width="100%" border="1">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tgl. Faktur</th>
                    <th>No. Faktur</th>
                    <th>Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total = 0;
                @endphp
                @foreach($receipt->items as $item)
                <tr>
                    <td align="center">{{ $loop->iteration }}.</td>
                    <td align="center">{{ $item->reference_date->format('d/m/Y') }}</td>
                    @if($item->reference == 'Sales Order')
                        <td align="center">{{ $item->salesOrder->invoice_no }}</td>
                        @php
                            $discount = ($item->salesOrder->discount_sales > $item->salesOrder->discount_company) ? $item->salesOrder->discount_sales : $item->salesOrder->discount_company;
                            $nominal = $item->salesOrder->items->sum('total_price') * (100 - $discount) / 100;
                            $total += $nominal;
                        @endphp
                        <td align="right">{{ number_format(moneyFormat($nominal),0,',','.') }}</td>
                    @else
                        <td align="center">RETUR</td>
                        @php
                            $nominal = $item->returnSalesOrder->items->sum('discounted_total_price');
                            $total -= $nominal;
                        @endphp
                        <td align="right"><em>({{ number_format(moneyFormat($nominal),0,',','.') }})</em></td>
                    @endif
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr valign="top" style="border-top: 1px black solid">
                    <td colspan="3" align="right"><strong>TOTAL</strong></td>
                    <td align="right"><strong>Rp {{ number_format(moneyFormat($total),0,',','.') }}</strong></td>
                </tr>
                <tr valign="top" style="border-top: 1px black solid">
                    <td colspan="4">
                        Terbilang : <br/>
                        {{ ucwords(Number::spell(moneyFormat($total), locale: 'id')) }} Rupiah<br/>
                    </td>
                </tr>
            </tfoot>
        </table>
        <br/>

        <footer>
            <table class="table" width="95%">
                <tr>
                    <td width="50%" align="left">
                        HORMAT KAMI
                        <br/><br/><br/><br/><br/>
                        (<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>)
                    </td>
                    <td width="50%" align="right">
                        YANG MENERIMA
                        <br/><br/><br/><br/><br/>
                        (<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>)
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <br/>
                        Kembali Tanggal : <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>/<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>/<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>
                        <br/><br/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center" style="border: 2px black solid; padding:10px">
                        Pembayaran melalui <strong>GIRO / TRANSFER</strong>, ke Rek, BCA<br/>
                        <strong>A/N: WANG CHUN, A/C: 370-3021111</strong>
                    </td>
                </tr>
            </table>
        </footer>
    </div>

</body>
</html>
