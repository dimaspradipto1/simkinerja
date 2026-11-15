<?php

namespace App\Http\Controllers;

use App\Http\Requests\TriwulanRequest;
use App\Models\Triwulan;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class TriwulanController extends Controller
{
    /**
     * Tampilkan daftar Triwulan (TW)
     */
    public function index()
    {
        $triwulans = Triwulan::orderBy('urutan', 'asc')->get();

        // Jika belum ada data, buat 4 Triwulan standar otomatis
        if ($triwulans->isEmpty()) {
            $this->seedStandardTws();
            $triwulans = Triwulan::orderBy('urutan', 'asc')->get();
        }

        return view('pages.triwulan.index', compact('triwulans'));
    }

    /**
     * Form tambah Triwulan
     */
    public function create()
    {
        $standardTws = Triwulan::getStandardDefinitions();
        return view('pages.triwulan.create', compact('standardTws'));
    }

    /**
     * Simpan Triwulan baru
     */
    public function store(TriwulanRequest $request)
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['is_active']) {
            Triwulan::query()->update(['is_active' => false]);
        }

        Triwulan::create($validated);

        Alert::success('Berhasil', 'Data Triwulan berhasil ditambahkan.');
        return redirect()->route('triwulan.index');
    }

    /**
     * Form edit Triwulan
     */
    public function edit(Triwulan $triwulan)
    {
        $standardTws = Triwulan::getStandardDefinitions();
        return view('pages.triwulan.edit', compact('triwulan', 'standardTws'));
    }

    /**
     * Update data Triwulan
     */
    public function update(TriwulanRequest $request, Triwulan $triwulan)
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['is_active']) {
            Triwulan::where('id', '!=', $triwulan->id)->update(['is_active' => false]);
        }

        $triwulan->update($validated);

        Alert::success('Berhasil', 'Data Triwulan berhasil diperbarui.');
        return redirect()->route('triwulan.index');
    }

    /**
     * Hapus Triwulan
     */
    public function destroy(Triwulan $triwulan)
    {
        $triwulan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Triwulan berhasil dihapus.',
        ]);
    }

    /**
     * Set aktif triwulan terpilih
     */
    public function setActive(Triwulan $triwulan)
    {
        Triwulan::where('id', '!=', $triwulan->id)->update(['is_active' => false]);
        $triwulan->update(['is_active' => true]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Triwulan ' . $triwulan->nama . ' (' . $triwulan->rentang_bulan . ') berhasil diaktifkan.',
            ]);
        }

        Alert::success('Berhasil', 'Triwulan ' . $triwulan->nama . ' berhasil diaktifkan.');
        return redirect()->route('triwulan.index');
    }

    /**
     * Reset / Sinkronisasi 4 Triwulan Standar:
     * TW1 (Sep–Nov)
     * TW2 (Des–Feb)
     * TW3 (Mar–Mei)
     * TW4 (Jun–Agu)
     */
    public function generateStandard()
    {
        $this->seedStandardTws();

        Alert::success('Berhasil', '4 Triwulan standar (TW1 s/d TW4) berhasil disinkronkan.');
        return redirect()->route('triwulan.index');
    }

    /**
     * Helper internal seed 4 TW
     */
    private function seedStandardTws(): void
    {
        $standardTws = Triwulan::getStandardDefinitions();
        $currentMonth = (int) now()->format('n');
        $currentTwCode = Triwulan::determineTwCodeByMonth($currentMonth);

        foreach ($standardTws as $def) {
            $isActive = ($def['nama'] === $currentTwCode);
            Triwulan::updateOrCreate(
                [
                    'nama' => $def['nama'],
                ],
                [
                    'label' => $def['label'],
                    'rentang_bulan' => $def['rentang_bulan'],
                    'keterangan' => $def['keterangan'],
                    'bulan_mulai' => $def['bulan_mulai'],
                    'bulan_selesai' => $def['bulan_selesai'],
                    'urutan' => $def['urutan'],
                    'is_active' => $isActive,
                ]
            );
        }
    }
}
