@extends('layouts.site')

@section('title', ($video?->title ?? 'Our wedding film').' · '.config('wedding.title'))

@section('content')
<section class="film-page">
    <div class="film-heading">
        <p class="eyebrow">Press play, come back with us</p>
        <h1>{{ $video?->title ?? 'Our wedding film' }}</h1>
    </div>

    @if($videoUrl)
        <div class="video-frame">
            <video controls playsinline preload="metadata" @if($posterUrl) poster="{{ $posterUrl }}" @endif>
                <source src="{{ $videoUrl }}" type="{{ $video->mime_type }}">
                Your browser does not support HTML video.
            </video>
        </div>
    @else
        <div class="empty-state light">
            <span class="monogram">S<span>&amp;</span>V</span>
            <h2>The film is being prepared.</h2>
            <p>Please come back soon.</p>
        </div>
    @endif
</section>
@endsection
