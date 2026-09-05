@extends('layouts.site')

@section('title', $playlist->name.' · '.config('wedding.title'))

@section('content')
<section class="gallery-heading">
    <p class="eyebrow">A private collection</p>
    <h1>{{ $playlist->name }}</h1>
    @if($playlist->description)<p>{{ $playlist->description }}</p>@endif
    <div class="gallery-meta"><span>{{ $photos->total() }} {{ Str::plural('photograph', $photos->total()) }}</span><span>Shared privately</span></div>
</section>

@if($photos->count())
    <section class="photo-grid" aria-label="Wedding photographs">
        @foreach($photos as $photo)
            <article class="photo-tile" data-lightbox-item data-src="{{ $photo->preview_url }}" data-name="{{ $photo->filename }}">
                <button class="photo-open" type="button" aria-label="Open {{ $photo->filename }}">
                    <img src="{{ $photo->preview_url }}" alt="Wedding photograph" loading="lazy" decoding="async">
                </button>
                @if($playlist->allow_sd_download || $playlist->allow_hd_download)
                    <div class="photo-actions">
                        @if($playlist->allow_sd_download && $photo->web_key)<a href="{{ route('gallery.download', [$token, $photo, 'sd']) }}">SD</a>@endif
                        @if($playlist->allow_hd_download && $photo->hd_key)<a href="{{ route('gallery.download', [$token, $photo, 'hd']) }}">HD</a>@endif
                    </div>
                @endif
            </article>
        @endforeach
    </section>
    <div class="pagination">{{ $photos->links() }}</div>
@else
    <div class="empty-state"><h2>This gallery is being prepared.</h2><p>Photographs will appear here once they have been selected.</p></div>
@endif

<dialog class="lightbox" data-lightbox aria-label="Photo viewer">
    <button type="button" class="lightbox-close" data-lightbox-close aria-label="Close">×</button>
    <button type="button" class="lightbox-nav lightbox-prev" data-lightbox-prev aria-label="Previous photo">←</button>
    <figure><img src="" alt="Enlarged wedding photograph" data-lightbox-image><figcaption data-lightbox-caption></figcaption></figure>
    <button type="button" class="lightbox-nav lightbox-next" data-lightbox-next aria-label="Next photo">→</button>
</dialog>
@endsection
