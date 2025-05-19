<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Créer un super administrateur
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@universite.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
        ]);

        // Créer un administrateur simple
        User::create([
            'name' => 'Admin',
            'email' => 'admin@universite.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }
}
