@php
    $signatureDataUsers = collect();
    if (isset($items)) {
        $signatureDataUsers = $signatureDataUsers->merge($items->pluck('user')->filter());
    }
    if (isset($kegiatans)) {
        $signatureDataUsers = $signatureDataUsers->merge($kegiatans->pluck('user')->filter());
    }
    $signatureDataUser = $signatureDataUsers->unique('id')->count() === 1
        ? $signatureDataUsers->first()
        : null;
    $signatureEmployeeName = $employeeName
        ?? (isset($namaStaff) && strtoupper($namaStaff) !== 'SEMUA STAFF'
            ? $namaStaff
            : ($signatureDataUser?->name ?? (auth()->user()?->name ?? '...................................')));
    $signatureEmployeeLabel = $employeeLabel
        ?? (isset($jabatanStaff) && strtoupper($jabatanStaff) !== 'SEMUA JABATAN'
            ? $jabatanStaff
            : 'Pegawai Yang Bersangkutan');
    $signatureEmployeeNup = $employeeNup
        ?? ($signatureDataUser?->nidn ?: (isset($namaStaff) && strtoupper($namaStaff) !== 'SEMUA STAFF'
            ? '-'
            : (auth()->user()?->nidn ?: '-')));
    $signatureLeaderLabel = $leaderLabel ?? 'Pimpinan Universitas Ibnu Sina';
    $signatureLeaderName = $leaderName ?? 'Dr. MUHAMMAD ROPIANTO, S.Kom., M.Kom';
    $signatureLeaderNup = $leaderNup ?? '1028067804';
@endphp
<table style="width: 100%; margin-top: 24px; border-collapse: collapse; page-break-inside: avoid; font-family: Helvetica, Arial, sans-serif; font-size: 9pt; color: #222;">
    <tr>
        <td style="width: 50%; text-align: center; vertical-align: top; padding: 0 12px;">
            <div>Mengetahui,</div>
            <div style="margin-top: 5px; font-weight: bold;">{{ $signatureLeaderLabel }}</div>
            <div style="height: 52px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">{{ $signatureLeaderName }}</div>
            <div style="margin-top: 2px; color: #555;">NUP: {{ $signatureLeaderNup }}</div>
        </td>
        <td style="width: 50%; text-align: center; vertical-align: top; padding: 0 12px;">
            <div>Batam, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
            <div style="margin-top: 5px; font-weight: bold;">{{ $signatureEmployeeLabel }}</div>
            <div style="height: 52px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">{{ $signatureEmployeeName }}</div>
            <div style="margin-top: 2px; color: #555;">NUP: {{ $signatureEmployeeNup }}</div>
        </td>
    </tr>
</table>
