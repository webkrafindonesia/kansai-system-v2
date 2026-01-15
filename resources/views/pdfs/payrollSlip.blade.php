<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Payroll Slip {{ $employeePayroll->name }}</title>
    <style>
        @page{
            margin: 0px;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px;
            color: #333;
            font-size: 16px;
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
            padding: 20px;
            text-align: center;
        }

        .table td {
            /* border: 1px solid #ddd; */
            padding: 20px;
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
            <table width="100%" class="" border="0">
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
                        <h1 style="float:right">Payroll Slip</h1>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><br/><br/><br/><hr/></td>
                </tr>
                <tr>
                    <td valign="top" width="55%" rowspan="2">{{$employeePayroll->employee->name}}</td>
                    <td width="15%">Gaji</td>
                    <td>{{$employeePayroll->type}}</td>
                </tr>
                <tr valign="top">
                    <td>Tanggal</td>
                    <td>{{ $employeePayroll->payroll_date->format('l, j F Y') }}</td>
                </tr>
            </table>
        </div>

        <table class="table" width="100%" cellspadding="0" border="1">
            <thead>
                <tr>
                    <th width="50%" colspan="2">PENDAPATAN</th>
                    <th colspan="2">PENGURANG</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="25%">Gaji Pokok</td>
                    <td width="25%" align="right">Rp {{ number_format(moneyFormat($employeePayroll->amount),0,',','.') }}</td>
                    <td width="25%">Pembayaran Pinjaman</td>
                    <td width="25%" align="right">Rp {{ number_format(moneyFormat($employeePayroll->amount_loan_repayment),0,',','.') }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4">GAJI BERSIH</th>
                </tr>
                <tr>
                    <td colspan="4" align="center" style="font-size:18px"><strong>Rp {{ number_format(moneyFormat($employeePayroll->amount_after_loan_repayment),0,',','.') }}</strong></td>
                </tr>
            </tfoot>
        </table>
        <br/>

        <footer>
            <div class="footer-note">
                Dokumen ini dicetak oleh sistem dan dianggap sah tanpa tanda tangan.
            </div>
        </footer>
    </div>

</body>
</html>
