<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = ['exam_id', 'position', 'prompt', 'options', 'correct_option', 'points'];

    protected function casts(): array
    {
        return ['options' => 'array'];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
