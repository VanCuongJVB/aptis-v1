<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingSet extends Model
{
    use HasFactory;

    protected $table = 'sets';

    protected $fillable = [
        'quiz_id',
        'skill',
        'title',
        'description',
        'is_public',
        'order',
        'metadata',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'metadata' => 'array',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'reading_set_id')->orderBy('order');
    }
}
