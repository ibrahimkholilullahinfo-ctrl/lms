<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    use BelongsToInstitute, HasFactory, SoftDeletes;

    protected $fillable = [
        'institute_id', 'student_id', 'course_id', 'batch_id', 'certificate_no',
        'title', 'issued_at', 'grade', 'template', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'meta' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public static function generateNumber(int $instituteId): string
    {
        return sprintf('AEC-%d-%s-%04d', $instituteId, now()->format('Ymd'), random_int(1, 9999));
    }
}
