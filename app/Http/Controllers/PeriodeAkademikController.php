<?php

namespace App\Http\Controllers;

use App\DataTables\PeriodeAkademikDataTable;
use App\Http\Requests\PeriodeAkademikRequest;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class PeriodeAkademikController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PeriodeAkademikDataTable $dataTable)
    {
        return $dataTable->render('pages.periode-akademik.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.periode-akademik.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PeriodeAkademikRequest $request)
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');
        if ($validated['is_active']) {
            PeriodeAkademik::query()->update(['is_active' => false]);
        }
        PeriodeAkademik::create($validated);

        Alert::success('Berhasil', 'Periode Akademik berhasil ditambahkan.');
        return redirect()->route('periode-akademik.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('periode-akademik.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $periodeAkademik = PeriodeAkademik::findOrFail($id);
        return view('pages.periode-akademik.edit', compact('periodeAkademik'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PeriodeAkademikRequest $request, string $id)
    {
        $periodeAkademik = PeriodeAkademik::findOrFail($id);
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');
        if ($validated['is_active']) {
            PeriodeAkademik::where('id', '!=', $id)->update(['is_active' => false]);
        }
        $periodeAkademik->update($validated);

        Alert::success('Berhasil', 'Data periode akademik berhasil diperbarui.');
        return redirect()->route('periode-akademik.index');
    }

    /**
     * Jadikan periode akademik sebagai periode aktif
     */
    public function setActive(string $id)
    {
        $periodeAkademik = PeriodeAkademik::findOrFail($id);
        PeriodeAkademik::where('id', '!=', $id)->update(['is_active' => false]);
        $periodeAkademik->update(['is_active' => true]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Periode Akademik ' . $periodeAkademik->formatted_ta . ' berhasil diaktifkan.',
            ]);
        }

        Alert::success('Berhasil', 'Periode Akademik ' . $periodeAkademik->formatted_ta . ' berhasil diaktifkan.');
        return redirect()->route('periode-akademik.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $periodeAkademik = PeriodeAkademik::findOrFail($id);
        $periodeAkademik->delete();

        return response()->json([
            'success' => true,
            'message' => 'Periode akademik berhasil dihapus.'
        ]);
    }
}

