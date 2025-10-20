<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{


   protected $fillable = ['user_id','action','entity_type','entity_id','meta','ip','agent'];
    protected $casts    = ['meta' => 'array'];
    public function user(){ return $this->belongsTo(\App\Models\User::class); }
}
