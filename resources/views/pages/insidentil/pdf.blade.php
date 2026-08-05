<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Rencana Kerja Insidentil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #777;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            color: #fff;
        }
        .bg-success { background-color: #198754; }
        .bg-primary { background-color: #0d6efd; }
        .bg-secondary { background-color: #6c757d; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Rencana Kerja Insidentil</h2>
        <p>Periode: {{ $periode ? $periode->nama_periode : 'Semua Periode' }}</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }} WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="30%">Uraian Tugas Insidentil</th>
                <th width="18%">Pembuat</th>
                <th width="18%">Rekan Kerja</th>
                <th width="15%">Estimasi Tanggal / Jam</th>
                <th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($insidentils as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->uraian_tugas }}</td>
                    <td>
                        <strong>{{ $item->user ? $item->user->name : '-' }}</strong><br>
                        <small>{{ $item->user->jabatan ?? '-' }}</small>
                    </td>
                    <td>
                        @if($item->taggedUsers->count() > 0)
                            {{ $item->taggedUsers->pluck('name')->implode(', ') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        {{ $item->estimasi_tanggal_mulai ? date('d/m/Y', strtotime($item->estimasi_tanggal_mulai)) : '-' }}
                        s/d
                        {{ $item->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($item->estimasi_tanggal_selesai)) : '-' }}
                        <br>
                        <small>({{ $item->estimasi_jam_mulai ? date('H:i', strtotime($item->estimasi_jam_mulai)) : '-' }} - {{ $item->estimasi_jam_selesai ? date('H:i', strtotime($item->estimasi_jam_selesai)) : '-' }})</small>
                    </td>
                    <td class="text-center">
                        {{ $item->status }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data Rencana Kerja Insidentil.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
