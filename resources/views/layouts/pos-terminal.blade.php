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
</head>
<body data-theme="light">
    @if(session('status'))<div class="pos-flash">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="pos-flash error">{{ $errors->first() }}</div>@endif
    @yield('content')
    <script src="{{ asset('assets/coarse-pos.js') }}?v={{ filemtime(public_path('assets/coarse-pos.js')) }}"></script>
</body>
</html>
