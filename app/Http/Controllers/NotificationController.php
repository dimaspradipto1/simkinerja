<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\RencanaKerja;
use App\Models\Kepanitiaan;
use App\Models\Kegiatan;
use App\Models\Insidentil;

class NotificationController extends Controller
{
    /**
     * Gather all active notifications for the given user.
     *
     * @param  \App\Models\User|null  $authUser
     * @return \Illuminate\Support\Collection
     */
    public static function fetchData($authUser)
    {
        $notifications = collect();

        if (!$authUser) {
            return $notifications;
        }

        $today = Carbon::now()->format('Y-m-d');

        // 1. Permintaan Perubahan Estimasi dari bawahan (khusus Kepala Unit / Pimpinan & Admin, tidak ke Rektor)
        if (!$authUser->isRektor() && ($authUser->isAdmin() || $authUser->isPimpinanUnit())) {
            $unlockSources = [
                [
                    'model' => RencanaKerja::class,
                    'label' => 'Rencana Kerja',
                    'approve_route' => 'rencana-kerja.approve-extension',
                    'index_route' => 'rencana-kerja.index'
                ],
                [
                    'model' => Kepanitiaan::class,
                    'label' => 'Kepanitiaan',
                    'approve_route' => 'kepanitiaan.approve-extension',
                    'index_route' => 'kepanitiaan.index'
                ],
                [
                    'model' => Insidentil::class,
                    'label' => 'Insidentil',
                    'approve_route' => 'insidentil.approve-extension',
                    'index_route' => 'insidentil.index'
                ],
                [
                    'model' => Kegiatan::class,
                    'label' => 'Kegiatan',
                    'approve_route' => 'kegiatan.approve-extension',
                    'index_route' => 'kegiatan.index'
                ],
            ];

            foreach ($unlockSources as $src) {
                $unlockQuery = $src['model']::whereNotNull('estimasi_unlock_requested_at')->with('user');

                if (!$authUser->isAdmin()) {
                    $unlockQuery->where(function ($q) use ($authUser) {
                        $q->whereHas('user', function ($qu) use ($authUser) {
                            $qu->where('unit', $authUser->unit);
                        })->orWhere('estimasi_unlock_reason', 'like', '%[Tujuan: ' . $authUser->name . '%');
                    });
                }

                $unlockRequests = $unlockQuery->latest('estimasi_unlock_requested_at')->take(5)->get();

                foreach ($unlockRequests as $r) {
                    $notifications->push([
                        'id' => $r->id,
                        'type' => 'extension_request',
                        'action_type' => 'modal_approve',
                        'icon' => 'bi-unlock-fill text-warning',
                        'bg_color' => '#fef3c7',
                        'badge' => 'Persetujuan',
                        'badge_class' => 'bg-warning text-dark',
                        'title' => 'Permintaan Perubahan Estimasi (' . $src['label'] . ')',
                        'message' => ($r->user->name ?? '-') . ': ' . ($r->uraian_tugas ?? $r->nama_kegiatan ?? 'Tugas'),
                        'time' => 'Diajukan ' . Carbon::parse($r->estimasi_unlock_requested_at)->diffForHumans(),
                        'uraian' => $r->uraian_tugas ?? $r->nama_kegiatan ?? '-',
                        'pegawai' => $r->user->name ?? '-',
                        'reason' => $r->estimasi_unlock_reason ?? '-',
                        'tgl_selesai' => $r->estimasi_tanggal_selesai ?? date('Y-m-d'),
                        'jam_selesai' => $r->estimasi_jam_selesai ? substr($r->estimasi_jam_selesai, 0, 5) : '23:59',
                        'route' => route($src['approve_route'], $r->id),
                        'url' => route($src['index_route']),
                        'timestamp' => Carbon::parse($r->estimasi_unlock_requested_at)->timestamp,
                    ]);
                }
            }
        }

        // 2. Pemberitahuan Perpanjangan Disetujui (untuk pegawai yang meminta perpanjangan)
        $approvedSources = [
            ['model' => RencanaKerja::class, 'label' => 'Rencana Kerja', 'route' => 'rencana-kerja.index'],
            ['model' => Kepanitiaan::class, 'label' => 'Kepanitiaan', 'route' => 'kepanitiaan.index'],
            ['model' => Insidentil::class, 'label' => 'Insidentil', 'route' => 'insidentil.index'],
            ['model' => Kegiatan::class, 'label' => 'Kegiatan', 'route' => 'kegiatan.index'],
        ];

        foreach ($approvedSources as $src) {
            $approvedTasks = $src['model']::where('user_id', $authUser->id)
                ->whereNotNull('estimasi_unlock_approved_at')
                ->where('estimasi_unlock_approved_at', '>=', Carbon::now()->subDays(4))
                ->latest('estimasi_unlock_approved_at')
                ->take(3)
                ->get();

            foreach ($approvedTasks as $t) {
                $notifications->push([
                    'id' => $t->id,
                    'type' => 'extension_approved',
                    'action_type' => 'navigate',
                    'icon' => 'bi-check-circle-fill text-success',
                    'bg_color' => '#dcfce7',
                    'badge' => 'Disetujui',
                    'badge_class' => 'bg-success text-white',
                    'title' => 'Perpanjangan Waktu Disetujui (' . $src['label'] . ')',
                    'message' => 'Estimasi tugas "' . Str::limit($t->uraian_tugas ?? $t->nama_kegiatan, 40) . '" disetujui s/d ' . ($t->estimasi_tanggal_selesai ? date('d/m/Y', strtotime($t->estimasi_tanggal_selesai)) : '-'),
                    'time' => Carbon::parse($t->estimasi_unlock_approved_at)->diffForHumans(),
                    'url' => route($src['route']),
                    'timestamp' => Carbon::parse($t->estimasi_unlock_approved_at)->timestamp,
                ]);
            }
        }

        // 3. Tugas Melewati Deadline (Overdue Tasks)
        $overdueTasks = RencanaKerja::where(function ($q) use ($authUser) {
                $q->where('user_id', $authUser->id)
                  ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                      $qu->where('users.id', $authUser->id);
                  });
            })
            ->where('status', '!=', 'Selesai')
            ->whereNotNull('estimasi_tanggal_selesai')
            ->where('estimasi_tanggal_selesai', '<', $today)
            ->latest('estimasi_tanggal_selesai')
            ->take(3)
            ->get();

        foreach ($overdueTasks as $t) {
            $notifications->push([
                'id' => $t->id,
                'type' => 'overdue',
                'action_type' => 'navigate',
                'icon' => 'bi-exclamation-triangle-fill text-danger',
                'bg_color' => '#fee2e2',
                'badge' => 'Terlambat',
                'badge_class' => 'bg-danger text-white',
                'title' => 'Tugas Melewati Deadline',
                'message' => Str::limit($t->uraian_tugas, 45),
                'time' => 'Batas: ' . date('d/m/Y', strtotime($t->estimasi_tanggal_selesai)),
                'url' => route('rencana-kerja.index'),
                'timestamp' => Carbon::parse($t->estimasi_tanggal_selesai)->timestamp,
            ]);
        }

        // 4. Tugas Sedang Berjalan (Running Tasks)
        $runningTasks = RencanaKerja::where(function ($q) use ($authUser) {
                $q->where('user_id', $authUser->id)
                  ->orWhereHas('taggedUsers', function ($qu) use ($authUser) {
                      $qu->where('users.id', $authUser->id);
                  });
            })
            ->where('status', 'Berjalan')
            ->latest()
            ->take(2)
            ->get();

        foreach ($runningTasks as $t) {
            $notifications->push([
                'id' => $t->id,
                'type' => 'running',
                'action_type' => 'navigate',
                'icon' => 'bi-play-circle-fill text-primary',
                'bg_color' => '#dbeafe',
                'badge' => 'Berjalan',
                'badge_class' => 'bg-primary text-white',
                'title' => 'Tugas Sedang Berjalan',
                'message' => Str::limit($t->uraian_tugas, 45),
                'time' => 'Timer aktif saat ini',
                'url' => route('rencana-kerja.index'),
                'timestamp' => Carbon::now()->timestamp,
            ]);
        }

        // 5. Penugasan / Ditandai dalam Tim Kepanitiaan
        if (method_exists($authUser, 'taggedKepanitiaans')) {
            $taggedKepanitiaans = $authUser->taggedKepanitiaans()
                ->where('status', '!=', 'Selesai')
                ->latest()
                ->take(3)
                ->get();

            foreach ($taggedKepanitiaans as $k) {
                $notifications->push([
                    'id' => $k->id,
                    'type' => 'tagged_kepanitiaan',
                    'action_type' => 'navigate',
                    'icon' => 'bi-people-fill text-info',
                    'bg_color' => '#e0f2fe',
                    'badge' => 'Penugasan',
                    'badge_class' => 'bg-info text-dark',
                    'title' => 'Penugasan Kepanitiaan',
                    'message' => Str::limit($k->nama_kegiatan ?? $k->uraian_tugas, 45),
                    'time' => 'Terdaftar dalam tim',
                    'url' => route('kepanitiaan.index'),
                    'timestamp' => Carbon::parse($k->updated_at ?? $k->created_at)->timestamp,
                ]);
            }
        }

        // Urutkan notifikasi: Persetujuan waktu di atas, kemudian overdue, approved, running, tagged
        return $notifications->sortBy(function ($item) {
            $priority = [
                'extension_request' => 1,
                'overdue' => 2,
                'extension_approved' => 3,
                'running' => 4,
                'tagged_kepanitiaan' => 5,
            ];
            return $priority[$item['type']] ?? 99;
        })->values();
    }

    /**
     * AJAX endpoint to return fresh notifications data & HTML.
     */
    public function getNotifications(Request $request)
    {
        $authUser = Auth::user();

        if (!$authUser) {
            return response()->json([
                'success' => false,
                'count' => 0,
                'html' => '<li class="p-4 text-center text-muted small">Silakan login kembali.</li>',
            ], 401);
        }

        $notifications = self::fetchData($authUser);
        $count = $notifications->count();

        $html = view('layouts.dashboard.notification_items', compact('notifications'))->render();

        return response()->json([
            'success' => true,
            'count' => $count,
            'html' => $html,
        ]);
    }
}
