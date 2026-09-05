@extends('layouts.admin')
@section('title', 'Collections')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Source folders</p><h1>Collections</h1><p>Each collection pairs a web-size S3 folder with its matching HD originals.</p></div><a class="button button-primary" href="{{ route('admin.collections.create') }}">Add collection</a></div>
<div class="card-list">
@forelse($collections as $collection)
    <article class="admin-panel collection-card">
        <div><div class="card-title-row"><h2>{{ $collection->name }}</h2><span class="status-pill {{ $collection->is_active ? 'active' : '' }}">{{ $collection->is_active ? 'Active' : 'Paused' }}</span></div>
        <p>{{ number_format($collection->active_photos_count) }} active photos @if($collection->last_synced_at) · Synced {{ $collection->last_synced_at->diffForHumans() }} @endif</p>
        <dl><div><dt>Web prefix</dt><dd>{{ $collection->web_prefix }}</dd></div><div><dt>HD prefix</dt><dd>{{ $collection->hd_prefix }}</dd></div></dl></div>
        <div class="card-actions">
            <form method="POST" action="{{ route('admin.collections.sync', $collection) }}">@csrf<button class="button button-secondary">Sync now</button></form>
            <a class="button button-quiet" href="{{ route('admin.collections.edit', $collection) }}">Edit</a>
        </div>
    </article>
@empty
    <div class="empty-state admin-panel"><h2>No collections yet</h2><p>Add the first photographer folder to begin indexing photographs.</p><a class="button button-primary" href="{{ route('admin.collections.create') }}">Add first collection</a></div>
@endforelse
</div>
@endsection
