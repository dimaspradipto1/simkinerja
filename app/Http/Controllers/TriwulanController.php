<?php

namespace App\Http\Controllers;

use App\Http\Requests\TriwulanRequest;
use App\Models\PeriodeAkademik;
use App\Models\Triwulan;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class TriwulanController extends Controller
{
    /**
     * Tampilkan daftar Triwulan (TW)
     */
    public function index(Request $request)
    {
        $periodeAkademiks = PeriodeAkademik::orderByDesc('is_active')->orderBy('id', 'asc')->get();
        $activePeriode = PeriodeAkademik::getActive();
        $selectedPeriodeId = $request->input('periode_id', $activePeriode ? $activePeriode->id : null);

        $query = Triwulan::with('periodeAkademik');
        if ($selectedPeriodeId) {
            $query->where('periode_akademik_id', $selectedPeriodeId);
        }
        $triwulans = $query->orderBy('urutan', 'asc')->get();

        return view('pages.triwulan.index', compact('triwulans', 'periodeAkademiks', 'selectedPeriodeId', 'activePeriode'));
    }

    /**
     * Form tambah Triwulan
     */
    public function create(Request $request)
    {
        $periodeAkademiks = PeriodeAkademik::orderByDesc('is_active')->orderBy('id', 'asc')->get();
        $activePeriode = PeriodeAkademik::getActive();
        $selectedPeriodeId = $request->input('periode_id', $activePeriode ? $activePeriode->id : null);
        $standardTws = Triwulan::getStandardDefinitions();

        return view('pages.triwulan.create', compact('periodeAkademiks', 'selectedPeriodeId', 'standardTws'));
    }

    /**
     * Simpan Triwulan baru
     */
    public function store(TriwulanRequest $request)
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['is_active']) {
            Triwulan::where('periode_akademik_id', $validated['periode_akademik_id'])
                ->update(['is_active' => false]);
        }

        Triwulan::create($validated);

        Alert::success('Berhasil', 'Data Triwulan berhasil ditambahkan.');
        return redirect()->route('triwulan.index', ['periode_id' => $validated['periode_akademik_id']]);
    }

    /**
     * Form edit Triwulan
     */
    public function edit(Triwulan $triwulan)
    {
        $periodeAkademiks = PeriodeAkademik::orderByDesc('is_active')->orderBy('id', 'asc')->get();
        $standardTws = Triwulan::getStandardDefinitions();

        return view('pages.triwulan.edit', compact('triwulan', 'periodeAkademiks', 'standardTws'));
    }

    /**
     * Update data Triwulan
     */
    public function update(TriwulanRequest $request, Triwulan $triwulan)
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['is_active']) {
            Triwulan::where('periode_akademik_id', $validated['periode_akademik_id'])
                ->where('id', '!=', $triwulan->id)
                ->update(['is_active' => false]);
        }

        $triwulan->update($validated);

        Alert::success('Berhasil', 'Data Triwulan berhasil diperbarui.');
        return redirect()->route('triwulan.index', ['periode_id' => $validated['periode_akademik_id']]);
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
        Triwulan::where('periode_akademik_id', $triwulan->periode_akademik_id)
            ->where('id', '!=', $triwulan->id)
            ->update(['is_active' => false]);

        $triwulan->update(['is_active' => true]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Triwulan ' . $triwulan->nama . ' (' . $triwulan->rentang_bulan . ') berhasil diaktifkan.',
            ]);
        }

        Alert::success('Berhasil', 'Triwulan ' . $triwulan->nama . ' berhasil diaktifkan.');
        return redirect()->back();
    }

    /**
     * Generate otomatis 4 Triwulan standar untuk periode akademik terpilih
     * Sesuai Ketentuan:
     * TW1 (Sep–Nov)
     * TW2 (Des–Feb)
     * TW3 (Mar–Mei)
     * TW4 (Jun–Agu)
     */
    public function generateStandard(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $periode = PeriodeAkademik::findOrFail($periodeId);

        $standardTws = Triwulan::getStandardDefinitions();
        $currentMonth = (int) now()->format('n');
        $currentTwCode = Triwulan::determineTwCodeByMonth($currentMonth);

        foreach ($standardTws as $def) {
            $isActive = ($def['nama'] === $currentTwCode);
            Triwulan::updateOrCreate(
                [
                    'periode_akademik_id' => $periode->id,
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

        Alert::success('Berhasil', '4 Triwulan (TW1 s/d TW4) berhasil dibuat/disinkronkan untuk ' . $periode->formatted_ta);
        return redirect()->route('triwulan.index', ['periode_id' => $periode->id]);
    }
}
