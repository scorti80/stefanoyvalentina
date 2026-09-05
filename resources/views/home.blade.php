@extends('layouts.site')

@section('content')
<section class="home-hero">
    <div class="home-copy">
        <p class="eyebrow">The celebration continues</p>
        <h1>Thank you for being part of our story.</h1>
        <p>We gathered the photographs and film from our wedding here, so the people we love can return to those moments whenever they wish.</p>
        @if($video)
            <a class="button button-primary" href="{{ route('video') }}">Watch our wedding film <span aria-hidden="true">→</span></a>
        @else
            <span class="button button-muted">The wedding film is coming soon</span>
        @endif
    </div>
    <aside class="home-note">
        <span class="monogram">S<span>&amp;</span>V</span>
        <p>Photographs are shared through private gallery links. Open the link we sent you and enter its PIN.</p>
    </aside>
</section>

<section class="privacy-note">
    <p class="eyebrow">Made for our people</p>
    <h2>Private by design.</h2>
    <p>Every gallery has its own link and PIN. Please keep both within the group they were shared with.</p>
</section>
@endsection
