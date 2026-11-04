<?php

namespace App\Models;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'institute_id', 'user_id', 'action', 'subject_type', 'subject_id',
        'description', 'properties', 'ip_address',
    ];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public static function record(string $action, ?Model $subject = null, ?string $description = null, array $properties = []): self
    {
        return static::create([
            'institute_id' => app(TenantContext::class)->id() ?? auth()->user()?->institute_id,
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
