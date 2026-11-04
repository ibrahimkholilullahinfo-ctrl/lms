<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'subdomain', 'custom_domain', 'email', 'phone', 'address',
        'logo', 'favicon', 'primary_color', 'secondary_color', 'locale', 'timezone',
        'currency', 'status', 'trial_ends_at', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function host(): string
    {
        return $this->custom_domain ?: ($this->subdomain.'.'.config('lms.central_domain'));
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
