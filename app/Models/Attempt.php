<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attempt extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','quiz_id','started_at','submitted_at','score_raw','score_percent'];
    protected $casts = ['started_at'=>'datetime','submitted_at'=>'datetime'];
    public function user(){ return $this->belongsTo(User::class); }
    public function quiz(){ return $this->belongsTo(Quiz::class); }
    public function items(){ return $this->hasMany(AttemptItem::class); }
}
