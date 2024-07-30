<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Surat Tanda Terima {{ '- '.$suratTandaTerima->nomor_document ?? '' }}</title>

    <style>
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 14px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.heading td {
            background: #e3e2e2;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            border: 1px solid black;
            /* Added this line for black border */
        }

        .invoice-box table tr.heading td.no {
            width: 25px;
            text-align: center;
            /* Set width for No. column */
        }

        .invoice-box table tr.heading td.keterangan {
            width: 270px;
            /* Set width for No. column */
        }

        .invoice-box table tr.item td {
            border: 1px solid black;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(4) {
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .small-text {
            font-size: 11px;
            font-style: italic;
            color: #2f2cc0;
        }

        .label {
            display: ;
            width: 70px;
        }

        .label-right {
            margin-left: 10px;
            /* Adjust as needed */
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /** RTL **/
        .invoice-box.rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .invoice-box.rtl table {
            text-align: right;
        }

        .invoice-box.rtl table tr td:nth-child(2) {
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <img src="../public/storage/{{$suratTandaTerima->company->image}}"
            style="width: 109%; max-width: 900px; margin-left: -28px; margin-top: -27px" />
        <table cellpadding="0" cellspacing="0">
            <tr class="title">
                <td colspan="4">
                    <table>
                        <tr>
                            <td style="text-align: center;">
                                <h3>Tanda Terima Dokumen</h3>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="4">
                    <table>
                        <tr>
                            <td style="text-align: left;">
                                <span class="label">Kepada</span><span style="margin-left: 13px">: {{
                                    $suratTandaTerima->kepada }}</span><br />
                                <span class="label">Nomor</span><span style="margin-left: 20px">: {{
                                    $suratTandaTerima->nomor_document }}</span><br />
                                <span class="label">Tanggal</span><span style="margin-left: 10px">: {{
                                    Carbon\Carbon::parse($suratTandaTerima->tanggal)->isoFormat('D MMMM Y') }}</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td class="no">No.</td>
                <td class="keterangan" style="text-align: center">Keterangan</td>
                <td style="text-align: center; width: 90px;">Nomor Dokumen</td>
                <td style="text-align: center; width: 70px;">Jumlah</td>
            </tr>

            @foreach ($suratTandaTerima->items as $index => $item)
            <tr class="item">
                <td style="text-align: center">{{ $index + 1 }}</td>
                <td style="text-align: left">{{ $item->keterangan }}</td>
                <td style="text-align: {{ $item->nomor_document ? 'left' : 'center' }}">
                    {{ $item->nomor_document ?? '-' }}
                </td>
                <td style="text-align: center">{{ $item->qty .' '.$item->satuan }} </td>
            </tr>
            @endforeach

            <tr class="item">
                <td colspan="3" style="text-align: right;">Total:</td>
                <td style="text-align: center">{{ $suratTandaTerima->total }}</td>
            </tr>

            <tr class="information">
                <td colspan="4">
                    <table>
                        <tr>
                            <td style="text-align: center; ">
                                <p class="small-text"><b>Jika dokumen sudah diterima mohon untuk dapat ditanda tangani
                                        dan
                                        di email ke
                                        {{ $suratTandaTerima->company->email }}</b></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="4">
                    <table>
                        <tr>
                            <td>
                                Menyerahkan,
                            </td>

                            <td style="text-align: center;">
                                Menerima,
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <br>
            <br>
        </table>
    </div>
</body>

</html>