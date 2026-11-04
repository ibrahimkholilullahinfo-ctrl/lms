<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plugin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'type', 'provider', 'version', 'description',
        'settings_schema', 'settings', 'is_active', 'is_installed',
    ];

    protected function casts(): array
    {
        return [
            'settings_schema' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
            'is_installed' => 'boolean',
        ];
    }

    public function institutes(): BelongsToMany
    {
        return $this->belongsToMany(Institute::class)->withPivot(['is_active', 'settings'])->withTimestamps();
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }

    public function isConfigured(): bool
    {
        if (empty($this->settings_schema)) {
            return false;
        }

        foreach ($this->settings_schema as $field) {
            if (($field['required'] ?? false) && blank($this->setting($field['key']))) {
                return false;
            }
        }

        return true;
    }
}
