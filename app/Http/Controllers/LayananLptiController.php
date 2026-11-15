<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LayananLptiController extends Controller
{
    public function index()
    {
        return redirect()->route('layanan.lpti.manajemen');
    }

    public function manajemen()
    {
        return view('pages.layanan-lpti.index', [
            'key' => 'manajemen',
            'title' => 'Manajemen LPTI',
            'icon' => 'bi-briefcase',
            'iconColor' => 'text-primary',
            'description' => 'Tata kelola, perencanaan strategis, dan manajemen administrasi operasional Lembaga Pengembangan Teknologi Informasi (LPTI) Universitas Ibnu Sina.',
            'category' => 'Manajemen & Administrasi LPTI',
            'pic' => 'Kepala LPTI & Divisi Tata Usaha',
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
        return view('pages.layanan-lpti.index', [
            'key' => 'pengembangan-si',
            'title' => 'Pengembangan Sistem Informasi dan Aplikasi',
            'icon' => 'bi-window-stack',
            'iconColor' => 'text-info',
            'description' => 'Layanan perancangan, analisis kebutuhan, dan pengembangan sistem informasi terintegrasi untuk mendukung kegiatan tridharma dan operasional universitas.',
            'category' => 'Divisi Pengembangan Sistem Informasi',
            'pic' => 'Divisi Sistem Informasi dan Programmer',
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
        return view('pages.layanan-lpti.index', [
            'key' => 'infrastruktur',
            'title' => 'Pengelolaan Infrastruktur dan Jaringan',
            'icon' => 'bi-diagram-3',
            'iconColor' => 'text-warning',
            'description' => 'Pengelolaan dan pemeliharaan pusat data (data center), server kampus, konektivitas internet, hotspot Wi-Fi, dan keamanan jaringan universitas.',
            'category' => 'Divisi Infrastruktur, Jaringan & Keamanan',
            'pic' => 'Network Administrator & Staff Jaringan',
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
        return view('pages.layanan-lpti.index', [
            'key' => 'pemeliharaan',
            'title' => 'Pengembangan dan Pemeliharaan Aplikasi',
            'icon' => 'bi-code-slash',
            'iconColor' => 'text-danger',
            'description' => 'Pemeliharaan berkala, perbaikan bug, patching keamanan, serta pembaruan fitur pada seluruh aplikasi dan portal web di lingkungan Universitas Ibnu Sina.',
            'category' => 'Divisi Pemeliharaan Aplikasi & Database',
            'pic' => 'Programmer & Database Administrator',
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
        return view('pages.layanan-lpti.index', [
            'key' => 'dukungan-teknis',
            'title' => 'Layanan Dukungan Teknis',
            'icon' => 'bi-headset',
            'iconColor' => 'text-success',
            'description' => 'Helpdesk dan dukungan teknis langsung untuk civitas akademika, meliputi troubleshooting perangkat, instalasi software resmi, dan akun pengguna.',
            'category' => 'Helpdesk & Layanan Troubleshooting',
            'pic' => 'Staff Layanan Troubleshooting & Helpdesk',
            'services' => [
                'Troubleshooting Perangkat Komputer / Laptop Staff dan Dosen',
                'Bantuan Akun Resmi Kampus (Email, SIAKAD, SIM Kinerja, Wi-Fi)',
                'Dukungan Teknis Audio Visual & Jaringan untuk Acara / Rapat',
                'Instalasi Perangkat Lunak Resmi dan Konfigurasi Printer Kampus',
            ]
        ]);
    }
}
