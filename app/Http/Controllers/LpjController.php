<?php

namespace App\Http\Controllers;

use App\DataTables\LpjDataTable;
use App\Http\Requests\LpjRequest;
use App\Models\Lpj;
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
    public function create()
    {
        $jenisKegiatanOptions = [
            'Rencana Kerja Utama',
            'Rencana Kerja Kepanitiaan',
            'Rencana Kerja Insidentil',
            'Kegiatan',
            'Lainnya',
        ];

        return view('pages.lpj.create', compact('jenisKegiatanOptions'));
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
        $lpj = Lpj::with('user')->findOrFail($id);
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

        $jenisKegiatanOptions = [
            'Rencana Kerja Utama',
            'Rencana Kerja Kepanitiaan',
            'Rencana Kerja Insidentil',
            'Kegiatan',
            'Lainnya',
        ];

        return view('pages.lpj.edit', compact('lpj', 'jenisKegiatanOptions'));
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

        if (!$lpj->file_lpj || !Storage::disk('public')->exists($lpj->file_lpj)) {
            Alert::error('Gagal', 'Berkas file LPJ tidak ditemukan di server.');
            return back();
        }

        return response()->download(storage_path('app/public/' . $lpj->file_lpj), basename($lpj->file_lpj));
    }

    /**
     * Helper to check if current user can manage the given LPJ.
     */
    private function canManageLpj(Lpj $lpj): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->isAdmin() ||
            $user->isSuperAdmin() ||
            $user->isPimpinanRektorat() ||
            $user->isPimpinanUnit() ||
            $user->id === $lpj->user_id;
    }
}
