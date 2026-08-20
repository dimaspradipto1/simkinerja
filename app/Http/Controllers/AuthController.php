<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RealRashid\SweetAlert\Facades\Alert;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('layouts.auth.login');
    }

    public function loginproses(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Email atau NUP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $login = trim((string) $request->input('username'));

        // Cari user berdasarkan NUP atau Email
        $user = User::where('nidn', $login)
            ->orWhereRaw('LOWER(TRIM(email)) = ?', [strtolower($login)])
            ->first();

        if ($user && Hash::check($request->input('password'), $user->password)) {
            if (!$user->is_active) {
                Alert::error('Akun Nonaktif', 'Akun Anda sedang dinonaktifkan. Silakan hubungi admin.')->toToast();
                return back()->withErrors([
                    'login_error' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi admin.',
                ])->withInput();
            }

            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();

            Alert::success('Login Berhasil', 'Selamat datang, ' . $user->name)
                ->toToast()
                ->autoClose(4000)
                ->timerProgressBar();

            return redirect()->intended(route('dashboard'));
        }

        Alert::error('Login Gagal', 'Email/NUP atau Password salah')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return back()->withErrors([
            'login_error' => 'Email/NUP atau Password yang Anda masukkan salah.',
        ])->withInput();
    }

    public function register()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('layouts.auth.register');
    }

    public function registerproses(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'nidn' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'roles' => 'nullable|string|max:100',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nidn' => $request->nidn ?? '-',
            'unit' => $request->unit ?? '-',
            'jabatan' => $request->jabatan ?? '-',
            'roles' => $request->roles ?? 'Standar',
            'status' => 'Aktif',
            'is_active' => true,
        ]);

        Alert::success('Registrasi Berhasil', 'Silakan login dengan akun Anda')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Alert::success('Logout Berhasil', 'Anda telah keluar dari sistem')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('login');
    }
}
