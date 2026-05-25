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
        ['printers', 'Printers', 'ti ti-printer', route('printers')],
        ['settings', 'Settings', 'ti ti-settings', route('settings')],
    ];
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f8fc">
    <title>{{ $title ?? 'POS - Sales Terminal' }}</title>
    <script>
        (function () {
            var theme = localStorage.getItem('pos-theme') || localStorage.getItem('coarse-theme') || 'light';
            document.documentElement.dataset.theme = theme;
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Syne:wght@500;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.x/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/coarse-pos.css') }}?v={{ filemtime(public_path('assets/coarse-pos.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/theme-fixes.css') }}?v={{ filemtime(public_path('assets/theme-fixes.css')) }}">
</head>
<body data-theme="light">
    <div class="pos-app-frame">
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
        <main class="pos-terminal-main">
            @if(session('status'))<div class="pos-flash">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="pos-flash error">{{ $errors->first() }}</div>@endif
            @yield('content')
        </main>
    </div>
    <script src="{{ asset('assets/coarse-pos.js') }}?v={{ filemtime(public_path('assets/coarse-pos.js')) }}"></script>
</body>
</html>
