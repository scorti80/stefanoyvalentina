<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow"><title>Gallery administration</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-login-page">
    <main class="admin-login-card">
        <a class="admin-brand centered" href="{{ route('home') }}"><span>S <i>&amp;</i> V</span><small>Gallery administration</small></a>
        <h1>Welcome back</h1><p>Sign in to manage private galleries and media.</p>
        <form method="POST" action="{{ route('admin.login.store') }}" class="stack-form">
            @csrf
            <label>Email<input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></label>
            <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
            <label class="check-row"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
            <button type="submit" class="button button-primary full">Sign in</button>
        </form>
    </main>
</body>
</html>
