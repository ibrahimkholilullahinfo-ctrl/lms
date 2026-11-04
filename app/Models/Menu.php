<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id', 'parent_id', 'label_en', 'label_bn', 'url',
        'target', 'sort_order', 'is_active', 'location',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order');
    }

    public function label(): string
    {
        return app()->getLocale() === 'bn' && filled($this->label_bn)
            ? $this->label_bn
            : $this->label_en;
    }

    public function scopeLocation($query, string $location)
    {
        return $query->where('location', $location)->where('is_active', true);
    }
}
