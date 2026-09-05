@extends('layouts.admin')
@section('title', 'Privacy')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Access control</p><h1>Privacy</h1><p>The site PIN protects the homepage and wedding film. Every playlist also has its own required PIN.</p></div></div>
<form class="admin-panel edit-form narrow-form" method="POST" action="{{ route('admin.settings.update') }}">@csrf @method('PUT')
    <label>New site PIN <small>At least 4 characters</small><input type="password" name="site_pin" required autocomplete="new-password"></label>
    <label>Confirm site PIN<input type="password" name="site_pin_confirmation" required autocomplete="new-password"></label>
    @error('site_pin')<p class="field-error">{{ $message }}</p>@enderror
    <button class="button button-primary" type="submit">Change site PIN</button>
</form>
<section class="admin-panel security-list"><h2>Crawler protection is active</h2><ul><li>All pages send no-index and no-archive directives.</li><li>The crawler policy disallows the entire site.</li><li>S3 media remains private and uses temporary links.</li><li>PIN attempts are rate limited.</li></ul><p>These measures stop compliant crawlers; the PIN gates are what protect media from non-compliant ones.</p></section>
@endsection
