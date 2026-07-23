<?php

namespace App\Http\Controllers;

use App\Models\RencanaKerja;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $tasksQuery = RencanaKerja::query();
        $usersQuery = User::query();

        if ($user) {
            if ($user->isAdmin() || $user->isPimpinanRektorat()) {
                // Superadmin, Admin, Rektorat -> Akses seluruh data universitas
            } elseif ($user->isPimpinanUnit()) {
                // Pimpinan Unit -> Akses seluruh tugas di unitnya
                $tasksQuery->whereHas('user', function ($q) use ($user) {
                    $q->where('unit', $user->unit);
                });
                $usersQuery->where('unit', $user->unit);
            } else {
                // Staff / Pegawai Regular -> Hanya akses tugas milik sendiri
                $tasksQuery->where('user_id', $user->id);
                $usersQuery->where('unit', $user->unit);
            }
        }

        $totalTugas = (clone $tasksQuery)->count();
        $tugasBelumMulai = (clone $tasksQuery)->where('status', 'Belum Dimulai')->count();
        $tugasProses = (clone $tasksQuery)->where('status', 'Proses')->count();
        $tugasSelesai = (clone $tasksQuery)->where('status', 'Selesai')->count();

        $persentaseSelesai = $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100) : 0;

        $recentTasks = (clone $tasksQuery)->with('user')->latest()->take(6)->get();
        $totalPegawai = $usersQuery->count();

        return view('layouts.dashboard.index', compact(
            'totalTugas',
            'tugasBelumMulai',
            'tugasProses',
            'tugasSelesai',
            'persentaseSelesai',
            'recentTasks',
            'totalPegawai'
        ));
    }
}
