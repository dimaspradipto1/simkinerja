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
        $userJabatan = strtoupper($user->jabatan ?? '');
        $isAdminOrKepala = $user->isAdmin() || $user->isSuperAdmin() || $user->isRektorOrSuperAdmin() || $user->isPimpinanRektorat() || $user->isPimpinanUnit() || str_contains($userJabatan, 'KEPALA') || str_contains($userJabatan, 'KA.');

        if (!$isAdminOrKepala) {
            return redirect()->route('rencana-kerja.index');
        }

        return redirect()->route('rencana-kerja.index', ['jabatan' => 'KEPALA LPTI']);
    }

    public function pengembanganSi()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $userJabatan = strtoupper($user->jabatan ?? '');
        $isLeader = $user->isAdmin() || $user->isSuperAdmin() || $user->isRektorOrSuperAdmin() || $user->isPimpinanRektorat() || $user->isPimpinanUnit() || str_contains($userJabatan, 'KEPALA') || str_contains($userJabatan, 'KA.');
        $isUserSi = str_contains($userJabatan, 'SISTEM INFORMASI') || str_contains($userJabatan, 'PENGEMBANGAN SISTEM');

        if (!$isLeader && !$isUserSi) {
            return redirect()->route('rencana-kerja.index');
        }

        if ($user && !$isLeader) {
            return redirect()->route('rencana-kerja.index');
        }

        return redirect()->route('rencana-kerja.index', ['jabatan' => 'DIVISI PENGEMBANGAN SISTEM INFORMASI DAN APLIKASI - LPTI BAUK']);
    }

    public function infrastruktur()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $userJabatan = strtoupper($user->jabatan ?? '');
        $isLeader = $user->isAdmin() || $user->isSuperAdmin() || $user->isRektorOrSuperAdmin() || $user->isPimpinanRektorat() || $user->isPimpinanUnit() || str_contains($userJabatan, 'KEPALA') || str_contains($userJabatan, 'KA.');
        $isUserJaringan = str_contains($userJabatan, 'INFRASTRUKTUR') || str_contains($userJabatan, 'JARINGAN');

        if (!$isLeader && !$isUserJaringan) {
            return redirect()->route('rencana-kerja.index');
        }

        if ($user && !$isLeader) {
            return redirect()->route('rencana-kerja.index');
        }

        return redirect()->route('rencana-kerja.index', ['jabatan' => 'DIVISI INFRASTRUKTUR, JARINGAN, DAN LAYANAN TROUBLESHOOTING']);
    }

    public function pemeliharaan()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $userJabatan = strtoupper($user->jabatan ?? '');
        $isLeader = $user->isAdmin() || $user->isSuperAdmin() || $user->isRektorOrSuperAdmin() || $user->isPimpinanRektorat() || $user->isPimpinanUnit() || str_contains($userJabatan, 'KEPALA') || str_contains($userJabatan, 'KA.');
        $isUserProgrammer = str_contains($userJabatan, 'PEMELIHARAAN') || str_contains($userJabatan, 'PROGRAMMER');

        if (!$isLeader && !$isUserProgrammer) {
            return redirect()->route('rencana-kerja.index');
        }

        if ($user && !$isLeader) {
            return redirect()->route('rencana-kerja.index');
        }

        return redirect()->route('rencana-kerja.index', ['jabatan' => 'PROGRAMMER']);
    }

    public function dukunganTeknis()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $userJabatan = strtoupper($user->jabatan ?? '');
        $isLeader = $user->isAdmin() || $user->isSuperAdmin() || $user->isRektorOrSuperAdmin() || $user->isPimpinanRektorat() || $user->isPimpinanUnit() || str_contains($userJabatan, 'KEPALA') || str_contains($userJabatan, 'KA.');
        $isUserTroubleshooting = str_contains($userJabatan, 'TROUBLESHOOTING') || str_contains($userJabatan, 'HELPDESK') || str_contains($userJabatan, 'DUKUNGAN TEKNIS');

        if ($user && !$isLeader) {
            return redirect()->route('rencana-kerja.index');
        }

        return redirect()->route('rencana-kerja.index', ['jabatan' => 'STAFF LAYANAN TROUBLESHOOTING']);
    }
}
