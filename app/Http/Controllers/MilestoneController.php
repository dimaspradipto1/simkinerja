<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MilestoneController extends Controller
{
    /**
     * Store a newly created milestone point.
     */
    public function store(Request $request)
    {
        $request->validate([
            'milestonable_type' => 'required|string',
            'milestonable_id'   => 'required|integer',
            'nama_milestone'    => 'required|string|max:255',
            'catatan'           => 'nullable|string',
        ]);

        $typeMap = [
            'rencana_kerja' => \App\Models\RencanaKerja::class,
            'kepanitiaan'   => \App\Models\Kepanitiaan::class,
            'insidentil'    => \App\Models\Insidentil::class,
            \App\Models\RencanaKerja::class => \App\Models\RencanaKerja::class,
            \App\Models\Kepanitiaan::class   => \App\Models\Kepanitiaan::class,
            \App\Models\Insidentil::class    => \App\Models\Insidentil::class,
        ];

        $targetType = $typeMap[$request->milestonable_type] ?? $request->milestonable_type;

        $milestone = Milestone::create([
            'milestonable_type' => $targetType,
            'milestonable_id'   => $request->milestonable_id,
            'nama_milestone'    => $request->nama_milestone,
            'catatan'           => $request->catatan,
            'status'            => 'Belum Dimulai',
            'durasi_detik'      => 0,
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Point milestone berhasil ditambahkan.',
            'milestone' => $milestone,
        ]);
    }

    /**
     * Start/Resume milestone point timer.
     */
    public function start(Milestone $milestone)
    {
        $now = Carbon::now();

        if ($milestone->status === 'Di-pause') {
            // Complete current paused milestone phase
            $milestone->status = 'Selesai';
            if (is_null($milestone->waktu_selesai)) {
                $milestone->waktu_selesai = $now;
            }
            $milestone->save();

            // Create a new continuation milestone (Point #2, Point #3, etc.)
            $parent = $milestone->milestonable;
            $newMilestone = null;
            if ($parent) {
                $newMilestone = $parent->milestones()->create([
                    'nama_milestone'    => 'Melanjutkan Pekerjaan',
                    'status'            => 'Berjalan',
                    'waktu_mulai'       => $now,
                    'last_started_at'   => $now,
                    'durasi_detik'      => 0,
                ]);

                // Update parent task status to Berjalan
                if ($parent->status !== 'Berjalan') {
                    $parent->update(['status' => 'Berjalan', 'last_started_at' => $now]);
                }
            }

            return response()->json([
                'success'   => true,
                'message'   => 'Milestone lanjutan berhasil dibuat.',
                'milestone' => $newMilestone ?? $milestone->fresh(),
            ]);
        }

        if (is_null($milestone->waktu_mulai)) {
            $milestone->waktu_mulai = $now;
        }

        $milestone->last_started_at = $now;
        $milestone->status = 'Berjalan';
        $milestone->save();

        if ($milestone->milestonable) {
            $parent = $milestone->milestonable;
            $updateData = [
                'status' => 'Berjalan',
                'last_started_at' => $now,
            ];
            if (empty($parent->tanggal_mulai)) {
                $updateData['tanggal_mulai'] = $now->format('Y-m-d');
            }
            if (empty($parent->waktu_mulai) || $parent->waktu_mulai === '00:00:00') {
                $updateData['waktu_mulai'] = $now->format('H:i:s');
            }
            $parent->update($updateData);
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Milestone started successfully.',
            'milestone' => $milestone->fresh(),
        ]);
    }

    /**
     * Pause milestone point timer.
     */
    public function pause(Milestone $milestone)
    {
        $now = Carbon::now();

        if ($milestone->status === 'Berjalan' && $milestone->last_started_at) {
            $elapsedSeconds = (int) abs($now->diffInSeconds($milestone->last_started_at));
            $milestone->durasi_detik = (int) ($milestone->durasi_detik ?? 0) + $elapsedSeconds;
        }

        $milestone->last_started_at = null;
        $milestone->status = 'Di-pause';
        $milestone->save();

        if ($milestone->milestonable) {
            $parent = $milestone->milestonable;
            $runningCount = $parent->milestones()->where('status', 'Berjalan')->count();
            if ($runningCount === 0) {
                $parent->update([
                    'status' => 'Di-pause',
                    'last_started_at' => null,
                ]);
            }
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Milestone paused successfully.',
            'milestone' => $milestone->fresh(),
        ]);
    }

    /**
     * Stop/Complete milestone point timer.
     */
    public function stop(Milestone $milestone)
    {
        $now = Carbon::now();

        if ($milestone->status === 'Berjalan' && $milestone->last_started_at) {
            $elapsedSeconds = (int) abs($now->diffInSeconds($milestone->last_started_at));
            $milestone->durasi_detik = (int) ($milestone->durasi_detik ?? 0) + $elapsedSeconds;
        }

        $milestone->last_started_at = null;
        $milestone->waktu_selesai = $now;
        $milestone->status = 'Selesai';
        $milestone->save();

        if ($milestone->milestonable) {
            $parent = $milestone->milestonable;
            $runningCount = $parent->milestones()->where('status', 'Berjalan')->count();
            if ($runningCount === 0) {
                $uncompletedCount = $parent->milestones()->where('status', '!=', 'Selesai')->count();
                if ($uncompletedCount === 0) {
                    // All milestone points completed -> stop parent task!
                    $updateData = [
                        'status' => 'Selesai',
                        'last_started_at' => null,
                    ];
                    if (empty($parent->tanggal_selesai)) {
                        $updateData['tanggal_selesai'] = $now->format('Y-m-d');
                    }
                    if (empty($parent->waktu_selesai) || $parent->waktu_selesai === '00:00:00') {
                        $updateData['waktu_selesai'] = $now->format('H:i:s');
                    }
                    $parent->update($updateData);
                } else {
                    $parent->update([
                        'status' => 'Di-pause',
                        'last_started_at' => null,
                    ]);
                }
            }
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Milestone finished successfully.',
            'milestone' => $milestone->fresh(),
        ]);
    }

    /**
     * Delete a milestone point.
     */
    public function destroy(Milestone $milestone)
    {
        $milestone->delete();

        return response()->json([
            'success' => true,
            'message' => 'Point milestone berhasil dihapus.',
        ]);
    }

    /**
     * Fetch all milestones for a target model.
     */
    public function index(Request $request)
    {
        $typeMap = [
            'rencana_kerja' => \App\Models\RencanaKerja::class,
            'kepanitiaan'   => \App\Models\Kepanitiaan::class,
            'insidentil'    => \App\Models\Insidentil::class,
        ];

        $targetType = $typeMap[$request->milestonable_type] ?? $request->milestonable_type;
        $targetId   = $request->milestonable_id;

        $milestones = Milestone::where('milestonable_type', $targetType)
            ->where('milestonable_id', $targetId)
            ->orderBy('id', 'asc')
            ->get();

        $totalDetik = $milestones->sum(function ($m) {
            return $m->total_durasi_detik;
        });

        // Formatted overall total duration
        $days = floor($totalDetik / 86400);
        $hours = floor(($totalDetik % 86400) / 3600);
        $minutes = floor(($totalDetik % 3600) / 60);
        $seconds = $totalDetik % 60;
        $totalFormattedParts = [];
        if ($days > 0) $totalFormattedParts[] = $days . 'h';
        if ($hours > 0) $totalFormattedParts[] = $hours . 'j';
        if ($minutes > 0) $totalFormattedParts[] = $minutes . 'm';
        if ($seconds > 0 || empty($totalFormattedParts)) $totalFormattedParts[] = $seconds . 's';
        $totalFormattedStr = implode(' ', $totalFormattedParts);

        return response()->json([
            'success'         => true,
            'milestones'      => $milestones,
            'total_detik'     => $totalDetik,
            'total_formatted' => $totalFormattedStr,
        ]);
    }
}
