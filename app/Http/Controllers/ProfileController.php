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
            'nomor_wa' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s().-]{8,30}$/'],
            'nidn' => ['nullable', 'string', 'max:50'],
            'unit' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'jabatan_kepanitiaan' => ['nullable', 'array'],
            'jabatan_kepanitiaan.*.nama' => ['nullable', 'string', 'max:255'],
            'jabatan_kepanitiaan.*.jabatan' => ['nullable', 'string', 'max:255'],
        ];

        // Only allow admin or superadmin to change their own role from profile
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            $rules['roles'] = ['nullable'];
        }

        $validated = $request->validate($rules);

        if (isset($validated['roles'])) {
            if (is_array($validated['roles'])) {
                $validated['roles'] = implode(', ', array_filter(array_map('trim', $validated['roles'])));
            } else {
                $validated['roles'] = trim((string) $validated['roles']);
            }
        }

        if (isset($validated['jabatan_kepanitiaan']) && is_array($validated['jabatan_kepanitiaan'])) {
            $cleanKepanitiaan = [];
            foreach ($validated['jabatan_kepanitiaan'] as $item) {
                $nama = trim($item['nama'] ?? '');
                $jabatan = trim($item['jabatan'] ?? '');
                if (!empty($nama) && !empty($jabatan)) {
                    $cleanKepanitiaan[] = [
                        'nama' => $nama,
                        'jabatan' => $jabatan,
                    ];
                }
            }
            $validated['jabatan_kepanitiaan'] = !empty($cleanKepanitiaan) ? $cleanKepanitiaan : null;
        }

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
