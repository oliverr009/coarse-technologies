@php
    $nav = [
        ['dashboard', 'Dashboard', 'ti ti-layout-dashboard', route('dashboard')],
        ['pos.index', 'POS Sell', 'ti ti-basket', route('pos.index')],
        ['orders', 'Orders', 'ti ti-receipt-2', route('orders')],
        ['inventory', 'Inventory', 'ti ti-package', route('inventory')],
        ['tables', 'Tables', 'ti ti-layout-grid', route('tables')],
        ['kds', 'Kitchen Display', 'ti ti-chef-hat', route('kds')],
        ['recipes', 'Recipes / BOM', 'ti ti-tools-kitchen-2', route('recipes')],
        ['hotel', 'Hotel / PMS', 'ti ti-building', route('hotel')],
        ['purchases', 'Purchases', 'ti ti-truck-delivery', route('purchases')],
        ['expenses', 'Expenses', 'ti ti-wallet', route('expenses')],
        ['credit', 'Credit Sales', 'ti ti-credit-card', route('credit')],
        ['reports', 'Reports', 'ti ti-chart-bar', route('reports')],
        ['shifts', 'Shifts & Till', 'ti ti-cash-banknote', route('shifts')],
        ['users', 'Users & Roles', 'ti ti-users', route('users')],
        ['settings', 'Settings', 'ti ti-settings', route('settings')],
    ];
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0e1230">
    <title>{{ $title ?? 'COARSE Restaurant POS' }}</title>
    <script>
        (function () {
            var theme = localStorage.getItem('coarse-theme') || 'dark';
            document.documentElement.dataset.theme = theme;
            document.querySelector('meta[name="theme-color"]')?.setAttribute('content', theme === 'light' ? '#f5f8fc' : '#0e1230');
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.x/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/coarse-pos.css') }}?v={{ filemtime(public_path('assets/coarse-pos.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/advanced-pos.css') }}?v={{ filemtime(public_path('assets/advanced-pos.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/theme-fixes.css') }}?v={{ filemtime(public_path('assets/theme-fixes.css')) }}">
</head>
<body>
<a href="#main" class="skip-link">Skip to main content</a>
<div class="bg-mesh"></div>
<div class="shell" id="main">
    <aside class="dock" aria-label="Main navigation">
        <div class="dock-logo" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" width="22" height="22">
                <circle cx="24" cy="24" r="20" stroke="#28BCEE" stroke-width="2.5"/>
                <circle cx="24" cy="24" r="12" stroke="#28BCEE" stroke-width="2.5"/>
                <circle cx="24" cy="24" r="5" fill="#28BCEE"/>
                <line x1="24" y1="29" x2="24" y2="44" stroke="#28BCEE" stroke-width="2.5"/>
            </svg>
        </div>
        @foreach($nav as [$routeName, $label, $icon, $url])
            <a class="dock-item {{ request()->routeIs($routeName) ? 'active' : '' }}" href="{{ $url }}" @if(request()->routeIs($routeName)) aria-current="page" @endif>
                <i class="{{ $icon }}" aria-hidden="true"></i><span class="dock-label">{{ $label }}</span>
            </a>
            @if(in_array($routeName, ['orders', 'recipes', 'credit'], true))
                <div class="dock-sep" aria-hidden="true"></div>
            @endif
        @endforeach
        <div class="mode-badge"><span class="mode-dot"></span><span class="mode-text">Restaurant Mode</span></div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <div class="topbar-title">{{ $title ?? 'Dashboard' }}</div>
                <div class="topbar-sub">Main Branch</div>
            </div>
            <div class="topbar-spacer"></div>
            <button class="topbar-pill pill-restaurant" type="button"><i class="ti ti-tools-kitchen-2" aria-hidden="true"></i> Restaurant</button>
            <button class="topbar-icon theme-toggle" type="button" aria-label="Switch theme" data-theme-toggle title="Switch theme"><i class="ti ti-sun" aria-hidden="true"></i></button>
            <button class="topbar-icon" type="button" aria-label="Notifications"><i class="ti ti-bell" aria-hidden="true"></i></button>
            <button class="topbar-icon" type="button" aria-label="Search"><i class="ti ti-search" aria-hidden="true"></i></button>
            <div class="avatar" title="{{ auth()->user()->name ?? 'Guest' }}">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="topbar-icon" type="submit" aria-label="Logout"><i class="ti ti-logout" aria-hidden="true"></i></button></form>
        </header>

        <section class="content">
            @if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="flash" style="color:var(--red);border-color:rgba(248,113,113,.25);background:rgba(248,113,113,.08)">{{ $errors->first() }}</div>@endif
            @yield('content')
        </section>
    </main>
</div>
<script src="{{ asset('assets/coarse-pos.js') }}?v={{ filemtime(public_path('assets/coarse-pos.js')) }}"></script>
</body>
</html>
