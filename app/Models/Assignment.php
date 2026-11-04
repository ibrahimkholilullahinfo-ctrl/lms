<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use BelongsToInstitute, HasFactory, SoftDeletes;

    protected $fillable = [
        'institute_id', 'batch_id', 'created_by', 'title', 'description',
        'attachment', 'total_marks', 'due_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'total_marks' => 'decimal:2',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
