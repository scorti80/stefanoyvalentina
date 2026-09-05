@extends('layouts.admin')
@section('title', $collection->exists ? 'Edit collection' : 'Add collection')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Source folder</p><h1>{{ $collection->exists ? 'Edit collection' : 'Add a collection' }}</h1><p>Enter object-key prefixes only—never paste AWS credentials here.</p></div></div>
<form class="admin-panel edit-form" method="POST" action="{{ $collection->exists ? route('admin.collections.update', $collection) : route('admin.collections.store') }}">
    @csrf @if($collection->exists) @method('PUT') @endif
    <div class="field-grid"><label>Name<input name="name" value="{{ old('name', $collection->name) }}" required placeholder="Photographer one"></label><label>Slug <small>Optional</small><input name="slug" value="{{ old('slug', $collection->slug) }}" placeholder="photographer-one"></label></div>
    <label>Storage disk<select name="disk" required>@foreach(config('filesystems.disks') as $name => $settings)<option value="{{ $name }}" @selected(old('disk', $collection->disk ?: 's3') === $name)>{{ $name }}</option>@endforeach</select></label>
    <label>Web-size prefix<input name="web_prefix" value="{{ old('web_prefix', $collection->web_prefix) }}" required placeholder="wedding/photographer-1/web"></label>
    <label>HD originals prefix<input name="hd_prefix" value="{{ old('hd_prefix', $collection->hd_prefix) }}" required placeholder="wedding/photographer-1/hd"></label>
    <label class="check-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $collection->exists ? $collection->is_active : true))> Collection is available for playlists</label>
    @if($errors->any())<div class="validation-list">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <div class="form-actions"><button class="button button-primary" type="submit">Save collection</button><a class="button button-quiet" href="{{ route('admin.collections.index') }}">Cancel</a></div>
</form>
@if($collection->exists)<form class="danger-zone" method="POST" action="{{ route('admin.collections.destroy', $collection) }}" onsubmit="return confirm('Remove this collection and its indexed records? S3 files will remain untouched.')">@csrf @method('DELETE')<div><strong>Remove collection</strong><p>This removes indexed records and playlist assignments, but never deletes S3 objects.</p></div><button class="button button-danger">Remove</button></form>@endif
@endsection
