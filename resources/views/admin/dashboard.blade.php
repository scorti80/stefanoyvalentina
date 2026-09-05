@extends('layouts.admin')
@section('title', 'Overview')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Overview</p><h1>Your wedding archive</h1><p>Manage the original collections once, then shape a private gallery for every group.</p></div><a class="button button-primary" href="{{ route('admin.playlists.create') }}">New playlist</a></div>
<section class="stat-grid">
    <a href="{{ route('admin.collections.index') }}"><strong>{{ $collections }}</strong><span>Collections</span></a>
    <a href="{{ route('admin.collections.index') }}"><strong>{{ number_format($photos) }}</strong><span>Indexed photos</span></a>
    <a href="{{ route('admin.playlists.index') }}"><strong>{{ $playlists }}</strong><span>Private playlists</span></a>
    <a href="{{ route('admin.video.edit') }}"><strong>{{ $video ? 'Ready' : 'Not set' }}</strong><span>Wedding film</span></a>
</section>
<section class="admin-panel getting-started"><p class="eyebrow">The simple workflow</p><div class="step-grid"><div><span>01</span><h2>Connect collections</h2><p>Add the web and HD prefixes for each folder in private S3 storage.</p></div><div><span>02</span><h2>Make playlists</h2><p>Create a protected share link and PIN for each group of guests.</p></div><div><span>03</span><h2>Choose photographs</h2><p>Filter either collection and assign the exact moments you want to share.</p></div></div></section>
@endsection
