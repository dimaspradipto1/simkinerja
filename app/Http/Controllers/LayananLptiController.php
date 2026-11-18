<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LayananLptiController extends Controller
{
    private function canAccessLpti(?User $user, ?string $division = null): bool
    {
        if (!$user) return false;
        if ($user->isSuperAdmin() || $user->hasRole('Super Admin') || ($user->role ?? '') === 'Super Admin') {
            return true;
        }

        $uJabatan = strtoupper($user->jabatan ?? '');
        $uUnit = strtoupper($user->unit ?? '');

        $isKepala = str_contains($uJabatan, 'KEPALA LPTI') || 
                    str_contains($uJabatan, 'KA. LPTI') || 
                    str_contains($uJabatan, 'KA. UPT LPTI') || 
                    (str_contains($uUnit, 'LPTI') && (str_contains($uJabatan, 'KEPALA') || str_contains($uJabatan, 'KA.'))) ||
                    (str_contains($uUnit, 'TEKNIK INFORMASI') && (str_contains($uJabatan, 'KEPALA') || str_contains($uJabatan, 'KA.')));

        if ($isKepala) return true;

        if ($division === 'manajemen') {
            return false;
        }

        if ($division === 'si') {
            return (str_contains($uJabatan, 'SISTEM INFORMASI') || str_contains($uJabatan, 'PENGEMBANGAN SISTEM')) && !str_contains($uJabatan, 'PRODI');
        }

        if ($division === 'jaringan') {
            return str_contains($uJabatan, 'INFRASTRUKTUR') || str_contains($uJabatan, 'JARINGAN');
        }

        if ($division === 'programmer') {
            return str_contains($uJabatan, 'PROGRAMMER') || str_contains($uJabatan, 'PEMELIHARAAN');
        }

        if ($division === 'troubleshooting') {
            return str_contains($uJabatan, 'TROUBLESHOOTING') || str_contains($uJabatan, 'HELPDESK') || str_contains($uJabatan, 'DUKUNGAN TEKNIS');
        }

        // Generic LPTI member
        return str_contains($uJabatan, 'LPTI') || 
               str_contains($uJabatan, 'SISTEM INFORMASI') || 
               str_contains($uJabatan, 'JARINGAN') || 
               str_contains($uJabatan, 'INFRASTRUKTUR') || 
               str_contains($uJabatan, 'PROGRAMMER') || 
               str_contains($uJabatan, 'TROUBLESHOOTING') ||
               (str_contains($uUnit, 'LPTI') && !str_contains($uUnit, 'PRODI')) || 
               str_contains($uUnit, 'TEKNIK INFORMASI');
    }

    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        if (!$this->canAccessLpti($user)) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke Modul Layanan LPTI.');
        }

        $userJabatan = strtoupper($user->jabatan ?? '');

        if (str_contains($userJabatan, 'TROUBLESHOOTING') || str_contains($userJabatan, 'HELPDESK')) {
            return $this->dukunganTeknis();
        }
        if ((str_contains($userJabatan, 'SISTEM INFORMASI') || str_contains($userJabatan, 'PENGEMBANGAN SISTEM')) && !str_contains($userJabatan, 'PRODI')) {
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

        if (!$this->canAccessLpti($user, 'manajemen')) {
            return redirect()->route('dashboard')->with('error', 'Hanya Kepala LPTI yang berhak mengakses Manajemen LPTI.');
        }

        return redirect()->route('rencana-kerja.index', ['jabatan' => 'KEPALA LPTI', 'from' => 'layanan']);
    }

    public function pengembanganSi()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        if (!$this->canAccessLpti($user, 'si')) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke Layanan Pengembangan Sistem Informasi.');
        }

        return redirect()->route('rencana-kerja.index', ['jabatan' => 'DIVISI PENGEMBANGAN SISTEM INFORMASI DAN APLIKASI - LPTI BAUK', 'from' => 'layanan']);
    }

    public function infrastruktur()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        if (!$this->canAccessLpti($user, 'jaringan')) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke Layanan Infrastruktur dan Jaringan.');
        }

        return redirect()->route('rencana-kerja.index', ['jabatan' => 'DIVISI INFRASTRUKTUR DAN JARINGAN', 'from' => 'layanan']);
    }

    public function pemeliharaan()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        if (!$this->canAccessLpti($user, 'programmer')) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke Layanan Programmer / Pemeliharaan Aplikasi.');
        }

        return redirect()->route('rencana-kerja.index', ['jabatan' => 'PROGRAMMER', 'from' => 'layanan']);
    }

    public function dukunganTeknis()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        if (!$this->canAccessLpti($user, 'troubleshooting')) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke Layanan Dukungan Teknis.');
        }

        return redirect()->route('rencana-kerja.index', ['jabatan' => 'STAFF LAYANAN TROUBLESHOOTING', 'from' => 'layanan']);
    }
}
