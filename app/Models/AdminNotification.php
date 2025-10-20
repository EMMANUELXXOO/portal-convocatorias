<?php
// app/Models/AdminNotification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $table = 'admin_notifications';
    protected $fillable = ['titulo','mensaje','nivel','activo','inicio','fin'];
    protected $casts = ['activo'=>'boolean','inicio'=>'datetime','fin'=>'datetime'];

    public function scopeVigentes($q)
    {
        $now = now();
        return $q->where('activo', true)
                 ->where(function($qq) use ($now) {
                    $qq->whereNull('inicio')->orWhere('inicio','<=',$now);
                 })
                 ->where(function($qq) use ($now) {
                    $qq->whereNull('fin')->orWhere('fin','>=',$now);
                 });
    }
}
