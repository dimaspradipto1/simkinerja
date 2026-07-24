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
        PeriodeAkademik::create($validated);

        Alert::success('Berhasil', 'Periode Akademik berhasil ditambahkan.');
        return redirect()->route('periode-akademik.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $periodeAkademik = PeriodeAkademik::findOrFail($id);
        return view('pages.periode-akademik.show', compact('periodeAkademik'));
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
        $periodeAkademik->update($request->validated());

        Alert::success('Berhasil', 'Data periode akademik berhasil diperbarui.');
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

