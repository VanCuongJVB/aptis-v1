<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'skill',
        'description',
        'is_published',
        'duration_minutes',
        'allow_seek',
        'listens_allowed'
    ];
    protected $casts = ['is_published' => 'boolean', 'allow_seek' => 'boolean'];

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }
    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }
    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }
    public function isListening(): bool
    {
        return $this->skill === 'listening';
    }

    // === NEW: Fixed 4 parts cấu hình theo skill ===
    public function partsConfig(): array
    {
        $cfg = config('aptis.skills.' . $this->skill, []);
        // đảm bảo có 4 phần
        return $cfg + [1 => [], 2 => [], 3 => [], 4 => []];
    }
    public function allowedTypesForPart(int $part): array
    {
        $cfg = $this->partsConfig();
        return $cfg[$part]['types'] ?? [];
    }
    public function partLabel(int $part): string
    {
        $cfg = $this->partsConfig();
        return $cfg[$part]['label'] ?? ('Part ' . $part);
    }
}
