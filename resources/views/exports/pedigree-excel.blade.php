<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    table { font-family: 'Arial', sans-serif; font-size: 11px; text-align: center; vertical-align: middle; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 4px; }
    .bg-light { background-color: #f3f3f3; }
</style>
<table>
    <tr>
        <td colspan="4" rowspan="4" align="center" valign="middle">
            <strong style="color:orange; font-size:20px;">ProCer</strong>
        </td>
        <td colspan="17" align="center" style="font-weight:bold; font-size:14px;">GERENCIA DE PRODUCCIÓN PORCINA</td>
    </tr>
    <tr>
        <td colspan="17" align="center" style="font-weight:bold;">PROCER, C.A. LA NUETA GUADALUPANA, SECTOR OJO DE AGUA. CARRETERA VIA GUADALUPE, HACIENDA.</td>
    </tr>
    <tr>
        <td colspan="17" align="center" style="font-weight:bold;">RIF: J-30974410-0. TELF. MASTER: (0253) 4911001</td>
    </tr>
    <tr>
        <td colspan="17"></td>
    </tr>
    <tr>
        <td colspan="21" align="center" style="font-weight:bold; font-size:14px; color:#0e5295; background-color:#e0eef7;">SELECCIÓN DE GENETICA HEMBRAS REEMPLAZO PROCER</td>
    </tr>
    <tr>
        <th rowspan="2">N°</th>
        <th rowspan="2">SALA</th>
        <th rowspan="2">LOTE</th>
        <th rowspan="2">JAULA</th>
        <th rowspan="2">ID MADRE</th>
        <th rowspan="2">N°<br>PARTOS</th>
        <th rowspan="2">ID<br>PADRE</th>
        <th rowspan="2">PIC<br>NACT</th>
        <th rowspan="2">LNV</th>
        <th rowspan="2">N° LECHO<br>MAS SELECC.</th>
        <th rowspan="2">ID LECHONES<br>(OREJA)</th>
        <th rowspan="2">ID LECHONES<br>(ARETE)</th>
        <th rowspan="2">RAZA</th>
        <th colspan="2">PESO</th>
        <th rowspan="2">M.<br>DE<br>PEZONES</th>
        <th colspan="3">N° PEZONES</th>
        <th rowspan="2">SELECCIÓ N</th>
        <th rowspan="2">OBSER.</th>
    </tr>
    <tr>
        <th>P. C/UP.</th>
        <th>PROM</th>
        <th>IZQ</th>
        <th>DETRÁS<br>OMBLIGO<br>LADO IZQ</th>
        <th>DETRÁS<br>OMBLIGO<br>LADO DER.</th>
    </tr>

    @php $index = 1; @endphp
    @foreach ($births as $birth)
        @php $avgWeight = $birth->details->count() > 0 ? number_format($birth->details->avg('weight'), 2) : ''; @endphp
        @foreach ($birth->details as $detail)
            <tr>
                <td style="font-weight:bold;">{{ $index++ }}</td>
                <td>{{ $birth->room }}</td>
                <td>{{ $birth->maternity_lot }}</td>
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
