<?php

namespace App\Models\Concerns;

use App\Models\Institute;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Applies institute-level data isolation to every tenant scoped model.
 */
trait BelongsToInstitute
{
    public static function bootBelongsToInstitute(): void
    {
        static::addGlobalScope('institute', function (Builder $builder) {
            $context = app(TenantContext::class);

            if ($context->bypassed()) {
                return;
            }

            $id = $context->id();

            if ($id !== null) {
                $builder->where($builder->getModel()->getTable().'.institute_id', $id);
            }
        });

        static::creating(function ($model) {
            $context = app(TenantContext::class);

            if ($model->institute_id === null && $context->id() !== null) {
                $model->institute_id = $context->id();
            }

            if ($model->institute_id === null) {
                throw new \RuntimeException('A tenant record cannot be created without an institute context.');
            }
        });
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function scopeForInstitute(Builder $query, int $instituteId): Builder
    {
        return $query->withoutGlobalScope('institute')->where($this->getTable().'.institute_id', $instituteId);
    }
}
