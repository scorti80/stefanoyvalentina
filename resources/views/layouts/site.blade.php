<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive,noimageindex,noai,noimageai">
    <meta name="referrer" content="same-origin">
    <title>@yield('title', config('wedding.title'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-body">
    <header class="site-header">
        <a class="wordmark" href="{{ route('home') }}" aria-label="Wedding home">
            <span>Stefano</span><i aria-hidden="true">&amp;</i><span>Valentina</span>
        </a>
        @if(session()->has('wedding.site_revision'))
            <nav class="site-nav" aria-label="Main navigation">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('video') }}">Film</a>
                <form method="POST" action="{{ route('site.lock') }}">@csrf<button type="submit" class="text-button">Lock</button></form>
            </nav>
        @endif
    </header>

    <main>@yield('content')</main>

    <footer class="site-footer">
        <span>S <i>&amp;</i> V</span>
        <p>A private collection of memories, shared with love.</p>
    </footer>
    @stack('scripts')
</body>
</html>
