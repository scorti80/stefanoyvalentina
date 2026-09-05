<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>@yield('title', 'Gallery admin') · {{ config('wedding.title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="admin-brand"><span>S <i>&amp;</i> V</span><small>Gallery administration</small></a>
        <nav>
            <a @class(['active' => request()->routeIs('admin.dashboard')]) href="{{ route('admin.dashboard') }}">Overview</a>
            <a @class(['active' => request()->routeIs('admin.collections.*')]) href="{{ route('admin.collections.index') }}">Collections</a>
            <a @class(['active' => request()->routeIs('admin.playlists.*')]) href="{{ route('admin.playlists.index') }}">Playlists</a>
            <a @class(['active' => request()->routeIs('admin.video.*')]) href="{{ route('admin.video.edit') }}">Wedding film</a>
            <a @class(['active' => request()->routeIs('admin.settings.*')]) href="{{ route('admin.settings.edit') }}">Privacy</a>
        </nav>
        <div class="admin-sidebar-bottom">
            <a href="{{ route('home') }}">View site ↗</a>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="text-button" type="submit">Sign out</button></form>
        </div>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar"><button type="button" class="menu-button" data-menu-toggle aria-label="Toggle navigation">Menu</button><span>{{ auth()->user()->name }}</span></header>
        @if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
        @yield('content')
    </main>
</body>
</html>
