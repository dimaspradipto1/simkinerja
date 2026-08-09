<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RencanaKerja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CleanDuplicateUsersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Clean by exact name
        $duplicates = User::select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name');

        foreach ($duplicates as $name) {
            $users = User::where('name', $name)->orderBy('id', 'desc')->get();
            $keep = $users->first();
            foreach ($users->slice(1) as $dup) {
                RencanaKerja::where('user_id', $dup->id)->update(['user_id' => $keep->id]);
                $dup->delete();
            }
        }

        // 2. Clean by normalized base name (e.g. LARISANG)
        $allUsers = User::all();
        $seenBaseNames = [];
        foreach ($allUsers as $u) {
            //Extract main name without titles
            $cleanName = strtoupper(preg_replace('/[^A-Za-z\s]/', '', $u->name));
            $words = array_filter(explode(' ', $cleanName), fn($w) => strlen($w) > 3 && !in_array($w, ['ASSOC', 'PROF', 'DOKTOR', 'SKM', 'MKKK', 'AKUN', 'TEKNIK', 'SINS', 'KES']));
            $key = implode('_', array_slice($words, 0, 3));

            if (!empty($key) && isset($seenBaseNames[$key])) {
                $keepId = $seenBaseNames[$key];
                RencanaKerja::where('user_id', $u->id)->update(['user_id' => $keepId]);
                $u->delete();
            } else if (!empty($key)) {
                $seenBaseNames[$key] = $u->id;
            }
        }
    }
}
