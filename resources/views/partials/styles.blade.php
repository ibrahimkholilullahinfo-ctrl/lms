<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
    :root {
        --navy: {{ $tenant->primary_color ?? '#0b2545' }};
        --navy-soft: #17395f;
        --orange: {{ $tenant->secondary_color ?? '#f97316' }};
    }
    body { font-family: "Segoe UI", "Noto Sans Bengali", system-ui, sans-serif; }
    .app-shell { display: flex; min-height: 100vh; }
    .app-sidebar {
        width: 264px; flex: 0 0 264px; background: var(--navy); color: #fff;
        display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; overflow-y: auto;
    }
    .app-sidebar .brand { padding: 1.1rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,.12); }
    .app-sidebar .brand-logo {
        width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center;
        justify-content: center; background: var(--orange); font-weight: 700; color: #fff;
    }
    .app-sidebar .nav-link { color: rgba(255,255,255,.75); border-radius: .5rem; margin: .1rem .6rem; padding: .5rem .75rem; font-size: .925rem; }
    .app-sidebar .nav-link:hover { color: #fff; background: rgba(255,255,255,.08); }
    .app-sidebar .nav-link.active { color: #fff; background: var(--orange); }
    .app-sidebar .nav-heading { font-size: .72rem; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.45); padding: 1rem 1.4rem .35rem; }
    .app-main { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; }
    .app-topbar { background: #fff; border-bottom: 1px solid #e6e8ec; }
    .card { border: 1px solid #e8ebef; border-radius: .85rem; box-shadow: 0 1px 2px rgba(16,24,40,.04); }
    .btn-primary { background: var(--navy); border-color: var(--navy); }
    .btn-primary:hover, .btn-primary:focus { background: var(--navy-soft); border-color: var(--navy-soft); }
    .btn-accent { background: var(--orange); border-color: var(--orange); color: #fff; }
    .btn-accent:hover { background: #ea6a0c; border-color: #ea6a0c; color: #fff; }
    .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem; }
    .table > :not(caption) > * > * { padding: .7rem .75rem; vertical-align: middle; }
    .auth-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem;
        background: radial-gradient(circle at 20% 20%, #17395f 0%, var(--navy) 55%, #071a30 100%); }
    .auth-card { width: 100%; max-width: 460px; border-radius: 1rem; }
    .hero { background: linear-gradient(120deg, var(--navy) 0%, #12365c 60%, #1d4a77 100%); color: #fff; }
    .brand-badge { background: var(--orange); color:#fff; border-radius: .5rem; padding: .15rem .5rem; font-size:.75rem; font-weight:600; }
    .sidebar-toggle { display: none; }
    @media (max-width: 991.98px) {
        .app-sidebar { position: fixed; z-index: 1040; left: -280px; transition: left .2s ease; }
        .app-sidebar.show { left: 0; }
        .sidebar-toggle { display: inline-flex; }
    }
</style>
