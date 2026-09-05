@extends('layouts.admin')
@section('title', 'Wedding film')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Private screening</p><h1>Wedding film</h1><p>The video page is linked from the unlocked homepage and protected by the site PIN.</p></div><a class="button button-secondary" href="{{ route('video') }}" target="_blank">Open film page ↗</a></div>
<form class="admin-panel edit-form" method="POST" action="{{ route('admin.video.update') }}">@csrf @method('PUT')
    <label>Title<input name="title" required value="{{ old('title', $video->title ?: 'Our wedding film') }}"></label>
    <div class="field-grid"><label>Storage disk<select name="disk">@foreach(config('filesystems.disks') as $name => $settings)<option value="{{ $name }}" @selected(old('disk', $video->disk ?: 's3') === $name)>{{ $name }}</option>@endforeach</select></label><label>MIME type<input name="mime_type" value="{{ old('mime_type', $video->mime_type ?: 'video/mp4') }}" required></label></div>
    <label>Video object key<input name="storage_key" value="{{ old('storage_key', $video->storage_key) }}" placeholder="wedding/video/wedding-film.mp4"></label>
    <label>Poster image object key <small>Optional</small><input name="poster_key" value="{{ old('poster_key', $video->poster_key) }}" placeholder="wedding/video/poster.jpg"></label>
    <label class="check-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $video->is_active))> Show the film link on the homepage</label>
    @if($errors->any())<div class="validation-list">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <button class="button button-primary" type="submit">Save film settings</button>
</form>
@endsection
