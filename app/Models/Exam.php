<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $fillable = [
        'subject_id', 'created_by', 'title', 'instructions', 'duration_minutes',
        'starts_at', 'ends_at', 'status', 'passing_score', 'max_attempts', 'shuffle_questions', 'show_score',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'shuffle_questions' => 'boolean',
            'show_score' => 'boolean',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }
}
