<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MakeAdminUser extends Command
{
    /**
     * El nombre y la firma del comando
     */
    protected $signature = 'user:make-admin 
                            {email : El correo del usuario} 
                            {--password= : La contraseña (opcional)} 
                            {--name= : Nombre del usuario (opcional)}';

    /**
     * La descripción del comando
     */
    protected $description = 'Crea o promueve un usuario a administrador';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->option('password') ?? 'password';
        $name = $this->option('name') ?? 'Administrador';

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->info("Usuario admin listo:");
        $this->line("Email: {$user->email}");
        $this->line("Password: {$password}");
        $this->line("is_admin: {$user->is_admin}");

        return Command::SUCCESS;
    }
}
