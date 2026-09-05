<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Playlist;
use App\Services\MediaUrl;
use App\Services\WeddingAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GalleryController extends Controller
{
    public function show(Request $request, string $token, WeddingAccess $access, MediaUrl $media): View
    {
        $playlist = $this->playlist($token);

        if (! $playlist->isAvailable()) {
            abort(404);
        }

        if (! $access->canViewPlaylist($request, $playlist)) {
            return view('access.playlist', compact('playlist', 'token'));
        }

        $photos = $playlist->photos()
            ->where('photos.is_active', true)
            ->with('collection')
            ->paginate(48)
            ->withQueryString();

        $photos->getCollection()->transform(function (Photo $photo) use ($media) {
            $key = $photo->web_key ?: $photo->hd_key;
            $photo->preview_url = $media->temporary($photo->collection->disk, $key);

            return $photo;
        });

        return view('gallery.show', compact('playlist', 'photos', 'token'));
    }

    public function unlock(Request $request, string $token, WeddingAccess $access): RedirectResponse
    {
        $playlist = $this->playlist($token);

        if (! $playlist->isAvailable()) {
            abort(404);
        }

        $validated = $request->validate(['pin' => ['required', 'string', 'max:100']]);

        if (! Hash::check($validated['pin'], $playlist->pin_hash)) {
            return back()->withErrors(['pin' => 'That PIN is not correct.'])->onlyInput();
        }

        $access->grantPlaylist($request, $playlist);
        $request->session()->regenerate();

        return redirect()->route('gallery.show', ['token' => $token]);
    }

    public function download(Request $request, string $token, Photo $photo, string $variant, WeddingAccess $access): RedirectResponse|StreamedResponse
    {
        $playlist = $this->playlist($token);

        if (! $playlist->isAvailable() || ! $access->canViewPlaylist($request, $playlist) || ! $playlist->photos()->whereKey($photo->id)->exists()) {
            abort(404);
        }

        if (! in_array($variant, ['sd', 'hd'], true)) {
            abort(404);
        }

        $allowed = $variant === 'sd' ? $playlist->allow_sd_download : $playlist->allow_hd_download;
        $key = $variant === 'sd' ? $photo->web_key : $photo->hd_key;

        if (! $allowed || ! $key) {
            abort(404);
        }

        $disk = $photo->collection->disk;
        $name = pathinfo($photo->filename, PATHINFO_FILENAME).'-'.strtoupper($variant).'.'.pathinfo($key, PATHINFO_EXTENSION);

        if ($disk === 's3') {
            $url = Storage::disk($disk)->temporaryUrl($key, now()->addMinutes(10), [
                'ResponseContentDisposition' => 'attachment; filename="'.$name.'"',
                'ResponseContentType' => $photo->mime_type ?: 'application/octet-stream',
            ]);

            return redirect()->away($url);
        }

        return Storage::disk($disk)->download($key, $name);
    }

    private function playlist(string $token): Playlist
    {
        return Playlist::query()->where('share_token_hash', hash('sha256', $token))->firstOrFail();
    }
}
