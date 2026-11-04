<?php

namespace App\Http\Middleware;

use App\Models\Institute;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active institute from (in order):
 *  1. an authenticated user's institute,
 *  2. a custom domain or subdomain match,
 *  3. the default institute on the central domain.
 */
class ResolveTenant
{
    public function __construct(private TenantContext $context)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->context->reset();

        $host = strtolower($request->getHost());
        $central = strtolower((string) config('lms.central_domain'));

        $institute = $this->fromHost($host, $central);

        if (! $institute && $request->user()) {
            $institute = $request->user()->institute;
        }

        if (! $institute && ($host === $central || str_starts_with($host, 'www.'))) {
            $institute = Institute::query()->where('slug', config('lms.default_institute_slug'))
                ->where('status', 'active')->first();
        }

        if ($institute) {
            abort_unless($institute->status === 'active', 403, __('This institute is currently inactive.'));

            // Prevent an authenticated user from browsing another institute's tenant host.
            if ($request->user()
                && ! $request->user()->isSuperAdmin()
                && $request->user()->institute_id !== $institute->id) {
                abort(403, __('You do not have access to this institute.'));
            }

            $this->context->set($institute);
            app()->setLocale($request->user()?->locale ?? $institute->locale ?? app()->getLocale());
        }

        return $next($request);
    }

    private function fromHost(string $host, string $central): ?Institute
    {
        $institute = Institute::query()
            ->where(fn ($q) => $q->where('custom_domain', $host)->orWhere('subdomain', $host))
            ->where('status', 'active')
            ->first();

        if ($institute) {
            return $institute;
        }

        // Any request coming through one of our own hosts may have a subdomain.
        if ($host !== $central && str_ends_with($host, '.'.$central)) {
            $sub = substr($host, 0, -strlen('.'.$central));

            return Institute::query()
                ->where('subdomain', $sub)
                ->orWhere('slug', $sub)
                ->where('status', 'active')
                ->first();
        }

        return null;
    }
}
