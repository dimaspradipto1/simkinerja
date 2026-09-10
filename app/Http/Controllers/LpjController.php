<?php

namespace App\Http\Controllers;

use App\DataTables\LpjDataTable;
use App\Http\Requests\LpjRequest;
use App\Models\Kepanitiaan;
use App\Models\Lpj;
use App\Models\LpjTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class LpjController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LpjDataTable $dataTable)
    {
        return $dataTable->render('pages.lpj.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $kepanitiaans = Kepanitiaan::query()
            ->orderBy('uraian_tugas', 'asc')
            ->get();

        $prefillKepanitiaanId = $request->query('kepanitiaan_id');
        $prefillKepanitiaan = $request->query('kepanitiaan');
        $prefillNamaLpj = $request->query('nama_lpj');
        $prefillTanggal = $request->query('tanggal');
        $templateLpj = LpjTemplate::first();

        return view('pages.lpj.create', compact(
            'kepanitiaans',
            'prefillKepanitiaanId',
            'prefillKepanitiaan',
            'prefillNamaLpj',
            'prefillTanggal',
            'templateLpj'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LpjRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        if ($request->hasFile('file_lpj')) {
            $file = $request->file('file_lpj');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . $cleanName . '.' . $extension;

            $filePath = $file->storeAs('lpj_files', $fileName, 'public');
            $data['file_lpj'] = $filePath;
        }

        Lpj::create($data);

        Alert::success('Berhasil', 'Laporan Pertanggungjawaban (LPJ) berhasil disimpan.');
        return redirect()->route('lpj.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $lpj = Lpj::with(['user', 'kepanitiaan'])->findOrFail($id);

        if (!$this->canViewLpj($lpj)) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki izin untuk melihat data LPJ ini.');
            return redirect()->route('lpj.index');
        }

        return view('pages.lpj.show', compact('lpj'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $lpj = Lpj::findOrFail($id);

        // Check if user is author or has admin/pimpinan role
        if (!$this->canManageLpj($lpj)) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki izin untuk mengedit data LPJ ini.');
            return redirect()->route('lpj.index');
        }

        $kepanitiaans = Kepanitiaan::query()
            ->orderBy('uraian_tugas', 'asc')
            ->get();

        return view('pages.lpj.edit', compact('lpj', 'kepanitiaans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LpjRequest $request, string $id)
    {
        $lpj = Lpj::findOrFail($id);

        if (!$this->canManageLpj($lpj)) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki izin untuk mengubah data LPJ ini.');
            return redirect()->route('lpj.index');
        }

        $data = $request->validated();

        if ($request->hasFile('file_lpj')) {
            // Delete old file if exists
            if ($lpj->file_lpj && Storage::disk('public')->exists($lpj->file_lpj)) {
                Storage::disk('public')->delete($lpj->file_lpj);
            }

            $file = $request->file('file_lpj');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . $cleanName . '.' . $extension;

            $filePath = $file->storeAs('lpj_files', $fileName, 'public');
            $data['file_lpj'] = $filePath;
        }

        $lpj->update($data);

        Alert::success('Berhasil', 'Laporan Pertanggungjawaban (LPJ) berhasil diperbarui.');
        return redirect()->route('lpj.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lpj = Lpj::findOrFail($id);

        if (!$this->canManageLpj($lpj)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus data LPJ ini.'
            ], 403);
        }

        if ($lpj->file_lpj && Storage::disk('public')->exists($lpj->file_lpj)) {
            Storage::disk('public')->delete($lpj->file_lpj);
        }

        $lpj->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data LPJ berhasil dihapus.'
        ]);
    }

    /**
     * Download the LPJ file.
     */
    public function download(string $id)
    {
        $lpj = Lpj::findOrFail($id);

        if (!$this->canViewLpj($lpj)) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki izin untuk mengunduh berkas LPJ ini.');
            return redirect()->route('lpj.index');
        }

        if (!$lpj->file_lpj || !Storage::disk('public')->exists($lpj->file_lpj)) {
            Alert::error('Gagal', 'Berkas file LPJ tidak ditemukan di server.');
            return back();
        }

        return response()->download(storage_path('app/public/' . $lpj->file_lpj), basename($lpj->file_lpj));
    }

    /**
     * Display or manage the LPJ template.
     */
    public function template()
    {
        $user = Auth::user();
        if (!$user || (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isTimEvaluasi())) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki izin untuk mengakses halaman Template LPJ.');
            return redirect()->route('lpj.index');
        }

        $templateLpj = LpjTemplate::first();
        if (!$templateLpj) {
            $templateLpj = LpjTemplate::create([
                'nama_template' => 'Template Resmi Laporan Pertanggungjawaban (LPJ)',
                'link_lpj' => 'https://docs.google.com/document/d/1official-template-lpj/edit?usp=sharing',
                'keterangan' => 'Gunakan template dokumen resmi ini sebagai acuan penulisan LPJ kegiatan kepanitiaan.',
            ]);
        }

        $canEdit = $user->isAdmin() || $user->isSuperAdmin() || $user->isTimEvaluasi();

        return view('pages.lpj.template', compact('templateLpj', 'canEdit'));
    }

    /**
     * Update the LPJ template link and file.
     */
    public function updateTemplate(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin() && !$user->isTimEvaluasi())) {
            Alert::error('Akses Ditolak', 'Anda tidak memiliki izin untuk mengubah pengaturan template LPJ.');
            return redirect()->route('lpj.template');
        }

        $request->validate([
            'nama_template' => 'required|string|max:255',
            'link_lpj' => 'nullable|url|max:500',
            'file_template' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip,rar|max:20480',
            'keterangan' => 'nullable|string',
        ], [
            'link_lpj.url' => 'Format tautan (link) LPJ harus berupa URL yang valid (contoh: https://drive.google.com/...).',
        ]);

        $templateLpj = LpjTemplate::first();
        if (!$templateLpj) {
            $templateLpj = new LpjTemplate();
        }

        $templateLpj->nama_template = $request->nama_template;
        $templateLpj->link_lpj = $request->link_lpj;
        $templateLpj->keterangan = $request->keterangan;
        $templateLpj->updated_by = Auth::id();

        if ($request->hasFile('file_template')) {
            if ($templateLpj->file_template && Storage::disk('public')->exists($templateLpj->file_template)) {
                Storage::disk('public')->delete($templateLpj->file_template);
            }
            $file = $request->file('file_template');
            $fileName = 'template_lpj_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('lpj_templates', $fileName, 'public');
            $templateLpj->file_template = $path;
        }

        $templateLpj->save();

        Alert::success('Berhasil', 'Pengaturan template LPJ berhasil disimpan.');
        return redirect()->route('lpj.template');
    }

    /**
     * Download the LPJ template file or redirect to link.
     */
    public function downloadTemplate()
    {
        $templateLpj = LpjTemplate::first();
        if (!$templateLpj) {
            Alert::error('Gagal', 'Template LPJ belum tersedia.');
            return back();
        }

        if ($templateLpj->file_template && Storage::disk('public')->exists($templateLpj->file_template)) {
            return response()->download(storage_path('app/public/' . $templateLpj->file_template), basename($templateLpj->file_template));
        }

        if (!empty($templateLpj->link_lpj)) {
            return redirect()->away($templateLpj->link_lpj);
        }

        Alert::warning('Perhatian', 'Berkas maupun tautan template LPJ belum diatur oleh admin.');
        return back();
    }

    /**
     * Helper to check if current user can view the given LPJ.
     * Hanya Super Admin, Admin, Tim Evaluasi, dan pemilik LPJ yang bisa melihat.
     */
    private function canViewLpj(Lpj $lpj): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin() ||
            $user->isAdmin() ||
            $user->isTimEvaluasi() ||
            $user->id === $lpj->user_id;
    }

    /**
     * Helper to check if current user can edit/delete the given LPJ.
     * Hanya Super Admin, Admin, dan pemilik LPJ yang bisa mengedit/menghapus.
     */
    private function canManageLpj(Lpj $lpj): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin() ||
            $user->isAdmin() ||
            $user->id === $lpj->user_id;
    }
}
