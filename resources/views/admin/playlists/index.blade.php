@extends('layouts.admin')
@section('title', 'Playlists')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Private sharing</p><h1>Playlists</h1><p>Create a different protected selection for every group.</p></div><a class="button button-primary" href="{{ route('admin.playlists.create') }}">New playlist</a></div>
<div class="card-list">
@forelse($playlists as $playlist)
    <article class="admin-panel playlist-card">
        <div class="playlist-main"><div class="card-title-row"><h2>{{ $playlist->name }}</h2><span class="status-pill {{ $playlist->isAvailable() ? 'active' : '' }}">{{ $playlist->isAvailable() ? 'Shareable' : 'Inactive' }}</span></div>
            <p>{{ $playlist->photos_count }} {{ Str::plural('photo', $playlist->photos_count) }} @if($playlist->expires_at) · Expires {{ $playlist->expires_at->format('M j, Y') }} @endif</p>
            <div class="share-row"><input readonly value="{{ $playlist->share_url }}" aria-label="Private share link"><button class="button button-secondary" type="button" data-copy="{{ $playlist->share_url }}">Copy link</button></div>
        </div>
        <div class="card-actions"><a class="button button-primary" href="{{ route('admin.playlists.photos', $playlist) }}">Choose photos</a><a class="button button-quiet" href="{{ route('admin.playlists.edit', $playlist) }}">Settings</a></div>
    </article>
@empty
    <div class="empty-state admin-panel"><h2>No playlists yet</h2><p>Create the first private collection of photographs to share.</p><a class="button button-primary" href="{{ route('admin.playlists.create') }}">Create a playlist</a></div>
@endforelse
</div>
@endsection
