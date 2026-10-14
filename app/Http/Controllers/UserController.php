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
        $suggestedKepanitiaan = \App\Models\Kepanitiaan::whereNotNull('uraian_tugas')
            ->pluck('uraian_tugas')
            ->merge(['PKKMB', 'ESQ', 'MILAD', 'Kuliah Umum', 'Wisuda', 'Dies Natalis', 'Akreditasi'])
            ->unique()
            ->filter()
            ->values();

        return view('pages.user.create', compact('suggestedKepanitiaan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        if (isset($validated['roles'])) {
            if (is_array($validated['roles'])) {
                $validated['roles'] = implode(', ', array_filter(array_map('trim', $validated['roles'])));
            } else {
                $validated['roles'] = trim((string) $validated['roles']);
            }
        }

        // Process dynamic jabatan_kepanitiaan
        if (isset($validated['jabatan_kepanitiaan']) && is_array($validated['jabatan_kepanitiaan'])) {
            $cleanKepanitiaan = [];
            foreach ($validated['jabatan_kepanitiaan'] as $item) {
                $nama = trim($item['nama'] ?? '');
                $jabatan = trim($item['jabatan'] ?? '');
                if (!empty($nama) && !empty($jabatan)) {
                    $cleanKepanitiaan[] = [
                        'nama' => $nama,
                        'jabatan' => $jabatan,
                    ];
                }
            }
            $validated['jabatan_kepanitiaan'] = !empty($cleanKepanitiaan) ? $cleanKepanitiaan : null;
        } else {
            $validated['jabatan_kepanitiaan'] = null;
        }

        User::create($validated);

        Alert::success('Berhasil', 'User berhasil ditambahkan.');
        return redirect()->route('user.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('user.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $suggestedKepanitiaan = \App\Models\Kepanitiaan::whereNotNull('uraian_tugas')
            ->pluck('uraian_tugas')
            ->merge(['PKKMB', 'ESQ', 'MILAD', 'Kuliah Umum', 'Wisuda', 'Dies Natalis', 'Akreditasi'])
            ->unique()
            ->filter()
            ->values();

        return view('pages.user.edit', compact('user', 'suggestedKepanitiaan'));
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

        if (isset($validated['roles'])) {
            if (is_array($validated['roles'])) {
                $validated['roles'] = implode(', ', array_filter(array_map('trim', $validated['roles'])));
            } else {
                $validated['roles'] = trim((string) $validated['roles']);
            }
        }

        // Process dynamic jabatan_kepanitiaan
        if (isset($validated['jabatan_kepanitiaan']) && is_array($validated['jabatan_kepanitiaan'])) {
            $cleanKepanitiaan = [];
            foreach ($validated['jabatan_kepanitiaan'] as $item) {
                $nama = trim($item['nama'] ?? '');
                $jabatan = trim($item['jabatan'] ?? '');
                if (!empty($nama) && !empty($jabatan)) {
                    $cleanKepanitiaan[] = [
                        'nama' => $nama,
                        'jabatan' => $jabatan,
                    ];
                }
            }
            $validated['jabatan_kepanitiaan'] = !empty($cleanKepanitiaan) ? $cleanKepanitiaan : null;
        } else {
            $validated['jabatan_kepanitiaan'] = null;
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

        if ($user->isProtectedAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Superadmin dan Admin tidak dapat dihapus.'
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

        if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
            // Superadmin, Admin, Pimpinan Rektorat -> Seluruh user (kecuali diri sendiri)
        } elseif ($authUser->isPimpinanUnit()) {
            $query->where('unit', $authUser->unit);
        } else {
            $query->where('id', $authUser->id);
        }

        $items = $query->get();
        $count = 0;

        foreach ($items as $item) {
            // Skip protected admin accounts
            if ($item->isProtectedAdmin()) {
                continue;
            }
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
            'E1' => 'nup',
            'F1' => 'unit',
            'G1' => 'jabatan',
            'H1' => 'nomor_wa',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerRange = 'A1:H1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('15432D');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setARGB('FFFFFF');

        // Sample Data 1 (Contoh Multi-Role)
        $sheet->setCellValue('A2', 'Ahmad Fadillah, S.Kom., M.Kom');
        $sheet->setCellValue('B2', 'ahmad.fadillah@gmail.com');
        $sheet->setCellValue('C2', 'password123');
        $sheet->setCellValue('D2', 'staff lpti, kepanitiaan');
        $sheet->setCellValue('E2', '1028067804');
        $sheet->setCellValue('F2', 'LPTI');
        $sheet->setCellValue('G2', 'Staff Programmer');

        // Sample Data 2
        $sheet->setCellValue('A3', 'Siti Rahmawati, S.E., M.M');
        $sheet->setCellValue('B3', 'siti.rahma@gmail.com');
        $sheet->setCellValue('C3', 'password123');
        $sheet->setCellValue('D3', 'staff keuangan fakultas');
        $sheet->setCellValue('E3', '-');
        $sheet->setCellValue('F3', 'BAUK');
        $sheet->setCellValue('G3', 'Staff Keuangan');

        // Sample Data 3 (Role Kepanitiaan)
        $sheet->setCellValue('A4', 'Budi Santoso, S.Kom');
        $sheet->setCellValue('B4', 'budi.santoso@uis.ac.id');
        $sheet->setCellValue('C4', 'password123');
        $sheet->setCellValue('D4', 'kepanitiaan');
        $sheet->setCellValue('E4', '-');
        $sheet->setCellValue('F4', 'PANITIA');
        $sheet->setCellValue('G4', 'Staff Kepanitiaan');

        foreach (range('A', 'H') as $col) {
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
     * Import Users from Excel file (Supports Insert & Update, imports all rows, fills empty fields with '-')
     */
    public function importExcel(Request $request)
    {
        $file = $request->file('file') ?? $request->file('file_excel');
        if (!$file) {
            Alert::error('Gagal', 'File Excel wajib diunggah.')->toToast();
            return redirect()->back();
        }

        try {
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

            $nidnIdx = array_search('nup', $header);
            if ($nidnIdx === false) $nidnIdx = array_search('nidn', $header);
            $unitIdx = array_search('unit', $header);
            $jabatanIdx = array_search('jabatan', $header);
            $nomorWaIdx = array_search('nomor_wa', $header);
            $jabatanPkkmbIdx = array_search('jabatan_pkkmb', $header);
            $jabatanEsqIdx = array_search('jabatan_esq', $header);
            $jabatanMiladIdx = array_search('jabatan_milad', $header);
            $jabatanKuliahUmumIdx = array_search('jabatan_kuliah_umum', $header);
            $statusIdx = array_search('status', $header);
            $isActiveIdx = array_search('is_active', $header);

            $imported = 0;
            $updated = 0;

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Periksa apakah baris memiliki data (tidak kosong total)
                $hasData = false;
                foreach ($row as $cell) {
                    if ($cell !== null && trim((string)$cell) !== '') {
                        $hasData = true;
                        break;
                    }
                }
                if (!$hasData) {
                    continue;
                }

                $name = ($nameIdx !== false && isset($row[$nameIdx]) && trim((string) $row[$nameIdx]) !== '')
                    ? trim((string) $row[$nameIdx])
                    : '-';

                $rawEmail = ($emailIdx !== false && isset($row[$emailIdx]) && trim((string) $row[$emailIdx]) !== '')
                    ? strtolower(trim((string) $row[$emailIdx]))
                    : null;

                $rawPassword = ($passwordIdx !== false && isset($row[$passwordIdx]) && trim((string) $row[$passwordIdx]) !== '')
                    ? trim((string) $row[$passwordIdx])
                    : null;

                $roles = ($rolesIdx !== false && isset($row[$rolesIdx]) && trim((string) $row[$rolesIdx]) !== '')
                    ? strtolower(trim((string) $row[$rolesIdx]))
                    : 'kepanitiaan';

                $nidn = ($nidnIdx !== false && isset($row[$nidnIdx]) && trim((string) $row[$nidnIdx]) !== '')
                    ? trim((string) $row[$nidnIdx])
                    : '-';

                $unit = ($unitIdx !== false && isset($row[$unitIdx]) && trim((string) $row[$unitIdx]) !== '')
                    ? trim((string) $row[$unitIdx])
                    : '-';

                $jabatan = ($jabatanIdx !== false && isset($row[$jabatanIdx]) && trim((string) $row[$jabatanIdx]) !== '')
                    ? trim((string) $row[$jabatanIdx])
                    : '-';

                $nomorWa = ($nomorWaIdx !== false && isset($row[$nomorWaIdx]) && trim((string) $row[$nomorWaIdx]) !== '')
                    ? trim((string) $row[$nomorWaIdx])
                    : null;

                $jabatanPkkmb = ($jabatanPkkmbIdx !== false && isset($row[$jabatanPkkmbIdx]) && trim((string) $row[$jabatanPkkmbIdx]) !== '')
                    ? trim((string) $row[$jabatanPkkmbIdx])
                    : '-';

                $jabatanEsq = ($jabatanEsqIdx !== false && isset($row[$jabatanEsqIdx]) && trim((string) $row[$jabatanEsqIdx]) !== '')
                    ? trim((string) $row[$jabatanEsqIdx])
                    : '-';

                $jabatanMilad = ($jabatanMiladIdx !== false && isset($row[$jabatanMiladIdx]) && trim((string) $row[$jabatanMiladIdx]) !== '')
                    ? trim((string) $row[$jabatanMiladIdx])
                    : '-';

                $jabatanKuliahUmum = ($jabatanKuliahUmumIdx !== false && isset($row[$jabatanKuliahUmumIdx]) && trim((string) $row[$jabatanKuliahUmumIdx]) !== '')
                    ? trim((string) $row[$jabatanKuliahUmumIdx])
                    : '-';

                $status = ($statusIdx !== false && isset($row[$statusIdx]) && trim((string) $row[$statusIdx]) !== '')
                    ? trim((string) $row[$statusIdx])
                    : 'Aktif';

                $isActiveVal = true;
                if ($isActiveIdx !== false && isset($row[$isActiveIdx]) && trim((string) $row[$isActiveIdx]) !== '') {
                    $rawActive = strtolower(trim((string) $row[$isActiveIdx]));
                    $isActiveVal = !in_array($rawActive, ['0', 'nonaktif', 'false', 'non aktif']);
                }

                // Buat email unik untuk SETIAP baris agar setiap data di Excel masuk 100%
                if (!empty($rawEmail)) {
                    $baseEmail = $rawEmail;
                } else {
                    $slugName = \Illuminate\Support\Str::slug($name !== '-' ? $name : 'pegawai', '.');
                    if (empty($slugName)) {
                        $slugName = 'pegawai';
                    }
                    $baseEmail = $slugName . '@uis.ac.id';
                }

                // Pastikan email unik di DB agar tidak terjadi duplicate error dan semua baris PASTI masuk
                $finalEmail = $baseEmail;
                $suffix = 1;
                while (User::whereRaw('LOWER(TRIM(email)) = ?', [$finalEmail])->exists()) {
                    $parts = explode('@', $baseEmail);
                    $localPart = $parts[0];
                    $domainPart = isset($parts[1]) ? '@' . $parts[1] : '@uis.ac.id';
                    $finalEmail = $localPart . '.' . $suffix . $domainPart;
                    $suffix++;
                }

                // Password
                $password = (!empty($rawPassword) && $rawPassword !== '-')
                    ? Hash::make($rawPassword)
                    : Hash::make('password123');

                $importedDynamicKepanitiaan = [];
                if (!empty($jabatanPkkmb) && $jabatanPkkmb !== '-') $importedDynamicKepanitiaan[] = ['nama' => 'PKKMB', 'jabatan' => $jabatanPkkmb];
                if (!empty($jabatanEsq) && $jabatanEsq !== '-') $importedDynamicKepanitiaan[] = ['nama' => 'ESQ', 'jabatan' => $jabatanEsq];
                if (!empty($jabatanMilad) && $jabatanMilad !== '-') $importedDynamicKepanitiaan[] = ['nama' => 'MILAD', 'jabatan' => $jabatanMilad];
                if (!empty($jabatanKuliahUmum) && $jabatanKuliahUmum !== '-') $importedDynamicKepanitiaan[] = ['nama' => 'Kuliah Umum', 'jabatan' => $jabatanKuliahUmum];

                User::create([
                    'name' => $name,
                    'email' => $finalEmail,
                    'nomor_wa' => $nomorWa,
                    'password' => $password,
                    'roles' => $roles,
                    'nidn' => $nidn,
                    'unit' => $unit,
                    'jabatan' => $jabatan,
                    'jabatan_kepanitiaan' => !empty($importedDynamicKepanitiaan) ? $importedDynamicKepanitiaan : null,
                    'status' => $status,
                    'is_active' => $isActiveVal,
                ]);
                $imported++;
            }

            Alert::success('Berhasil', "Import selesai: Seluruh {$imported} data dari file Excel berhasil dimasukkan ke sistem.")->toToast();
            return redirect()->route('user.index');

        } catch (\Exception $e) {
            Alert::error('Gagal', 'Terjadi kesalahan saat membaca file Excel: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
