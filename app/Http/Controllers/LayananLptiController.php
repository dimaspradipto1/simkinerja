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
            return redirect()->route('layanan.lpti.dukungan-teknis');
        }
        if (str_contains($userJabatan, 'SISTEM INFORMASI') || str_contains($userJabatan, 'PENGEMBANGAN SISTEM')) {
            return redirect()->route('layanan.lpti.pengembangan-si');
        }
        if (str_contains($userJabatan, 'INFRASTRUKTUR') || str_contains($userJabatan, 'JARINGAN')) {
            return redirect()->route('layanan.lpti.infrastruktur');
        }
        if (str_contains($userJabatan, 'PROGRAMMER') || str_contains($userJabatan, 'PEMELIHARAAN')) {
            return redirect()->route('layanan.lpti.pemeliharaan');
        }

        return redirect()->route('layanan.lpti.manajemen');
    }

    public function manajemen()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $userJabatan = strtoupper($user->jabatan ?? '');
        $hasAccess = $user->isAdmin() || $user->isSuperAdmin() || $user->isRektorOrSuperAdmin() || $user->isPimpinanRektorat() || $user->isPimpinanUnit() || str_contains($userJabatan, 'KEPALA') || str_contains($userJabatan, 'KA.');

        if (!$hasAccess) {
            return $this->index();
        }

        return view('pages.layanan-lpti.index', [
            'key' => 'manajemen',
            'title' => 'Manajemen LPTI',
            'roleAkses' => 'Kepala LPTI & Administrator',
            'roleBadge' => 'Kepala LPTI',
            'roleBadgeColor' => 'primary',
            'hasAccess' => $hasAccess,
            'icon' => 'bi-briefcase',
            'iconColor' => 'text-primary',
            'description' => 'Tata kelola, perencanaan strategis, dan manajemen administrasi operasional Lembaga Pengembangan Teknologi Informasi (LPTI) Universitas Ibnu Sina.',
            'category' => 'Manajemen & Administrasi LPTI',
            'pic' => 'Dr. MUHAMMAD ROPIANTO, S.Kom., M.Kom (Kepala LPTI)',
            'services' => [
                'Perencanaan dan Kebijakan Tata Kelola TI Kampus',
                'Penyusunan Anggaran dan Rencana Kerja Tahunan LPTI',
                'Koordinasi Pengadaan Lisensi Perangkat Lunak dan Hardware',
                'Manajemen Layanan Pengguna dan Pelaporan SLA',
            ]
        ]);
    }

    public function pengembanganSi()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $userJabatan = strtoupper($user->jabatan ?? '');
        $isLeader = $user->isAdmin() || $user->isSuperAdmin() || $user->isRektorOrSuperAdmin() || $user->isPimpinanRektorat() || $user->isPimpinanUnit() || str_contains($userJabatan, 'KEPALA') || str_contains($userJabatan, 'KA.');
        $hasAccess = $isLeader || str_contains($userJabatan, 'SISTEM INFORMASI') || str_contains($userJabatan, 'PENGEMBANGAN SISTEM');

        if (!$hasAccess) {
            return $this->index();
        }

        return view('pages.layanan-lpti.index', [
            'key' => 'pengembangan-si',
            'title' => 'Pengembangan Sistem Informasi dan Aplikasi',
            'roleAkses' => 'Divisi Pengembangan Sistem Informasi',
            'roleBadge' => 'Divisi SI',
            'roleBadgeColor' => 'info',
            'hasAccess' => $hasAccess,
            'icon' => 'bi-window-stack',
            'iconColor' => 'text-info',
            'description' => 'Layanan perancangan, analisis kebutuhan, dan pengembangan sistem informasi terintegrasi untuk mendukung kegiatan tridharma dan operasional universitas.',
            'category' => 'Divisi Pengembangan Sistem Informasi',
            'pic' => 'ANDI MUAMAR, S.Kom (Divisi Pengembangan Sistem Informasi & Aplikasi)',
            'services' => [
                'Pengembangan Sistem Informasi Baru Sesuai Kebutuhan Unit',
                'Integrasi API Antarsistem (PDDikti, Feeder, Bank, SIM Kinerja)',
                'Digitalisasi Layanan Akademik dan Kemahasiswaan',
                'Uji Coba Kelayakan dan Dokumentasi Sistem Informasi',
            ]
        ]);
    }

    public function infrastruktur()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $userJabatan = strtoupper($user->jabatan ?? '');
        $isLeader = $user->isAdmin() || $user->isSuperAdmin() || $user->isRektorOrSuperAdmin() || $user->isPimpinanRektorat() || $user->isPimpinanUnit() || str_contains($userJabatan, 'KEPALA') || str_contains($userJabatan, 'KA.');
        $hasAccess = $isLeader || str_contains($userJabatan, 'INFRASTRUKTUR') || str_contains($userJabatan, 'JARINGAN');

        if (!$hasAccess) {
            return $this->index();
        }

        return view('pages.layanan-lpti.index', [
            'key' => 'infrastruktur',
            'title' => 'Pengelolaan Infrastruktur dan Jaringan',
            'roleAkses' => 'Divisi Infrastruktur, Jaringan & Keamanan',
            'roleBadge' => 'Divisi Jaringan',
            'roleBadgeColor' => 'warning',
            'hasAccess' => $hasAccess,
            'icon' => 'bi-diagram-3',
            'iconColor' => 'text-warning',
            'description' => 'Pengelolaan dan pemeliharaan pusat data (data center), server kampus, konektivitas internet, hotspot Wi-Fi, dan keamanan jaringan universitas.',
            'category' => 'Divisi Infrastruktur, Jaringan & Keamanan',
            'pic' => 'DICKY ASHRUL IBRAHIM, S.Kom (Divisi Infrastruktur & Jaringan)',
            'services' => [
                'Pemeliharaan Server Kampus, Cloud VPS & Database Server',
                'Pengelolaan Bandwidth dan Akses Internet / Wi-Fi Kampus',
                'Konfigurasi Keamanan Jaringan, Firewall, dan Backup Rutin',
                'Pemasangan Titik Jaringan / Access Point Baru di Lingkungan Kampus',
            ]
        ]);
    }

    public function pemeliharaan()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $userJabatan = strtoupper($user->jabatan ?? '');
        $isLeader = $user->isAdmin() || $user->isSuperAdmin() || $user->isRektorOrSuperAdmin() || $user->isPimpinanRektorat() || $user->isPimpinanUnit() || str_contains($userJabatan, 'KEPALA') || str_contains($userJabatan, 'KA.');
        $hasAccess = $isLeader || str_contains($userJabatan, 'PEMELIHARAAN') || str_contains($userJabatan, 'APLIKASI') || str_contains($userJabatan, 'PROGRAMMER') || str_contains($userJabatan, 'DATABASE');

        if (!$hasAccess) {
            return $this->index();
        }

        return view('pages.layanan-lpti.index', [
            'key' => 'pemeliharaan',
            'title' => 'Pengembangan dan Pemeliharaan Aplikasi',
            'roleAkses' => 'Programmer & Pemeliharaan Aplikasi',
            'roleBadge' => 'Programmer',
            'roleBadgeColor' => 'danger',
            'hasAccess' => $hasAccess,
            'icon' => 'bi-code-slash',
            'iconColor' => 'text-danger',
            'description' => 'Pemeliharaan berkala, perbaikan bug, patching keamanan, serta pembaruan fitur pada seluruh aplikasi dan portal web di lingkungan Universitas Ibnu Sina.',
            'category' => 'Divisi Pemeliharaan Aplikasi & Database',
            'pic' => 'Programmer & Database Administrator LPTI',
            'services' => [
                'Perbaikan Error / Bug Sistem Operasional Kampus',
                'Pembaruan Versi dan Optimasi Kinerja Database',
                'Pemeliharaan Website Universitas, Fakultas, dan Lembaga',
                'Backup & Recovery Source Code dan Database Aplikasi',
            ]
        ]);
    }

    public function dukunganTeknis()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $userJabatan = strtoupper($user->jabatan ?? '');
        $isLeader = $user->isAdmin() || $user->isSuperAdmin() || $user->isRektorOrSuperAdmin() || $user->isPimpinanRektorat() || $user->isPimpinanUnit() || str_contains($userJabatan, 'KEPALA') || str_contains($userJabatan, 'KA.');
        $isUserTroubleshooting = str_contains($userJabatan, 'TROUBLESHOOTING') || str_contains($userJabatan, 'HELPDESK') || str_contains($userJabatan, 'DUKUNGAN TEKNIS');
        $isOtherLptiStaff = str_contains($userJabatan, 'SISTEM INFORMASI') || str_contains($userJabatan, 'PENGEMBANGAN SISTEM') || str_contains($userJabatan, 'INFRASTRUKTUR') || str_contains($userJabatan, 'JARINGAN') || str_contains($userJabatan, 'PROGRAMMER') || str_contains($userJabatan, 'PEMELIHARAAN');

        if (!$isLeader && $isOtherLptiStaff && !$isUserTroubleshooting) {
            return $this->index();
        }

        $hasAccess = $isLeader || $isUserTroubleshooting;

        return view('pages.layanan-lpti.index', [
            'key' => 'dukungan-teknis',
            'title' => 'Layanan Dukungan Teknis',
            'roleAkses' => 'Staff Layanan Troubleshooting & Helpdesk',
            'roleBadge' => 'Troubleshooting',
            'roleBadgeColor' => 'success',
            'hasAccess' => $hasAccess,
            'icon' => 'bi-headset',
            'iconColor' => 'text-success',
            'description' => 'Helpdesk dan dukungan teknis langsung untuk civitas akademika, meliputi troubleshooting perangkat, instalasi software resmi, dan akun pengguna.',
            'category' => 'Helpdesk & Layanan Troubleshooting',
            'pic' => 'Rusdiyanto, S.Kom (Staff Layanan Troubleshooting & Helpdesk)',
            'services' => [
                'Troubleshooting Perangkat Komputer / Laptop Staff dan Dosen',
                'Bantuan Akun Resmi Kampus (Email, SIAKAD, SIM Kinerja, Wi-Fi)',
                'Dukungan Teknis Audio Visual & Jaringan untuk Acara / Rapat',
                'Instalasi Perangkat Lunak Resmi dan Konfigurasi Printer Kampus',
            ]
        ]);
    }
}
