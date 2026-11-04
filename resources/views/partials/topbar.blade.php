<header class="app-topbar px-3 px-lg-4 py-2 d-flex align-items-center gap-3">
    <button class="btn btn-outline-secondary btn-sm sidebar-toggle" data-sidebar-toggle type="button" aria-label="{{ __('Menu') }}">
        <i class="bi bi-list"></i>
    </button>
    <div class="me-auto">
        <div class="fw-semibold">{{ $title ?? __('Dashboard') }}</div>
        <div class="text-muted small">{{ $tenant->name ?? config('lms.name') }}</div>
    </div>
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" type="button">
            <i class="bi bi-globe2 me-1"></i>{{ app()->getLocale() === 'bn' ? 'বাংলা' : 'English' }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            @foreach (config('lms.locales') as $code => $meta)
                <li><a class="dropdown-item {{ app()->getLocale() === $code ? 'active' : '' }}"
                       href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}">{{ $meta['native'] }}</a></li>
            @endforeach
        </ul>
    </div>
    @auth
        <div class="dropdown">
            <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" type="button">
                <span class="badge rounded-pill text-bg-dark">{{ __(config('lms.roles.' . auth()->user()->role) ?? auth()->user()->role) }}</span>
                <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
                <li><a class="dropdown-item" href="{{ route(auth()->user()->panelRoute() . '.profile') }}"><i class="bi bi-person me-2"></i>{{ __('My profile') }}</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>{{ __('Log out') }}</button>
                    </form>
                </li>
            </ul>
        </div>
    @endauth
</header>
