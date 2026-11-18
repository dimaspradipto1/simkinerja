<?php

namespace App\Traits;

use App\Models\Kegiatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

trait ManagesKegiatanLifecycle
{
    /**
     * Resolve kegiatan item to Kegiatan instance.
     */
    protected function resolveKegiatan($item): Kegiatan
    {
        if ($item instanceof Kegiatan) {
            return $item;
        }

        if (is_numeric($item)) {
            return Kegiatan::findOrFail($item);
        }

        return $item;
    }

    /**
     * Get folder name for attachment storage.
     */
    protected function getAttachmentFolder(): string
    {
        if (property_exists($this, 'attachmentFolder') && !empty($this->attachmentFolder)) {
            return $this->attachmentFolder;
        }

        return 'kegiatan';
    }

    /**
     * Check if user has permission to operate this task.
     */
    protected function canOperateKegiatan(Kegiatan $kegiatan, ?User $authUser): bool
    {
        if (!$authUser) {
            return false;
        }

        return ($kegiatan->user_id === $authUser->id)
            || ($kegiatan->relationLoaded('taggedUsers') ? $kegiatan->taggedUsers->contains($authUser->id) : $kegiatan->taggedUsers()->where('users.id', $authUser->id)->exists())
            || $authUser->isAdmin()
            || $authUser->isSuperAdmin()
            || $authUser->isPimpinanRektorat()
            || $authUser->isPimpinanUnit();
    }

    /**
     * Automatically log milestone points for task lifecycle transitions.
     */
    public function autoLogMilestone($kegiatan, string $action, Carbon $now, ?string $alasanPause = null): void
    {
        $kegiatan = $this->resolveKegiatan($kegiatan);

        if ($action === 'start') {
            $pausedMilestones = $kegiatan->milestones()->where('status', 'Di-pause')->get();
            foreach ($pausedMilestones as $pm) {
                $pm->status = 'Selesai';
                if (!$pm->waktu_selesai) {
                    $pm->waktu_selesai = $now;
                }
                $pm->save();
            }

            $runningMilestone = $kegiatan->milestones()->where('status', 'Berjalan')->first();
            if (!$runningMilestone) {
                $milestoneCount = $kegiatan->milestones()->count();
                $title = ($milestoneCount === 0) ? 'Mulai Pelaksanaan Pekerjaan' : 'Melanjutkan Pekerjaan';
                $kegiatan->milestones()->create([
                    'nama_milestone' => $title,
                    'status' => 'Berjalan',
                    'waktu_mulai' => $now,
                    'last_started_at' => $now,
                    'durasi_detik' => 0,
                    'urutan' => $milestoneCount + 1,
                ]);
            }
        } elseif ($action === 'pause') {
            $runningMilestones = $kegiatan->milestones()->where('status', 'Berjalan')->get();
            foreach ($runningMilestones as $m) {
                if ($m->last_started_at) {
                    $elapsedSeconds = (int) abs($now->diffInSeconds($m->last_started_at));
                    $m->durasi_detik = (int) ($m->durasi_detik ?? 0) + $elapsedSeconds;
                }
                $m->last_started_at = null;
                $m->status = 'Di-pause';
                if ($alasanPause !== null) {
                    $m->alasan_pause = $alasanPause;
                }
                $m->save();
            }
        } elseif ($action === 'stop') {
            $activeMilestones = $kegiatan->milestones()->whereIn('status', ['Berjalan', 'Di-pause'])->get();
            foreach ($activeMilestones as $m) {
                if ($m->status === 'Berjalan' && $m->last_started_at) {
                    $elapsedSeconds = (int) abs($now->diffInSeconds($m->last_started_at));
                    $m->durasi_detik = (int) ($m->durasi_detik ?? 0) + $elapsedSeconds;
                }
                $m->last_started_at = null;
                $m->status = 'Selesai';
                if (!$m->waktu_selesai) {
                    $m->waktu_selesai = $now;
                }
                $m->save();
            }

            $lastMilestone = $kegiatan->milestones()->latest()->first();
            if (!$lastMilestone || $lastMilestone->nama_milestone !== 'Selesai Pekerjaan') {
                $kegiatan->milestones()->create([
                    'nama_milestone' => 'Selesai Pekerjaan',
                    'status' => 'Selesai',
                    'waktu_mulai' => $now,
                    'waktu_selesai' => $now,
                    'durasi_detik' => 0,
                ]);
            }
        }
    }

    /**
     * Start/Resume task timer.
     */
    public function executeStart(Request $request, $item)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Anda belum login.'], 401);
        }

        $kegiatan = $this->resolveKegiatan($item);

        if (!$this->canOperateKegiatan($kegiatan, $authUser)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengoperasikan tugas ini.'], 403);
        }

        $now = Carbon::now();

        if ($kegiatan->status !== 'Selesai' && !empty($kegiatan->estimasi_tanggal_selesai)) {
            $endTime = $kegiatan->estimasi_jam_selesai ?: '23:59:59';
            $estimasiEnd = Carbon::parse($kegiatan->estimasi_tanggal_selesai . ' ' . $endTime);
            if ($now->gt($estimasiEnd)) {
                return response()->json([
                    'success' => false,
                    'is_expired' => true,
                    'message' => 'Tugas ini telah melewati batas waktu estimasi pelaksanaan (Tugas Gagal). Silakan ajukan permohonan perpanjangan waktu ke pimpinan Anda.',
                ], 422);
            }
        }

        $clientTime = $request->input('client_time') ?? now()->format('H:i:s');
        $clientDate = $request->input('client_date') ?? now()->format('Y-m-d');

        $updateData = [
            'status' => 'Berjalan',
            'last_started_at' => $now,
        ];

        if (empty($kegiatan->waktu_mulai) || $kegiatan->waktu_mulai === '00:00:00') {
            $updateData['waktu_mulai'] = $clientTime;
            $updateData['tanggal_mulai'] = $clientDate;
        }

        $kegiatan->update($updateData);

        $this->autoLogMilestone($kegiatan, 'start', $now);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dijalankan (' . substr($clientTime, 0, 5) . ').',
        ]);
    }

    /**
     * Pause task timer.
     */
    public function executePause(Request $request, $item)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Anda belum login.'], 401);
        }

        $kegiatan = $this->resolveKegiatan($item);

        if (!$this->canOperateKegiatan($kegiatan, $authUser)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $now = Carbon::now();

        if ($kegiatan->status === 'Berjalan' && $kegiatan->last_started_at) {
            $elapsedSeconds = (int) abs($now->diffInSeconds($kegiatan->last_started_at));
            $kegiatan->durasi_detik = (int) ($kegiatan->durasi_detik ?? 0) + $elapsedSeconds;
        }

        $alasanPause = $request->input('alasan_pause');
        $kegiatan->last_started_at = null;
        $kegiatan->status = 'Di-pause';
        if ($alasanPause !== null) {
            $kegiatan->alasan_pause = $alasanPause;
        }
        $kegiatan->save();

        $this->autoLogMilestone($kegiatan, 'pause', $now, $alasanPause);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil di-pause.',
        ]);
    }

    /**
     * Stop task timer.
     */
    public function executeStop(Request $request, $item)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Anda belum login.'], 401);
        }

        $kegiatan = $this->resolveKegiatan($item);

        if (!$this->canOperateKegiatan($kegiatan, $authUser)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $now = Carbon::now();

        if ($kegiatan->status !== 'Selesai' && !empty($kegiatan->estimasi_tanggal_selesai)) {
            $endTime = $kegiatan->estimasi_jam_selesai ?: '23:59:59';
            $estimasiEnd = Carbon::parse($kegiatan->estimasi_tanggal_selesai . ' ' . $endTime);
            if ($now->gt($estimasiEnd)) {
                return response()->json([
                    'success' => false,
                    'is_expired' => true,
                    'message' => 'Tugas ini telah melewati batas waktu estimasi pelaksanaan (Tugas Gagal). Silakan ajukan permohonan perpanjangan waktu ke pimpinan Anda.',
                ], 422);
            }
        }

        $clientTime = $request->input('client_time') ?? now()->format('H:i:s');
        $clientDate = $request->input('client_date') ?? now()->format('Y-m-d');

        if ($kegiatan->status === 'Berjalan' && $kegiatan->last_started_at) {
            $elapsedSeconds = (int) abs($now->diffInSeconds($kegiatan->last_started_at));
            $kegiatan->durasi_detik = (int) ($kegiatan->durasi_detik ?? 0) + $elapsedSeconds;
        }

        $targetStatus = 'Menunggu Validasi';
        $validatedAt = null;
        $validatedBy = null;

        if ($authUser->isSuperAdmin() || ($authUser->isPimpinanRektorat() && !$authUser->supervisor_id)) {
            $targetStatus = 'Disetujui';
            $validatedAt = $now;
            $validatedBy = $authUser->id;
        }

        $updateData = [
            'waktu_selesai' => $clientTime,
            'tanggal_selesai' => $clientDate,
            'status' => $targetStatus,
            'last_started_at' => null,
            'validated_at' => $validatedAt,
            'validated_by' => $validatedBy,
        ];

        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip,rar|max:20480',
            ]);
            if ($kegiatan->file && Storage::disk('public')->exists($kegiatan->file)) {
                Storage::disk('public')->delete($kegiatan->file);
            }
            $updateData['file'] = $request->file('file')->store($this->getAttachmentFolder(), 'public');
        }

        if ($request->filled('url_external')) {
            $updateData['url_external'] = $request->input('url_external');
        }

        $kegiatan->update($updateData);

        $this->autoLogMilestone($kegiatan, 'stop', $now);

        $msg = ($targetStatus === 'Disetujui')
            ? 'Waktu penyelesaian tugas (' . substr($clientTime, 0, 5) . ') berhasil dicatat.'
            : 'Tugas selesai dikerjakan (' . substr($clientTime, 0, 5) . ') dan diajukan untuk validasi atasan.';

        return response()->json([
            'success' => true,
            'new_status' => $targetStatus,
            'message' => $msg,
        ]);
    }

    /**
     * Upload attachment inline.
     */
    public function executeUploadAttachment(Request $request, $item)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Anda belum login.'], 401);
        }

        $kegiatan = $this->resolveKegiatan($item);

        if (!$this->canOperateKegiatan($kegiatan, $authUser)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip,rar|max:20480',
            'url_external' => 'nullable|url|max:500',
        ]);

        $updateData = [];

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            if ($uploadedFile->isValid()) {
                if ($kegiatan->file && Storage::disk('public')->exists($kegiatan->file)) {
                    Storage::disk('public')->delete($kegiatan->file);
                }
                $updateData['file'] = $uploadedFile->store($this->getAttachmentFolder(), 'public');
            }
        }

        if ($request->has('url_external')) {
            $updateData['url_external'] = $request->input('url_external');
        }

        if ($request->has('hasil_kerja')) {
            $updateData['hasil_kerja'] = $request->input('hasil_kerja');
        }

        if ($request->has('rencana_tindak_lanjut')) {
            $updateData['rencana_tindak_lanjut'] = $request->input('rencana_tindak_lanjut');
        }

        if ($request->has('alasan_pause')) {
            $alasanPause = $request->input('alasan_pause');
            $updateData['alasan_pause'] = $alasanPause;
            $kegiatan->milestones()->where('status', 'Di-pause')->update(['alasan_pause' => $alasanPause]);
        }

        if (!empty($updateData)) {
            $kegiatan->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berkas dan link eksternal berhasil disimpan.',
        ]);
    }

    /**
     * Request unlock estimasi waktu to pimpinan.
     */
    public function executeRequestUnlockEstimasi(Request $request, $item)
    {
        $kegiatan = $this->resolveKegiatan($item);

        $request->validate([
            'estimasi_unlock_reason' => 'required|string|max:1000',
        ]);

        $reason = $request->input('estimasi_unlock_reason');
        if ($request->filled('pimpinan_id')) {
            $pimpinan = User::find($request->input('pimpinan_id'));
            if ($pimpinan) {
                $reason = "[Tujuan: " . $pimpinan->name . " - " . ($pimpinan->jabatan ?: $pimpinan->roles) . "] " . $reason;
            }
        }

        $kegiatan->update([
            'estimasi_unlock_reason' => $reason,
            'estimasi_unlock_requested_at' => now(),
        ]);

        Alert::success('Berhasil', 'Permintaan perubahan estimasi telah dikirim ke pimpinan.')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->back();
    }

    /**
     * Approve extension request by pimpinan.
     */
    public function executeApproveExtension(Request $request, $item)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit())) {
            abort(403, 'Hanya pimpinan atau administrator yang dapat menyetujui perpanjangan waktu.');
        }

        $kegiatan = $this->resolveKegiatan($item);

        $request->validate([
            'estimasi_tanggal_selesai' => 'required|date',
            'estimasi_jam_selesai'     => 'required|string',
            'catatan_pimpinan'         => 'nullable|string|max:1000',
        ]);

        $updateData = [
            'estimasi_tanggal_selesai'     => $request->input('estimasi_tanggal_selesai'),
            'estimasi_jam_selesai'         => $request->input('estimasi_jam_selesai'),
            'estimasi_unlock_requested_at'  => null,
            'estimasi_unlock_approved_at'   => now(),
        ];

        if (empty($kegiatan->estimasi_tanggal_selesai_awal)) {
            $updateData['estimasi_tanggal_selesai_awal'] = $kegiatan->estimasi_tanggal_selesai;
            $updateData['estimasi_jam_selesai_awal']     = $kegiatan->estimasi_jam_selesai;
        }

        if ($request->filled('catatan_pimpinan')) {
            $existing = $kegiatan->estimasi_unlock_reason ? $kegiatan->estimasi_unlock_reason . " | " : "";
            $updateData['estimasi_unlock_reason'] = $existing . "[Disetujui oleh " . $authUser->name . "]: " . $request->input('catatan_pimpinan');
        }

        $kegiatan->update($updateData);

        Alert::success('Berhasil', 'Perpanjangan waktu tugas berhasil disetujui & diberikan kepada pegawai.')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->back();
    }

    /**
     * Request reopen completed task.
     */
    public function executeRequestReopen(Request $request, $item)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Anda belum login.'], 401);
        }

        $kegiatan = $this->resolveKegiatan($item);

        if (!$this->canOperateKegiatan($kegiatan, $authUser)) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk tugas ini.'], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $kegiatan->update([
            'reopen_reason' => $request->input('reason'),
            'reopen_requested_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan buka kembali tugas berhasil dikirimkan ke Pimpinan/Admin.',
        ]);
    }

    /**
     * Reopen completed task by pimpinan/admin.
     */
    public function executeReopen(Request $request, $item)
    {
        $authUser = Auth::user();
        if (!$authUser || (!$authUser->isAdmin() && !$authUser->isSuperAdmin() && !$authUser->isPimpinanRektorat() && !$authUser->isPimpinanUnit())) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Pimpinan, Admin, atau Superadmin yang berwenang membuka kunci tugas yang telah diselesaikan.',
            ], 403);
        }

        $kegiatan = $this->resolveKegiatan($item);
        $now = Carbon::now();

        // 1. Hapus milestone auto-log "Selesai Pekerjaan" jika ada
        $finishMilestones = $kegiatan->milestones()
            ->where(function ($q) {
                $q->where('nama_milestone', 'Selesai Pekerjaan')
                  ->orWhere('nama_milestone', 'like', '%Selesai%Pekerjaan%');
            })->get();
        foreach ($finishMilestones as $fm) {
            $fm->delete();
        }

        // 2. Aktifkan milestone point agar langsung melanjutkan dan berjalan otomatis
        $lastMilestone = $kegiatan->milestones()->latest()->first();
        if ($lastMilestone) {
            $lastMilestone->status = 'Berjalan';
            $lastMilestone->waktu_selesai = null;
            $lastMilestone->last_started_at = $now;
            $lastMilestone->save();
        } else {
            $kegiatan->milestones()->create([
                'nama_milestone' => 'Melanjutkan Pekerjaan',
                'status' => 'Berjalan',
                'waktu_mulai' => $now,
                'last_started_at' => $now,
                'durasi_detik' => 0,
            ]);
        }

        // 3. Kembalikan status tugas ke 'Berjalan', reset waktu_selesai, dan pasang last_started_at
        $kegiatan->update([
            'status' => 'Berjalan',
            'waktu_selesai' => null,
            'tanggal_selesai' => null,
            'last_started_at' => $now,
            'reopen_reason' => null,
            'reopen_requested_at' => null,
            'reopened_at' => $now,
            'reopened_by' => $authUser->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kunci tugas berhasil dibuka dan pengerjaan langsung berjalan otomatis.',
        ]);
    }

    /**
     * Update tagged users on a task.
     */
    public function executeUpdateTags(Request $request, $item)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Anda belum login.'], 401);
        }

        $kegiatan = $this->resolveKegiatan($item);

        if (!$this->canOperateKegiatan($kegiatan, $authUser)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'tagged_users' => 'nullable|array',
            'tagged_users.*' => 'exists:users,id',
        ]);

        $taggedIds = $request->input('tagged_users', []);
        $kegiatan->taggedUsers()->sync($taggedIds);
        \App\Helpers\AbsensiKinerjaHelper::syncTaskKeterangan($kegiatan);

        return response()->json([
            'success' => true,
            'message' => 'Rekan kerja berhasil diperbarui.',
        ]);
    }

    /**
     * Validate task by superior (Approve, Revise, Reject).
     */
    public function executeValidateTask(Request $request, $item)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Anda belum login.'], 401);
        }

        $kegiatan = $this->resolveKegiatan($item);

        if (!$authUser->canValidateTask($kegiatan)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki wewenang untuk memvalidasi tugas ini.',
            ], 403);
        }

        $request->validate([
            'action' => 'required|in:approve,revise,reject',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $action = $request->input('action');
        $catatan = trim($request->input('catatan') ?? '');

        if (($action === 'revise' || $action === 'reject') && empty($catatan)) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan/alasan wajib diisi jika meminta revisi atau menolak tugas.',
            ], 422);
        }

        $now = Carbon::now();

        if ($action === 'approve') {
            $newStatus = 'Disetujui';
            $message = 'Tugas berhasil divalidasi & disetujui.';
            $milestoneTitle = 'Divalidasi & Disetujui oleh ' . $authUser->name;
        } elseif ($action === 'revise') {
            $newStatus = 'Perlu Revisi';
            $message = 'Permintaan revisi berhasil dikirim ke pegawai terkait.';
            $milestoneTitle = 'Diminta Revisi oleh ' . $authUser->name . ($catatan ? ': ' . $catatan : '');
        } else {
            $newStatus = 'Ditolak';
            $message = 'Tugas telah ditolak dengan catatan evaluasi.';
            $milestoneTitle = 'Ditolak oleh ' . $authUser->name . ($catatan ? ': ' . $catatan : '');
        }

        $kegiatan->update([
            'status' => $newStatus,
            'validated_at' => $now,
            'validated_by' => $authUser->id,
            'catatan_validasi' => $catatan ?: null,
        ]);

        $kegiatan->milestones()->create([
            'nama_milestone' => $milestoneTitle,
            'status' => 'Selesai',
            'waktu_mulai' => $now,
            'waktu_selesai' => $now,
            'durasi_detik' => 0,
        ]);

        return response()->json([
            'success' => true,
            'new_status' => $newStatus,
            'message' => $message,
        ]);
    }

    /**
     * Batch validate tasks (approve multiple selected tasks).
     */
    public function executeBatchValidate(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Anda belum login.'], 401);
        }

        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:kegiatans,id',
            'action' => 'required|in:approve',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $ids = $request->input('ids', []);
        $catatan = $request->input('catatan');
        $now = Carbon::now();

        $kegiatans = Kegiatan::whereIn('id', $ids)->get();
        $approvedCount = 0;
        $unauthorizedCount = 0;

        foreach ($kegiatans as $kegiatan) {
            if ($authUser->canValidateTask($kegiatan)) {
                $kegiatan->update([
                    'status' => 'Disetujui',
                    'validated_at' => $now,
                    'validated_by' => $authUser->id,
                    'catatan_validasi' => $catatan ?: null,
                ]);

                $kegiatan->milestones()->create([
                    'nama_milestone' => 'Divalidasi (Batch) oleh ' . $authUser->name,
                    'status' => 'Selesai',
                    'waktu_mulai' => $now,
                    'waktu_selesai' => $now,
                    'durasi_detik' => 0,
                ]);

                $approvedCount++;
            } else {
                $unauthorizedCount++;
            }
        }

        if ($approvedCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada tugas yang berhasil disetujui (Anda bukan atasan penanggung jawab).',
            ], 403);
        }

        $msg = "Berhasil menyetujui {$approvedCount} tugas.";
        if ($unauthorizedCount > 0) {
            $msg .= " ({$unauthorizedCount} tugas dilewati karena di luar wewenang).";
        }

        return response()->json([
            'success' => true,
            'approved_count' => $approvedCount,
            'message' => $msg,
        ]);
    }

    /**
     * Resubmit task for superior validation after revision.
     */
    public function executeResubmitValidation(Request $request, $item)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'Anda belum login.'], 401);
        }

        $kegiatan = $this->resolveKegiatan($item);

        if (!$this->canOperateKegiatan($kegiatan, $authUser)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $now = Carbon::now();

        $kegiatan->update([
            'status' => 'Menunggu Validasi',
        ]);

        $kegiatan->milestones()->create([
            'nama_milestone' => 'Diajukan Ulang Validasi oleh ' . $authUser->name,
            'status' => 'Selesai',
            'waktu_mulai' => $now,
            'waktu_selesai' => $now,
            'durasi_detik' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diajukan ulang untuk validasi atasan.',
        ]);
    }
}
