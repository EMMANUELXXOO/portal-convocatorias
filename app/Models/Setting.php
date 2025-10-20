<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key','value'];
    public $timestamps = false;
    protected $table = 'settings';

    public static function get(string $key, $default=null)
    {
        $row = static::where('key',$key)->first();
        return $row?->value ?? $default;
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key'=>$key], ['value'=>$value]);
    }

    // helpers para secretos (encripta/descrifra)
    public static function putSecret(string $key, ?string $plain): void
    {
        static::updateOrCreate(['key'=>$key], ['value'=> $plain !== null ? encrypt($plain) : null]);
    }
    public static function getSecret(string $key, $default=null): ?string
    {
        $row = static::where('key',$key)->first();
        if (!$row || $row->value === null) return $default;
        try { return decrypt($row->value); } catch (\Throwable $e) { return $default; }
    }
}
