@extends('layouts.admin')
@section('title', 'Choose photos · '.$playlist->name)
@section('content')
<div class="page-heading"><div><p class="eyebrow">{{ $playlist->name }}</p><h1>Choose photographs</h1><p>Selections save one page at a time. A checked photograph is included in this playlist.</p></div><a class="button button-secondary" target="_blank" href="{{ $playlist->share_url }}">Open gallery ↗</a></div>
<form class="filter-bar" method="GET"><select name="collection"><option value="">All collections</option>@foreach($collections as $collection)<option value="{{ $collection->id }}" @selected(request('collection') == $collection->id)>{{ $collection->name }}</option>@endforeach</select><input type="search" name="search" value="{{ request('search') }}" placeholder="Search filename"><button class="button button-secondary">Filter</button><a class="button button-quiet" href="{{ route('admin.playlists.photos', $playlist) }}">Clear</a></form>
<form class="selection-toolbar" method="POST" action="{{ route('admin.playlists.photos.select-all', $playlist) }}">@csrf
    <input type="hidden" name="collection" value="{{ request('collection') }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <p id="select-all-description">Immediately saves all {{ $photos->total() }} photos matching the current filters across every page.</p>
    <button class="button button-secondary" type="submit" aria-describedby="select-all-description">Select All</button>
</form>
<form method="POST" action="{{ route('admin.playlists.photos.update', $playlist) }}">@csrf @method('PUT')
    <div class="selection-toolbar"><div><button type="button" class="text-button" data-select-all>Select page</button><span>·</span><button type="button" class="text-button" data-clear-all>Clear page</button></div><button class="button button-primary" type="submit">Save this page</button></div>
    @if($photos->count())<section class="admin-photo-grid">
    @foreach($photos as $photo)
        <input type="hidden" name="visible_ids[]" value="{{ $photo->id }}">
        <label class="select-photo"><input type="checkbox" name="photo_ids[]" value="{{ $photo->id }}" @checked(in_array($photo->id, $assigned))><span class="selection-mark">✓</span><img src="{{ $photo->preview_url }}" alt="" loading="lazy"><span class="photo-label"><b>{{ $photo->filename }}</b><small>{{ $photo->collection->name }}</small></span></label>
    @endforeach
    </section><div class="pagination">{{ $photos->links() }}</div>
    @else<div class="empty-state admin-panel"><h2>No matching photographs</h2><p>Sync a collection or change the current filters.</p></div>@endif
</form>
@endsection
