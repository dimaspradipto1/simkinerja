<?php

namespace App\Http\Controllers;

use App\DataTables\SkKepanitiaanDataTable;
use App\Models\SkKepanitiaan;
use App\Models\User;
use App\Services\SkParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class SkKepanitiaanController extends Controller
{
    protected SkParserService $parserService;

    public function __construct(SkParserService $parserService)
    {
        $this->parserService = $parserService;
    }

    /**
     * Check if user is Superadmin or Admin.
     */
    protected function authorizeAdmin(): void
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin())) {
            abort(403, 'Akses Ditolak. Menu ini hanya dapat diakses oleh Administrator dan Superadmin.');
        }
    }

    /**
     * Display a listing of SK Kepanitiaan.
     */
    public function index(SkKepanitiaanDataTable $dataTable)
    {
        $this->authorizeAdmin();
        return $dataTable->render('pages.sk-kepanitiaan.index');
    }

    /**
     * Show form for uploading new SK Kepanitiaan.
     */
    public function create()
    {
        $this->authorizeAdmin();
        return view('pages.sk-kepanitiaan.create');
    }

    /**
     * Store uploaded SK, auto-parse members, and update dynamic user positions.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'file_sk' => 'required|file|mimes:pdf|max:30720',
            'nama_kepanitiaan' => 'nullable|string|max:255',
            'nomor_sk' => 'nullable|string|max:255',
            'tahun_akademik' => 'nullable|string|max:100',
            'tanggal_sk' => 'nullable|date',
            'keterangan' => 'nullable|string',
        ], [
            'file_sk.required' => 'Berkas SK Kepanitiaan wajib diunggah.',
            'file_sk.mimes' => 'Format berkas harus PDF.',
            'file_sk.max' => 'Ukuran berkas maksimal 30 MB.',
        ]);

        $uploadedFile = $request->file('file_sk');
        $storedPath = $uploadedFile->store('sk_kepanitiaan', 'public');
        $absolutePath = storage_path('app/public/' . $storedPath);

        // Auto-extract text and committee members from PDF
        $parseResult = $this->parserService->parsePdf($absolutePath);

        $namaKepanitiaan = trim($request->input('nama_kepanitiaan') ?: ($parseResult['nama_kepanitiaan'] ?: 'Kepanitiaan Kegiatan'));
        $nomorSk = trim($request->input('nomor_sk') ?: ($parseResult['nomor_sk'] ?: ''));
        $tahunAkademik = trim($request->input('tahun_akademik') ?: ($parseResult['tahun_akademik'] ?: date('Y') . '/' . (date('Y') + 1)));
        $tanggalSk = $request->input('tanggal_sk') ?: ($parseResult['tanggal_sk'] ?: date('Y-m-d'));

        $members = $parseResult['members'] ?? [];
        $totalAnggota = count($members);
        $totalMatched = 0;

        // Save SK record
        $sk = SkKepanitiaan::create([
            'nama_kepanitiaan' => $namaKepanitiaan,
            'nomor_sk' => $nomorSk,
            'tahun_akademik' => $tahunAkademik,
            'tanggal_sk' => $tanggalSk,
            'file_sk' => $storedPath,
            'keterangan' => $request->input('keterangan'),
            'hasil_ekstraksi' => $members,
            'total_anggota' => $totalAnggota,
            'total_matched' => 0,
            'status' => 'Aktif',
            'user_id' => Auth::id(),
        ]);

        // Sync matched users to their dynamic jabatan_kepanitiaan attribute
        foreach ($members as $item) {
            if (!empty($item['matched']) && !empty($item['user_id'])) {
                $user = User::find($item['user_id']);
                if ($user) {
                    $user->addJabatanKepanitiaan($namaKepanitiaan, $item['posisi']);
                    $totalMatched++;
                }
            }
        }

        $sk->update(['total_matched' => $totalMatched]);

        Alert::success('Berhasil', "SK Kepanitiaan berhasil diproses. Terdeteksi {$totalAnggota} personel ({$totalMatched} otomatis terhubung ke akun pegawai).")
            ->toToast()
            ->autoClose(5000)
            ->timerProgressBar();

        return redirect()->route('sk-kepanitiaan.show', $sk->id);
    }

    /**
     * Show detail of SK Kepanitiaan and its extracted personnel.
     */
    public function show($id)
    {
        $this->authorizeAdmin();
        $sk = SkKepanitiaan::findOrFail($id);
        $users = User::where('status', 'Aktif')->orWhereNull('status')->orderBy('name')->get(['id', 'name', 'jabatan', 'unit']);

        return view('pages.sk-kepanitiaan.show', compact('sk', 'users'));
    }

    /**
     * Show form for editing SK Kepanitiaan.
     */
    public function edit($id)
    {
        $this->authorizeAdmin();
        $sk = SkKepanitiaan::findOrFail($id);

        return view('pages.sk-kepanitiaan.edit', compact('sk'));
    }

    /**
     * Update SK Kepanitiaan metadata or replace file.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();
        $sk = SkKepanitiaan::findOrFail($id);

        $request->validate([
            'nama_kepanitiaan' => 'required|string|max:255',
            'nomor_sk' => 'nullable|string|max:255',
            'tahun_akademik' => 'nullable|string|max:100',
            'tanggal_sk' => 'nullable|date',
            'status' => 'required|in:Aktif,Selesai',
            'file_sk' => 'nullable|file|mimes:pdf|max:30720',
            'keterangan' => 'nullable|string',
        ]);

        $oldName = $sk->nama_kepanitiaan;
        $newName = trim($request->input('nama_kepanitiaan'));

        $updateData = [
            'nama_kepanitiaan' => $newName,
            'nomor_sk' => $request->input('nomor_sk'),
            'tahun_akademik' => $request->input('tahun_akademik'),
            'tanggal_sk' => $request->input('tanggal_sk'),
            'status' => $request->input('status'),
            'keterangan' => $request->input('keterangan'),
        ];

        // If file replaced, re-parse and sync
        if ($request->hasFile('file_sk')) {
            if ($sk->file_sk && Storage::disk('public')->exists($sk->file_sk)) {
                Storage::disk('public')->delete($sk->file_sk);
            }

            // Remove old kepanitiaan from users
            $this->detachAllUsersFromSk($sk);

            $storedPath = $request->file('file_sk')->store('sk_kepanitiaan', 'public');
            $absolutePath = storage_path('app/public/' . $storedPath);
            $parseResult = $this->parserService->parsePdf($absolutePath);

            $members = $parseResult['members'] ?? [];
            $totalMatched = 0;

            foreach ($members as $item) {
                if (!empty($item['matched']) && !empty($item['user_id'])) {
                    $u = User::find($item['user_id']);
                    if ($u) {
                        $u->addJabatanKepanitiaan($newName, $item['posisi']);
                        $totalMatched++;
                    }
                }
            }

            $updateData['file_sk'] = $storedPath;
            $updateData['hasil_ekstraksi'] = $members;
            $updateData['total_anggota'] = count($members);
            $updateData['total_matched'] = $totalMatched;
        } elseif ($oldName !== $newName) {
            // Update the committee name on all assigned users
            $users = User::all();
            foreach ($users as $u) {
                if (!empty($u->jabatan_kepanitiaan) && is_array($u->jabatan_kepanitiaan)) {
                    $changed = false;
                    $current = $u->jabatan_kepanitiaan;
                    foreach ($current as &$item) {
                        if (strcasecmp(trim($item['nama'] ?? ''), trim($oldName)) === 0) {
                            $item['nama'] = $newName;
                            $changed = true;
                        }
                    }
                    unset($item);
                    if ($changed) {
                        $u->jabatan_kepanitiaan = $current;
                        $u->save();
                    }
                }
            }
        }

        $sk->update($updateData);

        Alert::success('Berhasil', 'Data SK Kepanitiaan berhasil diperbarui.')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('sk-kepanitiaan.index');
    }

    /**
     * Delete SK Kepanitiaan and detach committee roles from users.
     */
    public function destroy($id)
    {
        $this->authorizeAdmin();
        $sk = SkKepanitiaan::findOrFail($id);

        $this->detachAllUsersFromSk($sk);

        if ($sk->file_sk && Storage::disk('public')->exists($sk->file_sk)) {
            Storage::disk('public')->delete($sk->file_sk);
        }

        $nama = $sk->nama_kepanitiaan;
        $sk->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'SK Kepanitiaan "' . $nama . '" beserta penugasan personelnya berhasil dihapus.',
            ]);
        }

        Alert::success('Berhasil', 'SK Kepanitiaan "' . $nama . '" berhasil dihapus.')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('sk-kepanitiaan.index');
    }

    /**
     * Manually link an extracted member line to a SIMKINERJA User account.
     */
    public function linkUser(Request $request, $id)
    {
        $this->authorizeAdmin();
        $sk = SkKepanitiaan::findOrFail($id);

        $request->validate([
            'index' => 'required|integer',
            'user_id' => 'required|exists:users,id',
        ]);

        $index = (int) $request->input('index');
        $userId = (int) $request->input('user_id');
        $members = $sk->hasil_ekstraksi ?: [];

        if (!isset($members[$index])) {
            return response()->json(['success' => false, 'message' => 'Baris personel tidak ditemukan.'], 404);
        }

        $user = User::findOrFail($userId);
        $oldUserId = $members[$index]['user_id'] ?? null;

        if ($oldUserId && $oldUserId != $userId) {
            $oldUser = User::find($oldUserId);
            if ($oldUser) {
                $oldUser->removeJabatanKepanitiaan($sk->nama_kepanitiaan);
            }
        }

        // Link new user
        $members[$index]['user_id'] = $user->id;
        $members[$index]['user_name'] = $user->name;
        $members[$index]['user_jabatan'] = $user->jabatan;
        $members[$index]['user_unit'] = $user->unit;
        $members[$index]['matched'] = true;

        $user->addJabatanKepanitiaan($sk->nama_kepanitiaan, $members[$index]['posisi']);

        $totalMatched = count(array_filter($members, fn($m) => !empty($m['matched']) && !empty($m['user_id'])));

        $sk->update([
            'hasil_ekstraksi' => $members,
            'total_matched' => $totalMatched,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menghubungkan personel ke akun ' . $user->name,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'jabatan' => $user->jabatan,
                'unit' => $user->unit,
            ],
            'total_matched' => $totalMatched,
        ]);
    }

    /**
     * Unlink a member line from its user.
     */
    public function unlinkUser(Request $request, $id)
    {
        $this->authorizeAdmin();
        $sk = SkKepanitiaan::findOrFail($id);

        $request->validate([
            'index' => 'required|integer',
        ]);

        $index = (int) $request->input('index');
        $members = $sk->hasil_ekstraksi ?: [];

        if (!isset($members[$index])) {
            return response()->json(['success' => false, 'message' => 'Baris personel tidak ditemukan.'], 404);
        }

        $userId = $members[$index]['user_id'] ?? null;
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $user->removeJabatanKepanitiaan($sk->nama_kepanitiaan);
            }
        }

        $members[$index]['user_id'] = null;
        $members[$index]['user_name'] = null;
        $members[$index]['user_jabatan'] = null;
        $members[$index]['user_unit'] = null;
        $members[$index]['matched'] = false;

        $totalMatched = count(array_filter($members, fn($m) => !empty($m['matched']) && !empty($m['user_id'])));

        $sk->update([
            'hasil_ekstraksi' => $members,
            'total_matched' => $totalMatched,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tautan akun pegawai berhasil dilepas.',
            'total_matched' => $totalMatched,
        ]);
    }

    /**
     * Re-extract personnel from existing PDF file.
     */
    public function reExtract($id)
    {
        $this->authorizeAdmin();
        $sk = SkKepanitiaan::findOrFail($id);

        if (!$sk->file_sk || !Storage::disk('public')->exists($sk->file_sk)) {
            Alert::error('Gagal', 'Berkas PDF SK tidak ditemukan di server.');
            return redirect()->back();
        }

        $this->detachAllUsersFromSk($sk);

        $absolutePath = storage_path('app/public/' . $sk->file_sk);
        $parseResult = $this->parserService->parsePdf($absolutePath);

        $members = $parseResult['members'] ?? [];
        $totalMatched = 0;

        foreach ($members as $item) {
            if (!empty($item['matched']) && !empty($item['user_id'])) {
                $u = User::find($item['user_id']);
                if ($u) {
                    $u->addJabatanKepanitiaan($sk->nama_kepanitiaan, $item['posisi']);
                    $totalMatched++;
                }
            }
        }

        $sk->update([
            'hasil_ekstraksi' => $members,
            'total_anggota' => count($members),
            'total_matched' => $totalMatched,
        ]);

        Alert::success('Berhasil', "Pengecekan ulang berkas SK selesai. Ditemukan {$totalMatched} personel terhubung.")
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->back();
    }

    /**
     * Helper to detach this SK's committee position from all users.
     */
    protected function detachAllUsersFromSk(SkKepanitiaan $sk): void
    {
        $namaKep = $sk->nama_kepanitiaan;
        $users = User::all();
        foreach ($users as $u) {
            if (!empty($u->jabatan_kepanitiaan) && is_array($u->jabatan_kepanitiaan)) {
                $u->removeJabatanKepanitiaan($namaKep);
            }
        }
    }
}
