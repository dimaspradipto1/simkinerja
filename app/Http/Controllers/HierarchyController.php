<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HierarchyController extends Controller
{
    /**
     * Tampilkan halaman pengaturan struktur OTK.
     */
    public function index()
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya Admin yang dapat mengatur OTK.');
        }

        // Ambil semua user aktif beserta supervisor mereka
        $allUsers = User::where('is_active', true)
            ->orderBy('unit')
            ->orderBy('name')
            ->get(['id', 'name', 'jabatan', 'unit', 'roles', 'supervisor_id']);
        $otkSyncState = DB::table('otk_sync_state')->where('id', 1)->value('synced_at');
        $otkTitles = DB::table('otk_sync_state')
            ->where('id', 1)
            ->first(['title_line_1', 'title_line_2']);
        $otkOrderById = DB::table('otk_members')->pluck('sort_order', 'user_id')->all();
        $otkMemberIds = array_keys($otkOrderById);
        $availableUsers = $otkSyncState !== null
            ? User::query()
                ->where('is_active', true)
                ->whereNotIn('id', $otkMemberIds)
                ->orderBy('name')
                ->get(['id', 'name', 'jabatan', 'unit'])
            : collect();
        if ($otkSyncState !== null) {
            $allUsers = $allUsers->whereIn('id', $otkMemberIds)
                ->sortBy(fn ($user) => $otkOrderById[$user->id] ?? PHP_INT_MAX)
                ->values();
        }

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
        $rankWeight = function ($u) use ($otkSyncState, $otkOrderById) {
            if ($otkSyncState !== null && isset($otkOrderById[$u->id])) {
                return $otkOrderById[$u->id];
            }

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
            if (str_contains($jab, 'KA. BIRO ADMINISTRASI UMUM')
                || (str_contains($jab, 'BAUK') && !str_contains($jab, 'TATA USAHA'))) return 303;
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

            if (str_contains($jab, 'KABID. AKADEMIK')) return 601;
            if (str_contains($jab, 'KABID. ADMINISTRASI')) return 602;
            if (str_contains($jab, 'KABID. KEUANGAN')) return 603;
            if (str_contains($jab, 'KABID. SDM')) return 604;
            if (str_contains($jab, 'KABID. SARANA')) return 605;

            // 6. Kabid-kabid
            if (str_contains($jab, 'KABID. HUMAS')) return 610;
            if (str_contains($jab, 'KABID. KERJASAMA')) return 620;
            if (str_contains($jab, 'KABID. KEMAHASISWAAN')) return 630;
            if (str_contains($jab, 'PUSAT KARIR')) return 640;
            if (str_contains($jab, 'PERENCANAAN DAN PENGEMBANGAN')) return 650;
            if (str_contains($jab, 'KABID')) return 660;

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

        $isReportableUser = fn ($user) => !in_array(strtolower(trim($user->email ?? '')), [
                'admin@uis.ac.id',
                'admin@gmail.com',
                'superadmin@uis.ac.id',
            ], true)
            && !in_array(strtolower(trim($user->name ?? '')), ['admin', 'superadmin'], true)
            && !empty($user->jabatan) && $user->jabatan !== '-';

        $findPosition = fn (string $position) => $allUsers->first(
            fn ($user) => strtoupper(trim($user->jabatan ?? '')) === strtoupper($position)
        );

        $descendantsOf = function ($leader) use ($allUsers, $rankWeight) {
            $descendants = collect();
            $visitedIds = [$leader->id => true];
            $walk = function ($parentId) use (&$walk, &$visitedIds, $descendants, $allUsers, $rankWeight) {
                foreach ($allUsers->where('supervisor_id', $parentId)->sortBy($rankWeight) as $child) {
                    if (isset($visitedIds[$child->id])) {
                        continue;
                    }

                    $visitedIds[$child->id] = true;
                    $descendants->push($child);
                    $walk($child->id);
                }
            };
            $walk($leader->id);

            return $descendants;
        };

        $childrenGroups = function ($leader) use ($allUsers, $descendantsOf, $rankWeight, $isReportableUser) {
            return $allUsers->where('supervisor_id', $leader->id)
                ->sortBy($rankWeight)
                ->map(function ($child) use ($descendantsOf, $rankWeight, $isReportableUser) {
                    return [
                        'label' => $child->unit ?: $child->jabatan,
                        'employees' => collect([$child])
                            ->merge($descendantsOf($child))
                            ->filter($isReportableUser)
                            ->unique('id')
                            ->sortBy($rankWeight)
                            ->values(),
                    ];
                })
                ->values();
        };

        $rektor = $findPosition('REKTOR');
        $viceRectors = $allUsers
            ->filter(fn ($user) => preg_match('/^WAKIL REKTOR (I|II|III)$/i', trim($user->jabatan ?? '')))
            ->sortBy($rankWeight)
            ->values();

        $organizationSections = [[
            'number' => 1,
            'unit' => 'PIMPINAN UNIVERSITAS IBNU SINA (UIS)',
            'responsibility' => null,
            'coordinators' => [[
                'label' => null,
                'implementers' => [[
                    'label' => null,
                    'employees' => collect([$rektor])->filter()->merge($viceRectors)->filter($isReportableUser)->sortBy($rankWeight)->values(),
                ]],
            ]],
        ]];

        $centralCoordinators = [];
        foreach (['I', 'II', 'III'] as $number) {
            $coordinator = $findPosition("WAKIL REKTOR {$number}");
            if (!$coordinator) {
                continue;
            }

            $implementers = $childrenGroups($coordinator);
            $implementers = $implementers
                ->groupBy(function ($group) {
                    $unit = strtoupper($group['label'] ?? '');

                    return match (true) {
                        str_contains($unit, 'PERPUSTAKAAN') => 'PERPUSTAKAAN',
                        str_contains($unit, 'LPPM') => 'LEMBAGA PENELITIAN DAN PENGABDIAN KEPADA MASYARAKAT',
                        str_contains($unit, 'LPMI') => 'SISTEM PENJAMINAN MUTU INTERNAL',
                        str_contains($unit, 'LPTI'), str_contains($unit, 'ICT') => 'UPT TEKNIK INFORMASI DAN KOMUNIKASI',
                        default => 'UNSUR PELAKSANA ADMINISTRASI',
                    };
                })
                ->map(fn ($groups, $label) => [
                    'label' => $label,
                    'employees' => $groups->flatMap(fn ($group) => $group['employees'])
                        ->unique('id')
                        ->sortBy($rankWeight)
                        ->values(),
                ])
                ->values();

            if ($implementers->isNotEmpty()) {
                $centralCoordinators[] = [
                    'label' => "WAKIL REKTOR {$number}",
                    'implementers' => $implementers,
                ];
            }
        }

        $organizationSections[] = [
            'number' => null,
            'unit' => 'REKTORAT UIS',
            'responsibility' => null,
            'coordinators' => $centralCoordinators,
        ];

        $facultyDefinitions = [
            [
                'number' => 2,
                'unit' => 'FAKULTAS EKONOMI DAN BISNIS',
                'match' => ['FAKULTAS EKONOMI DAN BISNIS', 'PRODI S1 MANAJEMEN', 'PRODI S1 AKUNTANSI', 'PRODI S2 MANAJEMEN'],
                'order' => [
                    'DEKAN' => 10,
                    'WAKIL DEKAN I' => 20,
                    'WAKIL DEKAN II' => 30,
                    'KETUA PROGRAM STUDI S1 MANAJEMEN' => 40,
                    'SEKRETARIS PRODI S1 MANAJEMEN' => 41,
                    'KETUA PROGRAM STUDI S1 AKUNTANSI' => 50,
                    'SEKRETARIS PRODI S1 AKUNTANSI' => 51,
                    'KETUA PROGRAM STUDI PASCASARJANA' => 60,
                    'SEKRETARIS PRODI PASCASARJANA' => 61,
                    'UPMI' => 70,
                    'UPPM' => 80,
                    'TATA USAHA SARJANA' => 90,
                    'TATA USAHA PASCASARJANA' => 100,
                    'HUMAS DAN PUBLIKASI' => 110,
                ],
            ],
            [
                'number' => 3,
                'unit' => 'FAKULTAS SAINS DAN TEKNOLOGI',
                'match' => [
                    'FAKULTAS SAINS DAN TEKNOLOGI',
                    'FAKULTAS TEKNIK',
                    'PRODI TEKNIK INDUSTRI',
                    'PRODI TEKNIK INFORMATIKA',
                    'PRODI TEKNIK LOGISTIK',
                    'PRODI TEKNIK LOGISTIK DAN PERKAPALAN',
                    'LABORATORIUM FST',
                ],
                'order' => [
                    'DEKAN' => 10,
                    'WAKIL DEKAN I' => 20,
                    'WAKIL DEKAN II' => 30,
                    'KETUA PROGRAM STUDI TEKNIK INDUSTRI' => 40,
                    'SEKRETARIS PROGRAM STUDI TEKNIK INDUSTRI' => 41,
                    'KETUA PROGRAM STUDI TEKNIK INFORMATIKA' => 50,
                    'SEKRETARIS PROGRAM STUDI TEKNIK INFORMATIKA' => 51,
                    'KETUA PROGRAM STUDI TEKNIK LOGISTIK' => 60,
                    'SEKRETARIS PROGRAM STUDI TEKNIK LOGISTIK' => 61,
                    'TATA USAHA' => 70,
                    'UPMI' => 80,
                    'KA. UPPM' => 90,
                    'STAFF UPPM' => 91,
                    'KA. LABORATORIUM' => 100,
                    'KA. LAB' => 100,
                    'STAFF LABOR' => 101,
                    'KA. HUMAS' => 110,
                    'STAFF HUMAS' => 111,
                ],
            ],
            [
                'number' => 4,
                'unit' => 'FAKULTAS ILMU KESEHATAN',
                'match' => ['FAKULTAS ILMU KESEHATAN', 'PRODI K3', 'PRODI KESLING'],
                'order' => [
                    'DEKAN' => 10,
                    'WAKIL DEKAN I' => 20,
                    'WAKIL DEKAN II' => 30,
                    'KETUA PROGRAM STUDI K3' => 40,
                    'SEKRETARIS PRODI K3' => 41,
                    'KETUA PROGRAM STUDI KESLING' => 50,
                    'SEKRETARIS PRODI KESLING' => 51,
                    'UPMI' => 60,
                    'GKM' => 70,
                    'UPPM' => 80,
                    'LABORAN' => 90,
                    'TATA USAHA' => 100,
                    'HUMAS' => 110,
                ],
            ],
        ];

        foreach ($facultyDefinitions as $faculty) {
            $dean = $allUsers->first(fn ($user) => str_contains(strtoupper($user->jabatan ?? ''), 'DEKAN')
                && !str_contains(strtoupper($user->jabatan ?? ''), 'WAKIL')
                && in_array(strtoupper(trim($user->unit ?? '')), $faculty['match'], true));
            $employees = collect([$dean])
                ->filter()
                ->merge($dean ? $descendantsOf($dean) : collect())
                ->filter($isReportableUser)
                ->unique('id')
                ->sortBy(function ($user) use ($faculty, $otkSyncState, $otkOrderById) {
                    if ($otkSyncState !== null && isset($otkOrderById[$user->id])) {
                        return $otkOrderById[$user->id];
                    }

                    $position = strtoupper($user->jabatan ?? '');
                    $priority = 999;
                    foreach ($faculty['order'] as $marker => $weight) {
                        if (str_contains($position, $marker)
                            && !($marker === 'DEKAN' && str_contains($position, 'WAKIL'))
                            && !($marker === 'WAKIL DEKAN I' && str_contains($position, 'WAKIL DEKAN II'))) {
                            $priority = $weight;
                            break;
                        }
                    }

                    return $priority * 1000 + $user->id;
                })
                ->values();

            $organizationSections[] = [
                'number' => $faculty['number'],
                'unit' => $faculty['unit'],
                'responsibility' => 'TANGGUNG JAWAB REKTOR',
                'coordinators' => [[
                    'label' => 'KOORDINASI MELALUI WAKIL REKTOR I',
                    'implementers' => [[
                        'label' => 'UNSUR PELAKSANA AKADEMIK',
                        'employees' => $employees,
                    ]],
                ]],
            ];
        }

        return view('pages.hirarki.otk-table', compact(
            'allUsers',
            'availableUsers',
            'pimpinan',
            'hierarchyTree',
            'unassigned',
            'organizationSections',
            'otkTitles'
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
                'message' => "Struktur OTK berhasil disinkronisasi sesuai OTK UIS 2024-2029! ({$assignedCount} pegawai terhubung).",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronisasi OTK: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function uploadOtk(Request $request, \App\Services\OtkPdfSyncService $otkPdfSyncService)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'file_sk' => 'required|file|mimes:pdf|max:30720',
        ], [
            'file_sk.required' => 'File SK OTK wajib diunggah.',
            'file_sk.mimes' => 'Format file SK OTK harus PDF.',
            'file_sk.max' => 'Ukuran file maksimal 30 MB.',
        ]);

        try {
            $result = $otkPdfSyncService->sync($validated['file_sk']->getRealPath());

            return response()->json([
                'success' => true,
                'message' => "SK OTK berhasil disinkronkan. {$result['updated_count']} data jabatan, unit, dan anggota OTK diperbarui.",
            ]);
        } catch (\RuntimeException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyinkronkan SK OTK. Periksa log aplikasi untuk detail kesalahan.',
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
            'name' => 'sometimes|required|string|max:255',
            'jabatan' => 'sometimes|required|string|max:255',
            'unit' => 'sometimes|required|string|max:255',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);

        $newSupervisorId = $request->input('supervisor_id');
        if ($this->supervisorWouldCreateCycle($user, $newSupervisorId)) {
            return response()->json([
                'success' => false,
                'message' => 'Atasan yang dipilih akan membuat siklus dalam struktur OTK.',
            ], 422);
        }

        $user->fill($request->only(['name', 'jabatan', 'unit']));
        $user->supervisor_id = $newSupervisorId ?: null;
        $user->save();

        $supervisorName = $newSupervisorId
            ? User::find($newSupervisorId)?->name ?? '-'
            : 'Tidak ada (bebas)';

        return response()->json([
            'success' => true,
            'message' => "Data OTK dan akun Pengguna {$user->name} berhasil diperbarui. Atasan langsung: {$supervisorName}.",
        ]);
    }

    public function addExistingUserToOtk(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin() && !$authUser->subordinates()->exists())) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'jabatan' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'supervisor_id' => 'nullable|integer|exists:users,id',
        ]);

        $user = User::query()
            ->whereKey($validated['user_id'])
            ->where('is_active', true)
            ->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak ditemukan atau tidak aktif.',
            ], 422);
        }

        if (DB::table('otk_members')->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tersebut sudah terdaftar sebagai anggota OTK.',
            ], 422);
        }

        $supervisorId = !empty($validated['supervisor_id']) ? (int) $validated['supervisor_id'] : null;
        if ($supervisorId) {
            $supervisor = User::query()
                ->whereKey($supervisorId)
                ->where('is_active', true)
                ->first();
            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Atasan tidak ditemukan atau tidak aktif.',
                ], 422);
            }

            if ($this->supervisorWouldCreateCycle($user, $supervisor->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Atasan yang dipilih akan membuat siklus dalam struktur OTK.',
                ], 422);
            }
        }

        DB::transaction(function () use ($user, $supervisorId, $validated): void {
            if (!empty($validated['jabatan'])) {
                $user->jabatan = trim($validated['jabatan']);
            }
            if (!empty($validated['unit'])) {
                $user->unit = trim($validated['unit']);
            }
            $user->supervisor_id = $supervisorId;
            $user->save();

            DB::table('otk_members')->insert([
                'user_id' => $user->id,
                'sort_order' => (int) DB::table('otk_members')->max('sort_order') + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('otk_sync_state')->updateOrInsert(['id' => 1], ['synced_at' => now()]);
        });

        return response()->json([
            'success' => true,
            'message' => "{$user->name} berhasil ditambahkan ke OTK dan data Pengguna diperbarui.",
        ]);
    }

    /**
     * Pindahkan posisi baris OTK (Naik / Turun).
     */
    public function moveOtkRow(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin() && !$authUser->subordinates()->exists())) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'direction' => 'required|in:up,down',
            'swap_user_id' => 'nullable|integer|exists:users,id',
        ]);

        $userId = (int) $validated['user_id'];
        $direction = $validated['direction'];
        $swapUserId = !empty($validated['swap_user_id']) ? (int) $validated['swap_user_id'] : null;

        $currentRow = DB::table('otk_members')->where('user_id', $userId)->first();
        if (!$currentRow) {
            $maxOrder = (int) DB::table('otk_members')->max('sort_order');
            DB::table('otk_members')->insert([
                'user_id' => $userId,
                'sort_order' => $maxOrder + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $currentRow = DB::table('otk_members')->where('user_id', $userId)->first();
        }

        $swapRow = null;
        if ($swapUserId) {
            $swapRow = DB::table('otk_members')->where('user_id', $swapUserId)->first();
            if (!$swapRow) {
                $maxOrder = (int) DB::table('otk_members')->max('sort_order');
                DB::table('otk_members')->insert([
                    'user_id' => $swapUserId,
                    'sort_order' => $direction === 'up' ? max(0, (int)$currentRow->sort_order - 1) : (int)$currentRow->sort_order + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $swapRow = DB::table('otk_members')->where('user_id', $swapUserId)->first();
            }
        }

        if (!$swapRow) {
            $currentSortOrder = (int) $currentRow->sort_order;
            if ($direction === 'up') {
                $swapRow = DB::table('otk_members')
                    ->where('sort_order', '<', $currentSortOrder)
                    ->orderBy('sort_order', 'desc')
                    ->first();
            } else {
                $swapRow = DB::table('otk_members')
                    ->where('sort_order', '>', $currentSortOrder)
                    ->orderBy('sort_order', 'asc')
                    ->first();
            }
        }

        if (!$swapRow) {
            return response()->json([
                'success' => false,
                'message' => $direction === 'up' ? 'Baris sudah berada di posisi paling atas.' : 'Baris sudah berada di posisi paling bawah.',
            ], 422);
        }

        DB::transaction(function () use ($currentRow, $swapRow) {
            $orderA = (int) $currentRow->sort_order;
            $orderB = (int) $swapRow->sort_order;

            if ($orderA === $orderB) {
                $orderB = $orderA + 1;
            }

            $tempOrder = (int) DB::table('otk_members')->max('sort_order') + 100;
            DB::table('otk_members')->where('user_id', $currentRow->user_id)->update(['sort_order' => $tempOrder]);
            DB::table('otk_members')->where('user_id', $swapRow->user_id)->update(['sort_order' => $orderA]);
            DB::table('otk_members')->where('user_id', $currentRow->user_id)->update(['sort_order' => $orderB]);

            DB::table('otk_sync_state')->updateOrInsert(['id' => 1], ['synced_at' => now()]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Posisi baris berhasil dipindahkan ' . ($direction === 'up' ? 'ke atas.' : 'ke bawah.'),
        ]);
    }

    /**
     * Sisipkan baris baru (di atas atau di bawah baris acuan tertentu).
     */
    public function insertOtkRow(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin() && !$authUser->subordinates()->exists())) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'reference_user_id' => 'required|integer|exists:users,id',
            'position' => 'required|in:before,after',
            'user_mode' => 'nullable|string',
            'existing_user_id' => 'nullable|integer',
            'new_name' => 'nullable|string|max:255',
            'new_email' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);

        $refUser = User::findOrFail($validated['reference_user_id']);
        $refRow = DB::table('otk_members')->where('user_id', $refUser->id)->first();
        if (!$refRow) {
            $refSortOrder = (int) DB::table('otk_members')->max('sort_order') + 1;
        } else {
            $refSortOrder = (int) $refRow->sort_order;
        }

        $targetSortOrder = ($validated['position'] === 'before')
            ? $refSortOrder
            : ($refSortOrder + 1);

        $insertedUser = null;
        DB::transaction(function () use ($validated, $targetSortOrder, &$insertedUser) {
            $jabatanVal = !empty($validated['jabatan']) ? trim($validated['jabatan']) : '-';
            $unitVal = !empty($validated['unit']) ? trim($validated['unit']) : '-';
            $supervisorIdVal = !empty($validated['supervisor_id']) ? $validated['supervisor_id'] : null;

            if (!empty($validated['existing_user_id']) && $existing = User::find($validated['existing_user_id'])) {
                $insertedUser = $existing;
                if (!empty($validated['jabatan']) && $validated['jabatan'] !== '-') {
                    $insertedUser->jabatan = $jabatanVal;
                }
                if (!empty($validated['unit']) && $validated['unit'] !== '-') {
                    $insertedUser->unit = $unitVal;
                }
                $insertedUser->supervisor_id = $supervisorIdVal;
                $insertedUser->save();

                DB::table('otk_members')->where('user_id', $insertedUser->id)->delete();
            } else {
                // Mode bypass / pengguna baru / kosong
                $nameVal = !empty($validated['new_name']) ? trim($validated['new_name']) : '-';
                $randomSuffix = time() . '_' . mt_rand(100, 999);
                $emailVal = !empty($validated['new_email']) ? trim($validated['new_email']) : ('otk_' . $randomSuffix . '@uis.ac.id');
                if (User::where('email', $emailVal)->exists()) {
                    $emailVal = 'otk_' . $randomSuffix . '_' . uniqid() . '@uis.ac.id';
                }

                $insertedUser = User::create([
                    'name' => $nameVal,
                    'email' => $emailVal,
                    'password' => bcrypt('password123'),
                    'jabatan' => $jabatanVal,
                    'unit' => $unitVal,
                    'supervisor_id' => $supervisorIdVal,
                    'is_active' => true,
                    'roles' => 'staff',
                    'status' => 'Aktif',
                ]);
            }

            // Geser baris di bawahnya
            DB::table('otk_members')
                ->where('sort_order', '>=', $targetSortOrder)
                ->increment('sort_order');

            // Masukkan pada posisi target
            DB::table('otk_members')->insert([
                'user_id' => $insertedUser->id,
                'sort_order' => $targetSortOrder,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Pastikan otk_sync_state ada nilainya
            DB::table('otk_sync_state')->updateOrInsert(['id' => 1], ['synced_at' => now()]);
        });

        return response()->json([
            'success' => true,
            'message' => "Baris berhasil disisipkan untuk {$insertedUser->name}.",
        ]);
    }

    /**
     * Hapus baris dari tabel penempatan OTK.
     */
    public function deleteOtkRow(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin() && !$authUser->subordinates()->exists())) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $userId = (int) $validated['user_id'];
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
        }

        $exists = DB::table('otk_members')->where('user_id', $userId)->exists();
        if (!$exists) {
            return response()->json(['success' => false, 'message' => 'Baris tidak ditemukan pada daftar OTK.'], 404);
        }

        DB::transaction(function () use ($userId) {
            DB::table('otk_members')->where('user_id', $userId)->delete();

            // Normalisasi urutan sekuensial
            $all = DB::table('otk_members')->orderBy('sort_order', 'asc')->get();
            foreach ($all as $idx => $m) {
                DB::table('otk_members')->where('user_id', $m->user_id)->update(['sort_order' => $idx]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Baris {$user->name} berhasil dihapus dari penempatan OTK.",
        ]);
    }

    public function updateOtkTitles(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'title_line_1' => 'required|string|max:255',
            'title_line_2' => 'required|string|max:255',
        ]);

        DB::table('otk_sync_state')
            ->where('id', 1)
            ->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Judul OTK berhasil diperbarui.',
        ]);
    }

    private function supervisorWouldCreateCycle(User $user, ?int $supervisorId): bool
    {
        $ancestorIds = [];
        $currentSupervisorId = (int) $supervisorId;
        while ($currentSupervisorId !== 0) {
            if ($currentSupervisorId === $user->id) {
                return true;
            }
            if (isset($ancestorIds[$currentSupervisorId])) {
                return false;
            }

            $ancestorIds[$currentSupervisorId] = true;
            $currentSupervisorId = (int) User::whereKey($currentSupervisorId)->value('supervisor_id');
        }

        return false;
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
            'message' => "Relasi atasan langsung OTK {$user->name} berhasil direset.",
        ]);
    }

    /**
     * Reset semua relasi OTK (AJAX - hati-hati!).
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
            'message' => 'Seluruh data OTK berhasil direset.',
        ]);
    }
}
