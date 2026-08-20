<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserDataTable $dataTable)
    {
        return $dataTable->render('pages.user.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        Alert::success('Berhasil', 'User berhasil ditambahkan.');
        return redirect()->route('user.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return view('pages.user.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('pages.user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, string $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validated();

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        Alert::success('Berhasil', 'Data user berhasil diperbarui.');
        return redirect()->route('user.index');
    }

    /**
     * Update user password via AJAX
     */
    public function updatePassword(Request $request, string $id)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password user berhasil diperbarui.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.'
        ]);
    }

    /**
     * Delete multiple users at once.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $authUser = auth()->user();
        $query = User::whereIn('id', $request->ids);

        // Disallow deleting self
        $query->where('id', '!=', $authUser->id);

        if ($authUser->isPimpinanUnit()) {
            $query->where('unit', $authUser->unit);
        } elseif (!$authUser->isAdmin() && !$authUser->isPimpinanRektorat()) {
            $query->where('id', $authUser->id);
        }

        $items = $query->get();
        $count = 0;

        foreach ($items as $item) {
            $item->delete();
            $count++;
        }

        if ($count === 0 && count($request->ids) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada user yang dapat dihapus (akun Anda sendiri atau di luar wewenang Anda tidak dapat dihapus).'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menghapus {$count} data user terpilih."
        ]);
    }

    /**
     * Download Excel Import Template for Users
     */
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template User');

        $headers = [
            'A1' => 'name',
            'B1' => 'email',
            'C1' => 'password',
            'D1' => 'roles',
            'E1' => 'nidn',
            'F1' => 'unit',
            'G1' => 'jabatan',
            'H1' => 'jabatan_pkkmb',
            'I1' => 'jabatan_esq',
            'J1' => 'jabatan_milad',
            'K1' => 'jabatan_kuliah_umum',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerRange = 'A1:K1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('15432D');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setARGB('FFFFFF');

        // Sample Data 1
        $sheet->setCellValue('A2', 'Ahmad Fadillah, S.Kom., M.Kom');
        $sheet->setCellValue('B2', 'ahmad.fadillah@gmail.com');
        $sheet->setCellValue('C2', 'password123');
        $sheet->setCellValue('D2', 'staff lpti');
        $sheet->setCellValue('E2', '1028067804');
        $sheet->setCellValue('F2', 'LPTI');
        $sheet->setCellValue('G2', 'Staff Programmer');
        $sheet->setCellValue('H2', 'Anggota Seksi Acara');
        $sheet->setCellValue('I2', 'Koordinator IT');
        $sheet->setCellValue('J2', '-');
        $sheet->setCellValue('K2', '-');

        // Sample Data 2
        $sheet->setCellValue('A3', 'Siti Rahmawati, S.E., M.M');
        $sheet->setCellValue('B3', 'siti.rahma@gmail.com');
        $sheet->setCellValue('C3', 'password123');
        $sheet->setCellValue('D3', 'staff keuangan fakultas');
        $sheet->setCellValue('E3', '-');
        $sheet->setCellValue('F3', 'BAUK');
        $sheet->setCellValue('G3', 'Staff Keuangan');
        $sheet->setCellValue('H3', 'Bendahara Panitia');
        $sheet->setCellValue('I3', '-');
        $sheet->setCellValue('J3', 'Seksi Konsumsi');
        $sheet->setCellValue('K3', '-');

        // Sample Data 3 (Role Kepanitiaan)
        $sheet->setCellValue('A4', 'Budi Santoso, S.Kom');
        $sheet->setCellValue('B4', 'budi.santoso@uis.ac.id');
        $sheet->setCellValue('C4', 'password123');
        $sheet->setCellValue('D4', 'kepanitiaan');
        $sheet->setCellValue('E4', '-');
        $sheet->setCellValue('F4', 'PANITIA');
        $sheet->setCellValue('G4', 'Staff Kepanitiaan');
        $sheet->setCellValue('H4', 'Koordinator Absensi');
        $sheet->setCellValue('I4', 'Anggota Seksi Acara');
        $sheet->setCellValue('J4', '-');
        $sheet->setCellValue('K4', '-');

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'template_import_user.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import Users from Excel file (Supports Insert & Update)
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file_excel');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows) || count($rows) < 2) {
                Alert::error('Gagal', 'File Excel kosong atau tidak memiliki data user.')->toToast();
                return redirect()->back();
            }

            $header = array_map(function ($h) {
                return strtolower(trim((string) $h));
            }, $rows[0]);

            $nameIdx = array_search('name', $header);
            if ($nameIdx === false) $nameIdx = array_search('nama', $header);
            if ($nameIdx === false) $nameIdx = array_search('nama_lengkap', $header);
            if ($nameIdx === false) $nameIdx = array_search('nama_pegawai', $header);

            $emailIdx = array_search('email', $header);
            $passwordIdx = array_search('password', $header);
            $rolesIdx = array_search('roles', $header);
            if ($rolesIdx === false) $rolesIdx = array_search('role', $header);

            $nidnIdx = array_search('nidn', $header);
            $unitIdx = array_search('unit', $header);
            $jabatanIdx = array_search('jabatan', $header);
            $jabatanPkkmbIdx = array_search('jabatan_pkkmb', $header);
            $jabatanEsqIdx = array_search('jabatan_esq', $header);
            $jabatanMiladIdx = array_search('jabatan_milad', $header);
            $jabatanKuliahUmumIdx = array_search('jabatan_kuliah_umum', $header);
            $statusIdx = array_search('status', $header);
            $isActiveIdx = array_search('is_active', $header);

            if ($nameIdx === false || $emailIdx === false) {
                Alert::error('Format Salah', 'Kolom header "name" dan "email" wajib ada di baris pertama Excel.')->toToast();
                return redirect()->back();
            }

            $imported = 0;
            $updated = 0;

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $name = isset($row[$nameIdx]) ? trim((string) $row[$nameIdx]) : '';
                $email = ($emailIdx !== false && isset($row[$emailIdx])) ? strtolower(trim((string) $row[$emailIdx])) : '';

                if (empty($name) && empty($email)) {
                    continue;
                }

                $rawPassword = ($passwordIdx !== false && !empty($row[$passwordIdx])) ? trim((string) $row[$passwordIdx]) : null;
                $roles = ($rolesIdx !== false && isset($row[$rolesIdx])) ? strtolower(trim((string) $row[$rolesIdx])) : null;
                $nidn = ($nidnIdx !== false && isset($row[$nidnIdx])) ? trim((string) $row[$nidnIdx]) : null;
                $unit = ($unitIdx !== false && isset($row[$unitIdx])) ? trim((string) $row[$unitIdx]) : null;
                $jabatan = ($jabatanIdx !== false && isset($row[$jabatanIdx])) ? trim((string) $row[$jabatanIdx]) : null;
                $jabatanPkkmb = ($jabatanPkkmbIdx !== false && isset($row[$jabatanPkkmbIdx])) ? trim((string) $row[$jabatanPkkmbIdx]) : null;
                $jabatanEsq = ($jabatanEsqIdx !== false && isset($row[$jabatanEsqIdx])) ? trim((string) $row[$jabatanEsqIdx]) : null;
                $jabatanMilad = ($jabatanMiladIdx !== false && isset($row[$jabatanMiladIdx])) ? trim((string) $row[$jabatanMiladIdx]) : null;
                $jabatanKuliahUmum = ($jabatanKuliahUmumIdx !== false && isset($row[$jabatanKuliahUmumIdx])) ? trim((string) $row[$jabatanKuliahUmumIdx]) : null;

                $status = ($statusIdx !== false && !empty($row[$statusIdx])) ? trim((string) $row[$statusIdx]) : null;
                
                $isActiveVal = null;
                if ($isActiveIdx !== false && isset($row[$isActiveIdx]) && trim((string) $row[$isActiveIdx]) !== '') {
                    $rawActive = strtolower(trim((string) $row[$isActiveIdx]));
                    $isActiveVal = !in_array($rawActive, ['0', 'nonaktif', 'false', 'non aktif']);
                }

                // 1. Cari user yang sudah ada berdasarkan Email, NIDN, atau Nama
                $existingUser = null;
                if (!empty($email)) {
                    $existingUser = User::whereRaw('LOWER(email) = ?', [$email])->first();
                }
                if (!$existingUser && !empty($nidn) && $nidn !== '-') {
                    $existingUser = User::where('nidn', $nidn)->first();
                }
                if (!$existingUser && !empty($name)) {
                    $existingUser = User::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])->first();
                }

                if ($existingUser) {
                    $updateData = [];

                    // Jika nama diisi di Excel, update
                    if (!empty($name)) {
                        $updateData['name'] = $name;
                    }

                    // Jika email diisi di Excel, update
                    if (!empty($email)) {
                        $updateData['email'] = $email;
                    }

                    // Role: Jika di Excel diisi role baru (tidak kosong / bukan '-'), pakai role baru.
                    // Jika di Excel KOSONG / '-', TETAP memakai role lama (tidak diubah).
                    if (!empty($roles) && $roles !== '-') {
                        $updateData['roles'] = $roles;
                    }

                    // Field data lainnya: update jika diisi di Excel (tidak kosong / bukan '-')
                    if ($nidn !== null && $nidn !== '' && $nidn !== '-') {
                        $updateData['nidn'] = $nidn;
                    }
                    if ($unit !== null && $unit !== '' && $unit !== '-') {
                        $updateData['unit'] = $unit;
                    }
                    if ($jabatan !== null && $jabatan !== '' && $jabatan !== '-') {
                        $updateData['jabatan'] = $jabatan;
                    }
                    if ($jabatanPkkmb !== null && $jabatanPkkmb !== '' && $jabatanPkkmb !== '-') {
                        $updateData['jabatan_pkkmb'] = $jabatanPkkmb;
                    }
                    if ($jabatanEsq !== null && $jabatanEsq !== '' && $jabatanEsq !== '-') {
                        $updateData['jabatan_esq'] = $jabatanEsq;
                    }
                    if ($jabatanMilad !== null && $jabatanMilad !== '' && $jabatanMilad !== '-') {
                        $updateData['jabatan_milad'] = $jabatanMilad;
                    }
                    if ($jabatanKuliahUmum !== null && $jabatanKuliahUmum !== '' && $jabatanKuliahUmum !== '-') {
                        $updateData['jabatan_kuliah_umum'] = $jabatanKuliahUmum;
                    }
                    if ($status !== null && $status !== '' && $status !== '-') {
                        $updateData['status'] = $status;
                    }
                    if ($isActiveVal !== null) {
                        $updateData['is_active'] = $isActiveVal;
                    }

                    // Password: Hanya update jika diisi password baru & bukan placeholder umum
                    if (!empty($rawPassword) && !in_array(strtolower($rawPassword), ['password123', 'password', '-'])) {
                        $updateData['password'] = Hash::make($rawPassword);
                    }

                    if (!empty($updateData)) {
                        $existingUser->update($updateData);
                    }
                    $updated++;
                } else {
                    // Buat user baru
                    if (empty($email)) {
                        $slugName = \Illuminate\Support\Str::slug($name, '.');
                        $email = $slugName . '@uis.ac.id';
                    }

                    $newUserPassword = (!empty($rawPassword) && !in_array(strtolower($rawPassword), ['password123', 'password', '-']))
                        ? $rawPassword
                        : 'password123';

                    User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($newUserPassword),
                        'roles' => (!empty($roles) && $roles !== '-') ? $roles : 'staff',
                        'nidn' => ($nidn !== '-' ? $nidn : null),
                        'unit' => ($unit !== '-' ? $unit : null),
                        'jabatan' => ($jabatan !== '-' ? $jabatan : null),
                        'jabatan_pkkmb' => ($jabatanPkkmb !== '-' ? $jabatanPkkmb : null),
                        'jabatan_esq' => ($jabatanEsq !== '-' ? $jabatanEsq : null),
                        'jabatan_milad' => ($jabatanMilad !== '-' ? $jabatanMilad : null),
                        'jabatan_kuliah_umum' => ($jabatanKuliahUmum !== '-' ? $jabatanKuliahUmum : null),
                        'status' => ($status !== null && $status !== '-') ? $status : 'Aktif',
                        'is_active' => ($isActiveVal !== null) ? $isActiveVal : true,
                    ]);
                    $imported++;
                }
            }

            Alert::success('Berhasil', "Import selesai: {$imported} user baru ditambahkan, {$updated} user diperbarui.")->toToast();
            return redirect()->route('user.index');

        } catch (\Exception $e) {
            Alert::error('Gagal', 'Terjadi kesalahan saat membaca file Excel: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
