<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttemptItem extends Model
{
    use HasFactory;
    protected $fillable = ['attempt_id','question_id','selected_option_ids','is_correct','time_spent_sec'];
    protected $casts = ['selected_option_ids'=>'array','is_correct'=>'boolean'];
    public function attempt(){ return $this->belongsTo(Attempt::class); }
    public function question(){ return $this->belongsTo(Question::class); }
}
