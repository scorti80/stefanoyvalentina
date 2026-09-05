@extends('layouts.site')

@section('title', $playlist->name.' · Private gallery')

@section('content')
<section class="access-shell compact-access">
    <div class="access-card">
        <p class="eyebrow">A private gallery</p>
        <h1>{{ $playlist->name }}</h1>
        @if($playlist->description)<p class="access-intro">{{ $playlist->description }}</p>@endif
        <form method="POST" action="{{ route('gallery.unlock', ['token' => $token]) }}" class="pin-form">
            @csrf
            <label for="pin">Gallery PIN</label>
            <div class="pin-row">
                <input id="pin" name="pin" type="password" autocomplete="current-password" required autofocus>
                <button type="submit" class="button button-primary">View photos</button>
            </div>
            @error('pin')<p class="field-error">{{ $message }}</p>@enderror
            <p class="form-help">The private link and PIN are both required.</p>
        </form>
    </div>
</section>
@endsection
