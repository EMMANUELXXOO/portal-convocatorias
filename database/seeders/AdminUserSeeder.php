<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'informatica@escuelacruzrojatijuana.org'],
            [
                'name' => 'AdministradorSistemas',
                'password' => Hash::make('root195091'),
                'role' => 'admin',   // o 'is_admin' => true
            ]
        );
    }
}
