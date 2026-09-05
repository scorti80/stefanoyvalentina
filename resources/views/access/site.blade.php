@extends('layouts.site')

@section('title', 'Private wedding memories · '.config('wedding.title'))

@section('content')
<section class="access-shell">
    <div class="botanical botanical-left" aria-hidden="true"></div>
    <div class="access-card">
        <p class="eyebrow">Our wedding memories</p>
        <h1>A day we’ll<br>always carry.</h1>
        <p class="access-intro">This is a private space for the people who shared it with us.</p>

        @if(session('notice'))<p class="notice">{{ session('notice') }}</p>@endif

        <form method="POST" action="{{ route('site.unlock') }}" class="pin-form">
            @csrf
            <label for="pin">Enter the private PIN</label>
            <div class="pin-row">
                <input id="pin" name="pin" type="password" inputmode="numeric" autocomplete="current-password" required autofocus aria-describedby="pin-help">
                <button type="submit" class="button button-primary">Enter</button>
            </div>
            @error('pin')<p class="field-error">{{ $message }}</p>@enderror
            <p id="pin-help" class="form-help">Ask Stefano or Valentina if you need the PIN.</p>
        </form>
    </div>
    <div class="botanical botanical-right" aria-hidden="true"></div>
</section>
@endsection
