<?php

namespace App\Support;

use App\Models\Institute;

/**
 * Holds the institute resolved for the current request.
 *
 * Data isolation is enforced through the BelongsToInstitute trait, which reads
 * the active institute id from this singleton. Super Admin operations that
 * need cross-institute access call bypass() explicitly.
 */
class TenantContext
{
    private ?Institute $institute = null;

    private bool $bypass = false;

    public function set(?Institute $institute): void
    {
        $this->institute = $institute;
    }

    public function institute(): ?Institute
    {
        return $this->institute;
    }

    public function id(): ?int
    {
        return $this->institute?->id;
    }

    public function has(): bool
    {
        return $this->institute !== null;
    }

    public function bypass(bool $value = true): void
    {
        $this->bypass = $value;
    }

    public function bypassed(): bool
    {
        return $this->bypass;
    }

    /** Execute a callback with institute scoping disabled (Super Admin tools). */
    public function withoutScope(callable $callback): mixed
    {
        $previous = $this->bypass;
        $this->bypass = true;

        try {
            return $callback();
        } finally {
            $this->bypass = $previous;
        }
    }

    public function reset(): void
    {
        $this->institute = null;
        $this->bypass = false;
    }
}
