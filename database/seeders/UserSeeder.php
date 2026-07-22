<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
          [
            'name' => 'superadmin',
            'email' => 'superadmin@uis.ac.id',
            'roles' => 'superadmin',
            'nidn' => '-',
            'jabatan' => '-',
            'status' => '-',
            'unit' => '-',
            'password' => Hash::make('password'),
            'is_active' => 1,
            'created_at'=> now(),
            'updated_at' => now()
          ],
          [
            'name' => 'admin',
            'email' => 'admin@uis.ac.id',
            'roles' => 'admin',
            'nidn' => '-',
            'jabatan' => '-',
            'status' => '-',
            'unit' => '-',
            'password' => Hash::make('password'),
            'is_active' => 1,
            'created_at'=> now(),
            'updated_at' => now()
          ]
        ];

        foreach ($users as $user) {
          User::create($user);
        }
    }
}
