<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · COARSE POS</title>
    <link rel="stylesheet" href="{{ asset('assets/coarse-pos.css') }}">
</head>
<body class="login-page">
<div class="bg-mesh"></div>
<div class="card login-card">
    <div class="login-logo">COARSE <span>POS</span></div>
    <h1>Restaurant POS</h1>
    <p style="color:var(--text3)">Sign in to continue.</p>
    @if($errors->any())<div class="flash" style="color:var(--red);border-color:rgba(248,113,113,.25);background:rgba(248,113,113,.08)">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('login.post') }}">
        @csrf
        <p><div class="lbl">Email</div><input class="inp" name="email" type="email" value="{{ old('email', 'admin@coarse.test') }}" required></p>
        <p><div class="lbl">Password</div><input class="inp" name="password" type="password" value="admin123" required></p>
        <button class="btn btn-primary">Login</button>
    </form>
</div>
</body>
</html>

