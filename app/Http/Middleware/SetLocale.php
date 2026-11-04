<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('lms.supported_locales', ['en', 'bn']);

        $locale = $request->query('lang')
            ?? session('locale')
            ?? $request->user()?->locale
            ?? config('app.locale');

        if (in_array($locale, $supported, true)) {
            app()->setLocale($locale);
            if ($request->query('lang')) {
                session(['locale' => $locale]);
            }
        }

        return $next($request);
    }
}
