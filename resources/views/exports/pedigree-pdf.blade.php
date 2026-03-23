<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedigree PDF - Sala {{ $sala }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto;}
        th, td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
        .title-row td { background-color: #e0eef7; color: #0e5295; font-weight: bold; font-size: 14px; text-transform: uppercase; padding: 8px; border: 2px solid #000;}
        .header-top { font-weight: bold; font-size: 14px; }
        .header-sub { font-weight: bold; font-size: 10px; }
        .bg-light { background-color: #f0f0f0; font-weight: bold; font-size: 9px; }
        .logo-text { color: #f0a500; font-size: 24px; font-weight: bold; text-align: center; font-family: sans-serif; }
        .table-header th { background-color: #f7f7f7; font-size: 9px; }
        .small-col { width: 3%; }
        .med-col { width: 5%; }
        td { font-size: 9px; }
    </style>
</head>
<body>

<table>
    <tr>
        <td colspan="4" rowspan="3" class="logo-text" style="vertical-align: middle;">
            ProCer
        </td>
        <td colspan="17" class="header-top">GERENCIA DE PRODUCCIÓN PORCINA</td>
    </tr>
    <tr>
        <td colspan="17" class="header-sub">PROCER, C.A. LA NUETA GUADALUPANA, SECTOR OJO DE AGUA. CARRETERA VIA GUADALUPE, HACIENDA.</td>
    </tr>
    <tr>
        <td colspan="17" class="header-sub">RIF: J-30974410-0. TELF. MASTER: (0253) 4911001</td>
    </tr>
    <tr class="title-row">
        <td colspan="21">SELECCIÓN DE GENETICA HEMBRAS REEMPLAZO PROCER</td>
    </tr>
    <tr class="table-header">
        <th rowspan="2" class="small-col">N°</th>
        <th rowspan="2" class="med-col">SALA</th>
        <th rowspan="2" class="med-col">LOTE</th>
        <th rowspan="2" class="med-col">JAULA</th>
        <th rowspan="2" class="med-col">ID MADRE</th>
        <th rowspan="2" class="small-col">N°<br>PAR</th>
        <th rowspan="2" class="med-col">ID<br>PADRE</th>
        <th rowspan="2" class="med-col">PIC<br>NACT</th>
        <th rowspan="2" class="small-col">LNV</th>
        <th rowspan="2" class="small-col">N° LECHO<br>MAS. S</th>
        <th rowspan="2" class="med-col">ID LECHON<br>(OREJA)</th>
        <th rowspan="2" class="med-col">ID LECHON<br>(ARETE)</th>
        <th rowspan="2" class="med-col">RAZA</th>
        <th colspan="2">PESO</th>
        <th rowspan="2" class="small-col">M.<br>DE<br>PEZ</th>
        <th colspan="3">N° PEZONES</th>
        <th rowspan="2" class="med-col">SELECC.</th>
        <th rowspan="2" class="med-col">OBSER.</th>
    </tr>
    <tr class="table-header">
        <th>P. C/UP.</th>
        <th>PROM</th>
        <th>IZQ</th>
        <th>DET<br>OMB<br>IZQ</th>
        <th>DET<br>OMB<br>DER</th>
    </tr>

    @php $index = 1; @endphp
    @foreach ($births as $birth)
        @php $avgWeight = $birth->details->count() > 0 ? number_format($birth->details->avg('weight'), 2) : ''; @endphp
        @foreach ($birth->details as $detail)
            <tr>
                <td style="font-weight:bold;">{{ $index++ }}</td>
                <td>{{ $birth->room }}</td>
                <td style="font-weight:bold;">{{ $lote }}</td>
                <td>{{ $birth->cage }}</td>
                <td>{{ $birth->mother_tag }}</td>
                <td>{{ $birth->parity }}</td>
                <td>{{ $birth->father_tag }}</td>
                <td>{{ $birth->pic_cycle }}-{{ str_pad($birth->pic_day, 3, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $birth->lnv }}</td>
                <td>{{ $birth->quantity }}</td>
                <td>{{ $detail->ear_id }}</td>
                <td>{{ $detail->generated_id }}</td>
                <td>{{ optional($birth->genetic)->name }}</td>
                <td>{{ $detail->weight ?? '' }}</td>
                <td style="background-color: #f0f0f0;">{{ $avgWeight }}</td>
                <td>{{ $detail->teats_total }}</td>
                <td>{{ $detail->teats_left }}</td>
                <td>{{ $detail->teats_behind_shoulder_left }}</td>
                <td>{{ $detail->teats_behind_shoulder_right }}</td>
                <td>{{ strtoupper($detail->sex) }}</td>
                <td></td>
            </tr>
        @endforeach
    @endforeach
</table>

</body>
</html>
