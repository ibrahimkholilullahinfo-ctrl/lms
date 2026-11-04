<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    use BelongsToInstitute, HasFactory;

    protected $fillable = ['institute_id', 'exam_id', 'student_id', 'marks', 'grade', 'status', 'remarks'];

    protected function casts(): array
    {
        return ['marks' => 'decimal:2'];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public static function gradeFor(float $marks, float $total): string
    {
        $percent = $total > 0 ? ($marks / $total) * 100 : 0;

        return match (true) {
            $percent >= 80 => 'A+',
            $percent >= 70 => 'A',
            $percent >= 60 => 'A-',
            $percent >= 50 => 'B',
            $percent >= 40 => 'C',
            default => 'F',
        };
    }
}
