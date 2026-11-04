<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSession extends Model
{
    use BelongsToInstitute, HasFactory, SoftDeletes;

    protected $fillable = [
        'institute_id', 'batch_id', 'lesson_id', 'teacher_id', 'title', 'mode',
        'provider', 'meeting_id', 'join_url', 'meeting_password', 'venue',
        'starts_at', 'duration_minutes', 'status',
    ];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime'];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function isOnline(): bool
    {
        return in_array($this->mode, ['online', 'hybrid'], true);
    }
}
