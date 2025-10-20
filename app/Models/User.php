<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable; // 👈 sin HasApiTokens

   protected $fillable = [
    'name','email','password','role',
];
protected $casts = [
    'email_verified_at' => 'datetime',
];

    protected $hidden = ['password','remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function perfilPostulante()
    {
        return $this->hasOne(PerfilPostulante::class, 'user_id');
    }

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class, 'user_id');
    }
}
