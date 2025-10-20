<?php

// app/Models/AdminNotice.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotice extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo','mensaje','nivel','audiencia',
        'visible_desde','visible_hasta','activo','created_by'
    ];

    protected $casts = [
        'visible_desde' => 'datetime',
        'visible_hasta' => 'datetime',
        'activo'        => 'boolean',
    ];

    public function autor(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Alcance para los avisos vigentes a “ahora” para cierta audiencia. */
    public function scopeVigentesPara($q, string $aud = 'todos') {
        $now = now();
        return $q->where('activo', true)
            ->where(function($qq) use ($aud){
                $qq->where('audiencia','todos')->orWhere('audiencia',$aud);
            })
            ->where(function($qq) use ($now){
                $qq->whereNull('visible_desde')->orWhere('visible_desde','<=',$now);
            })
            ->where(function($qq) use ($now){
                $qq->whereNull('visible_hasta')->orWhere('visible_hasta','>=',$now);
            })
            ->orderByDesc('id');
    }
}
