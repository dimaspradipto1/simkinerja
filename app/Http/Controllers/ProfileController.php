<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class ProfileController extends Controller
{
    /**
     * Display the user profile page.
     */
    public function index()
    {
        $user = Auth::user();
        return view('pages.profile.index', compact('user'));
    }

    /**
     * Update the user profile information.
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nidn' => ['nullable', 'string', 'max:50'],
            'unit' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'jabatan_pkkmb' => ['nullable', 'string', 'max:150'],
            'jabatan_esq' => ['nullable', 'string', 'max:150'],
            'jabatan_milad' => ['nullable', 'string', 'max:150'],
            'jabatan_kuliah_umum' => ['nullable', 'string', 'max:150'],
        ];

        // Only allow admin or superadmin to change their own role from profile
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            $rules['roles'] = ['nullable', 'string', 'max:100'];
        }

        $validated = $request->validate($rules);

        $user->update($validated);

        Alert::success('Berhasil', 'Profil Anda berhasil diperbarui.')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->route('profile');
    }

    /**
     * Display the separate change password page.
     */
    public function editPassword()
    {
        $user = Auth::user();
        return view('pages.profile.password', compact('user'));
    }

    /**
     * Update the user password directly without current password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 6 karakter.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        Alert::success('Berhasil', 'Password akun Anda berhasil diperbarui.')
            ->toToast()
            ->autoClose(4000)
            ->timerProgressBar();

        return redirect()->back();
    }
}
