@php
    $nav = [
        ['dashboard', 'Dashboard', 'D', route('dashboard')],
        ['pos', 'POS', 'P', route('pos.index')],
        ['inventory', 'Inventory', 'I', route('inventory')],
        ['tables', 'Tables', 'T', route('tables')],
        ['kds', 'Kitchen Display', 'K', route('kds')],
        ['recipes', 'Recipes / BOM', 'R', route('recipes')],
        ['purchases', 'Purchases', 'B', route('purchases')],
        ['expenses', 'Expenses', 'E', route('expenses')],
        ['credit', 'Credit Sales', 'C', route('credit')],
        ['reports', 'Reports', 'A', route('reports')],
        ['hotel', 'Hotel / PMS', 'H', route('hotel')],
        ['users', 'Users', 'U', route('users')],
        ['settings', 'Settings', 'S', route('settings')],
    ];
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'COARSE Restaurant POS' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/coarse-pos.css') }}?v={{ filemtime(public_path('assets/coarse-pos.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/advanced-pos.css') }}?v={{ filemtime(public_path('assets/advanced-pos.css')) }}">
</head>
<body>
<div class="bg-mesh"></div>
<div class="shell">
    <aside class="dock">
        <div class="dock-logo">C</div>
        <div class="dock-sep"></div>
        @foreach($nav as [$key, $label, $icon, $url])
            <a class="dock-item {{ request()->routeIs($key) || ($key === 'dashboard' && request()->routeIs('dashboard')) ? 'active' : '' }}" href="{{ $url }}">
                <i>{{ $icon }}</i><span class="dock-label">{{ $label }}</span>
            </a>
        @endforeach
        <div class="mode-badge"><span class="mode-dot"></span><span class="mode-text">Restaurant Mode</span></div>
    </aside>
    <main class="main">
        <header class="topbar">
            <div>
                <div class="topbar-title">{{ $title ?? 'Dashboard' }}</div>
                <div class="topbar-sub">Main Branch · {{ auth()->user()->name ?? 'Guest' }}</div>
            </div>
            <div class="topbar-spacer"></div>
            <div class="topbar-pill pill-restaurant">Restaurant</div>
            <div class="topbar-icon">{{ now()->format('H:i') }}</div>
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-ghost btn-sm">Logout</button></form>
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
