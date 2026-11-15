<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LayananLptiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $userJabatan = strtoupper($user->jabatan ?? '');
        if (str_contains($userJabatan, 'TROUBLESHOOTING') || str_contains($userJabatan, 'HELPDESK')) {
            return $this->dukunganTeknis();
        }
        if (str_contains($userJabatan, 'SISTEM INFORMASI') || str_contains($userJabatan, 'PENGEMBANGAN SISTEM')) {
            return $this->pengembanganSi();
        }
        if (str_contains($userJabatan, 'INFRASTRUKTUR') || str_contains($userJabatan, 'JARINGAN')) {
            return $this->infrastruktur();
        }
        if (str_contains($userJabatan, 'PROGRAMMER') || str_contains($userJabatan, 'PEMELIHARAAN')) {
            return $this->pemeliharaan();
        }

        return $this->manajemen();
    }

    public function manajemen()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        return redirect()->route('rencana-kerja.index', ['jabatan' => 'KEPALA LPTI', 'from' => 'layanan']);
    }

    public function pengembanganSi()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        return redirect()->route('rencana-kerja.index', ['jabatan' => 'DIVISI PENGEMBANGAN SISTEM INFORMASI DAN APLIKASI - LPTI BAUK', 'from' => 'layanan']);
    }

    public function infrastruktur()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        return redirect()->route('rencana-kerja.index', ['jabatan' => 'DIVISI INFRASTRUKTUR DAN JARINGAN', 'from' => 'layanan']);
    }

    public function pemeliharaan()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        return redirect()->route('rencana-kerja.index', ['jabatan' => 'PROGRAMMER', 'from' => 'layanan']);
    }

    public function dukunganTeknis()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        return redirect()->route('rencana-kerja.index', ['jabatan' => 'STAFF LAYANAN TROUBLESHOOTING', 'from' => 'layanan']);
    }
}
