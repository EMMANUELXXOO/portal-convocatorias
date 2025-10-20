<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class RemoveAdminUser extends Command
{
    protected $signature = 'user:remove-admin {email}';
    protected $description = 'Revoca permisos de administrador a un usuario';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Usuario con email {$email} no encontrado.");
            return Command::FAILURE;
        }

        $user->is_admin = false;
        $user->save();

        $this->info("Usuario {$email} ya no es administrador.");
        return Command::SUCCESS;
    }
}
