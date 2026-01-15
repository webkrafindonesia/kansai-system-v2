<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan SO#{{ $deliveryOrder->salesOrder->salesorder_no }}</title>
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
            margin-top: 5px;
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
                        <h1 style="float:right">SURAT JALAN</h1>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><br/><br/><br/><hr/></td>
                </tr>
                {{-- <tr>
                    <td width="55%"></td>
                    <td width="15%">No. Surat Jalan</td>
                    <td>{{ $deliveryOrder->salesOrder->invoice_no }}</td>
                </tr> --}}
                <tr valign="top">
                    <td rowspan="2" width="55%">
                        <strong>{{$deliveryOrder->salesOrder->customer->name}}</strong><br>
                        {!! nl2br($deliveryOrder->salesOrder->customer->address) !!}
                    </td>
                    <td width="15%">Tanggal</td>
                    <td>: {{ $deliveryOrder->delivery_date->format('l, j F Y') }}</td>
                </tr>
                <tr>
                    <td>Term of Payment</td>
                    <td>: 60 hari ({{ $deliveryOrder->salesOrder->term_of_payment->format('F Y') }})</td>
                </tr>
            </table>
        </div>

        <table class="table" width="100%">
            <thead>
                <tr>
                    <th width="10%">No.</th>
                    <th width="50%">Item Description</th>
                    <th width="10%">Qty</th>
                    <th width="10%">Satuan</th>
                    <th width="10%">OUT-OK</th>
                    <th width="10%">IN-OK</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryOrder->items as $item)
                <tr>
                    <td align="center">{{ $loop->iteration }}.</td>
                    <td>{{ $item->product->code.' - '.$item->product->name }}</td>
                    <td align="center">{{ number_format($item->qty,0,',','.') }}</td>
                    <td align="center"> {{ $item->uom }}</td>
                    <td style="border-bottom:1px black solid"></td>
                    <td style="border-bottom:1px black solid"></td>
                </tr>
                @endforeach
                @if($deliveryOrder->items->count() < 15)
                    @for($i = $deliveryOrder->items->count(); $i < 15; $i++)
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
                    <td colspan="2">
                        Cek terlebih dahulu barang tersebut sebelum Anda menandatangani Surat Jalan ini.<br/>
                        Barang tersebut telah diterima dalam keadaan Baik dan Benar.
                    </td>
                    <td align="right"><h3>{{ number_format($deliveryOrder->items->sum('qty'),0,',','.') }} PCS</h3></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <footer>
            <table width="100%" cellspacing="10">
                <tr>
                    <td width="20%" align="center"">DIBUAT<br/><br/><br/><br/><br/></td>
                    <td width="20%" align="center"">DIPERIKSA<br/><br/><br/><br/><br/></td>
                    <td width="20%" align="center"">GUDANG<br/><br/><br/><br/><br/></td>
                    <td width="20%" align="center"">DIKIRIM<br/><br/><br/><br/><br/></td>
                    <td width="20%" align="center"">DITERIMA<br/><br/><br/><br/><br/></td>
                </tr>
            </table>
        </footer>
    </div>
</body>
</html>
