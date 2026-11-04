<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'bn' ? 'ltr' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? config('lms.name')) · {{ $tenant->name ?? config('lms.name') }}</title>
    @include('partials.styles')
</head>
<body class="bg-light">
<div class="app-shell">
    @include('partials.sidebar')
    <div class="app-main">
        @include('partials.topbar')
        <main class="container-fluid py-4">
            @include('partials.flash')
            @yield('content')
        </main>
        <footer class="text-center text-muted small py-3">
            {{ config('lms.name') }} · {{ now()->format('Y') }} · {{ __('phone') }}: {{ config('lms.support_phone') }}
        </footer>
    </div>
</div>
@include('partials.scripts')
</body>
</html>
