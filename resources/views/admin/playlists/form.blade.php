@extends('layouts.admin')
@section('title', $playlist->exists ? 'Playlist settings' : 'New playlist')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Private sharing</p><h1>{{ $playlist->exists ? 'Playlist settings' : 'Create a playlist' }}</h1><p>Every playlist requires both its secret link and a PIN.</p></div></div>
<form class="admin-panel edit-form" method="POST" action="{{ $playlist->exists ? route('admin.playlists.update', $playlist) : route('admin.playlists.store') }}">
    @csrf @if($playlist->exists) @method('PUT') @endif
    <label>Playlist name<input name="name" value="{{ old('name', $playlist->name) }}" required placeholder="Family favorites"></label>
    <label>Short introduction <small>Optional</small><textarea name="description" rows="3" placeholder="A few favorite moments for our family.">{{ old('description', $playlist->description) }}</textarea></label>
    <div class="field-grid"><label>{{ $playlist->exists ? 'New PIN' : 'Required PIN' }} <small>{{ $playlist->exists ? 'Leave blank to keep it' : 'At least 4 characters' }}</small><input type="password" name="pin" @required(!$playlist->exists) autocomplete="new-password"></label><label>Expires <small>Optional</small><input type="datetime-local" name="expires_at" value="{{ old('expires_at', $playlist->expires_at?->format('Y-m-d\TH:i')) }}"></label></div>
    <fieldset><legend>Permissions</legend><label class="check-row"><input type="checkbox" name="allow_sd_download" value="1" @checked(old('allow_sd_download', $playlist->exists ? $playlist->allow_sd_download : true))> Allow web-size downloads</label><label class="check-row"><input type="checkbox" name="allow_hd_download" value="1" @checked(old('allow_hd_download', $playlist->exists ? $playlist->allow_hd_download : true))> Allow HD downloads</label><label class="check-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $playlist->exists ? $playlist->is_active : true))> Link is active</label></fieldset>
    @if($errors->any())<div class="validation-list">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <div class="form-actions"><button class="button button-primary" type="submit">{{ $playlist->exists ? 'Save settings' : 'Create and choose photos' }}</button><a class="button button-quiet" href="{{ route('admin.playlists.index') }}">Cancel</a></div>
</form>
@if($playlist->exists)
<div class="secondary-actions admin-panel"><div><strong>Replace private link</strong><p>The current link will stop working immediately. The PIN stays the same.</p></div><form method="POST" action="{{ route('admin.playlists.rotate', $playlist) }}" onsubmit="return confirm('Replace the private link? The old link will stop working.')">@csrf<button class="button button-secondary">Generate new link</button></form></div>
<form class="danger-zone" method="POST" action="{{ route('admin.playlists.destroy', $playlist) }}" onsubmit="return confirm('Permanently remove this playlist?')">@csrf @method('DELETE')<div><strong>Remove playlist</strong><p>The photographs themselves will not be deleted.</p></div><button class="button button-danger">Remove</button></form>
@endif
@endsection
