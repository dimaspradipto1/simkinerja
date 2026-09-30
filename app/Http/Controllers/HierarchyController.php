<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class HierarchyController extends Controller
{
    /**
     * Tampilkan halaman pengaturan struktur hirarki.
     */
    public function index()
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya Admin yang dapat mengatur hirarki.');
        }

        // Ambil semua user aktif beserta supervisor mereka
        $allUsers = User::where('is_active', true)
            ->orderBy('unit')
            ->orderBy('name')
            ->get(['id', 'name', 'jabatan', 'unit', 'roles', 'supervisor_id']);

        // Helper untuk kategorisasi OTK
        $categorizeLeader = function ($leader) {
            $unit = strtoupper($leader->unit ?? '');
            $jabatan = strtoupper($leader->jabatan ?? '');

            if (str_contains($unit, 'FST') || str_contains($unit, 'FAKULTAS SAINS') || str_contains($unit, 'TEKNIK') || str_contains($jabatan, 'TEKNIK') || str_contains($jabatan, 'FST') || str_contains($jabatan, 'LABORATORIUM FST')) {
                return 'fst';
            }
            if (str_contains($unit, 'FEB') || str_contains($unit, 'EKONOMI') || str_contains($unit, 'MANAJEMEN') || str_contains($unit, 'AKUNTANSI') || str_contains($jabatan, 'FEB') || str_contains($jabatan, 'EKONOMI')) {
                return 'feb';
            }
            if (str_contains($unit, 'FIKES') || str_contains($unit, 'KESEHATAN') || str_contains($unit, 'K3') || str_contains($unit, 'KESLING') || str_contains($jabatan, 'FIKES') || str_contains($jabatan, 'KESEHATAN')) {
                return 'fikes';
            }
            if (str_contains($unit, 'LPPM') || str_contains($jabatan, 'LPPM')) {
                return 'lembaga';
            }
            if (str_contains($unit, 'LPMI') || str_contains($jabatan, 'LPMI')) {
                return 'lembaga';
            }
            return 'rektorat';
        };

        // Cari semua ID yang menjadi supervisor di tabel users
        $supervisorIdsInDb = $allUsers->pluck('supervisor_id')->filter()->unique()->toArray();

        // Pimpinan adalah mereka yang memiliki bawahan ATAU memiliki jabatan struktural utama
        $pimpinan = $allUsers->filter(function ($u) use ($supervisorIdsInDb) {
            if (in_array($u->id, $supervisorIdsInDb)) {
                return true;
            }
            return $u->isPimpinanRektorat() || str_contains(strtoupper($u->jabatan ?? ''), 'DEKAN');
        });

        // Helper bobot peringkat jabatan sesuai struktur OTK UIS
        $rankWeight = function ($u) {
            $jab = strtoupper($u->jabatan ?? '');
            $unit = strtoupper($u->unit ?? '');

            // 1. Rektor
            if ($jab === 'REKTOR' || (str_contains($jab, 'REKTOR') && !str_contains($jab, 'WAKIL') && !str_contains($jab, 'KASIR') && !str_contains($jab, 'REKTORAT'))) {
                return 100;
            }

            // 2. Wakil Rektor
            if (str_contains($jab, 'WAKIL REKTOR I') && !str_contains($jab, 'II') && !str_contains($jab, 'III')) return 201;
            if (str_contains($jab, 'WAKIL REKTOR II') && !str_contains($jab, 'III')) return 202;
            if (str_contains($jab, 'WAKIL REKTOR III')) return 203;

            // 3. Kepala Biro & Lembaga Rektorat
            if (str_contains($jab, 'KA. BIRO ADMINISTRASI AKADEMIK') || str_contains($jab, 'BAAK')) return 301;
            if (str_contains($jab, 'KEPALA PERPUSTAKAAN') || str_contains($unit, 'PERPUSTAKAAN')) return 302;
            if (str_contains($jab, 'KA. BIRO ADMINISTRASI UMUM') || str_contains($jab, 'BAUK')) return 303;
            if (str_contains($jab, 'KEPALA LPTI') || str_contains($jab, 'KEPALA ICT')) return 304;
            if (str_contains($jab, 'BIRO KEMAHASISWAAN') || str_contains($jab, 'KA. BIRO KEMAHASISWAAN')) return 305;
            if (str_contains($jab, 'KA. LPPM')) return 306;
            if (str_contains($jab, 'KA. LPMI')) return 307;

            // 4. Dekan Fakultas
            if (str_contains($jab, 'DEKAN') && !str_contains($jab, 'WAKIL')) {
                if (str_contains($unit, 'EKONOMI') || str_contains($jab, 'EKONOMI')) return 401;
                if (str_contains($unit, 'SAINS') || str_contains($unit, 'FST') || str_contains($jab, 'SAINS')) return 402;
                if (str_contains($unit, 'KESEHATAN') || str_contains($unit, 'FIKES') || str_contains($jab, 'KESEHATAN')) return 403;
                return 404;
            }

            // 5. Wakil Dekan
            if (str_contains($jab, 'WAKIL DEKAN I') && !str_contains($jab, 'II')) return 501;
            if (str_contains($jab, 'WAKIL DEKAN II')) return 502;
            if (str_contains($jab, 'WAKIL DEKAN')) return 503;

            // 6. Kabid-kabid
            if (str_contains($jab, 'KABID')) return 600;

            // 7. Ketua Program Studi (Kaprodi)
            if (str_contains($jab, 'KETUA PROGRAM STUDI') || str_contains($jab, 'KAPRODI')) return 700;

            // 8. Sekretaris Program Studi (Sekprodi)
            if (str_contains($jab, 'SEKRETARIS PRODI') || str_contains($jab, 'SEKRETARIS PROGRAM STUDI')) return 800;

            // 9. Kepala Unit / Lembaga Fakultas (UPMI, UPPM, Ka. Lab, Ka. Humas)
            if (str_contains($jab, 'UPMI')) return 901;
            if (str_contains($jab, 'GKM')) return 902;
            if (str_contains($jab, 'KA. UPPM') || (str_contains($jab, 'UPPM') && !str_contains($jab, 'STAFF'))) return 903;
            if (str_contains($jab, 'KA. LABORATORIUM') || str_contains($jab, 'KA. LAB')) return 904;
            if (str_contains($jab, 'KA. HUMAS')) return 905;

            // 10. Tata Usaha & Kasir
            if (str_contains($jab, 'TATA USAHA') || str_contains($jab, 'KASIR')) return 1000;

            // 11. Pustakawan & Laboran
            if (str_contains($jab, 'PUSTAKAWAN')) return 1100;
            if (str_contains($jab, 'LABORAN') || str_contains($jab, 'STAFF LABOR')) return 1101;

            // 12. Staff
            if (str_contains($jab, 'STAFF') || str_contains($jab, 'OPERATOR') || str_contains($jab, 'ADMIN') || str_contains($jab, 'DIVISI') || str_contains($jab, 'PROGRAMMER')) return 1200;

            return 2000;
        };

        // Urutkan pimpinan secara hirarkis mulai dari Rektor, Wakil Rektor, Ka. Biro, Dekan, dst.
        $pimpinan = $pimpinan->sortBy($rankWeight)->values();

        // Bangun tree: setiap pimpinan → daftar bawahannya beserta kategorinya (bawahan juga diurutkan berdasarkan peringkat jabatan)
        $hierarchyTree = $pimpinan->map(function ($leader) use ($allUsers, $categorizeLeader, $rankWeight) {
            $subs = $allUsers->where('supervisor_id', $leader->id)->sortBy($rankWeight)->values();
            return [
                'leader'       => $leader,
                'subordinates' => $subs,
                'category'     => $categorizeLeader($leader),
            ];
        })->values();

        // Pegawai yang belum punya supervisor dan memiliki jabatan struktural resmi
        $unassigned = $allUsers->filter(function ($u) {
            $isTopLeader = str_contains(strtoupper($u->jabatan ?? ''), 'REKTOR') && !str_contains(strtoupper($u->jabatan ?? ''), 'WAKIL');
            $hasJabatanStruktural = !empty($u->jabatan) && $u->jabatan !== '-' && !empty($u->unit) && $u->unit !== '-';
            return is_null($u->supervisor_id) && !$isTopLeader && !$u->isAdmin() && !$u->isSuperAdmin() && $hasJabatanStruktural;
        })->values();

        return view('pages.hirarki.index', compact(
            'allUsers',
            'pimpinan',
            'hierarchyTree',
            'unassigned'
        ));
    }

    /**
     * Terapkan sinkronisasi otomatis sesuai dokumen resmi OTK UIS 2024-2029 (AJAX).
     */
    public function syncOtk()
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        try {
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\HierarchyOtkSeeder']);
            $assignedCount = User::whereNotNull('supervisor_id')->count();

            return response()->json([
                'success' => true,
                'message' => "Struktur Hirarki berhasil disinkronisasi sesuai OTK UIS 2024-2029! ({$assignedCount} pegawai terhubung).",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronisasi OTK: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update supervisor_id untuk satu user (AJAX).
     */
    public function update(Request $request, User $user)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'supervisor_id' => 'nullable|exists:users,id',
        ]);

        // Cegah circular reference: supervisor tidak boleh menjadi bawahan dari user itu sendiri
        $newSupervisorId = $request->input('supervisor_id');
        if ($newSupervisorId && (int)$newSupervisorId === $user->id) {
            return response()->json(['success' => false, 'message' => 'User tidak dapat menjadi atasan dirinya sendiri.'], 422);
        }

        $user->supervisor_id = $newSupervisorId ?: null;
        $user->save();

        $supervisorName = $newSupervisorId
            ? User::find($newSupervisorId)?->name ?? '-'
            : 'Tidak ada (bebas)';

        return response()->json([
            'success' => true,
            'message' => "Atasan langsung {$user->name} berhasil diperbarui menjadi: {$supervisorName}",
        ]);
    }

    /**
     * Bulk update: assign semua bawahan dari satu pimpinan sekaligus (AJAX).
     */
    public function bulkAssign(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'supervisor_id'    => 'required|exists:users,id',
            'subordinate_ids'  => 'required|array',
            'subordinate_ids.*'=> 'exists:users,id',
        ]);

        $supervisorId    = $request->supervisor_id;
        $subordinateIds  = $request->subordinate_ids;

        // Cegah supervisor menjadi bawahannya sendiri
        if (in_array((int) $supervisorId, array_map('intval', $subordinateIds))) {
            return response()->json(['success' => false, 'message' => 'Pimpinan tidak dapat menjadi bawahannya sendiri.'], 422);
        }

        User::whereIn('id', $subordinateIds)->update(['supervisor_id' => $supervisorId]);

        return response()->json([
            'success' => true,
            'message' => count($subordinateIds) . ' pegawai berhasil ditetapkan sebagai bawahan.',
        ]);
    }

    /**
     * Reset (hapus) supervisor_id untuk user tertentu (AJAX).
     */
    public function reset(User $user)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $user->supervisor_id = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Hirarki atasan langsung {$user->name} berhasil direset.",
        ]);
    }

    /**
     * Reset semua hirarki (AJAX - hati-hati!).
     */
    public function resetAll()
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        User::whereNotNull('supervisor_id')->update(['supervisor_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Seluruh data hirarki berhasil direset.',
        ]);
    }
}
