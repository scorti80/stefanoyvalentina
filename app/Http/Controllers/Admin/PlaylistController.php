<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Photo;
use App\Models\Playlist;
use App\Services\MediaUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlaylistController extends Controller
{
    public function index(): View
    {
        return view('admin.playlists.index', [
            'playlists' => Playlist::query()->withCount('photos')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.playlists.form', ['playlist' => new Playlist]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, true);
        $token = Str::random(48);
        $data['slug'] = Str::slug($data['name']);
        $data['share_token'] = $token;
        $data['share_token_hash'] = hash('sha256', $token);
        $data['pin_hash'] = Hash::make($data['pin']);
        unset($data['pin']);
        $playlist = Playlist::create($data);

        return redirect()->route('admin.playlists.photos', $playlist)->with('status', 'Playlist created. Now choose its photos.');
    }

    public function edit(Playlist $playlist): View
    {
        return view('admin.playlists.form', compact('playlist'));
    }

    public function update(Request $request, Playlist $playlist): RedirectResponse
    {
        $data = $this->validated($request, false);
        $data['slug'] = Str::slug($data['name']);

        if (! empty($data['pin'])) {
            $data['pin_hash'] = Hash::make($data['pin']);
            $data['access_revision'] = $playlist->access_revision + 1;
        }

        unset($data['pin']);
        $playlist->update($data);

        return redirect()->route('admin.playlists.index')->with('status', 'Playlist updated.');
    }

    public function destroy(Playlist $playlist): RedirectResponse
    {
        $playlist->delete();

        return back()->with('status', 'Playlist removed. Its photographs remain in their collections.');
    }

    public function rotate(Playlist $playlist): RedirectResponse
    {
        $token = Str::random(48);
        $playlist->update(['share_token' => $token, 'share_token_hash' => hash('sha256', $token)]);

        return back()->with('status', 'A new private link was generated. The previous link no longer works.');
    }

    public function photos(Request $request, Playlist $playlist, MediaUrl $media): View
    {
        $query = Photo::query()->where('is_active', true)->with('collection')
            ->when($request->integer('collection'), fn ($query, $id) => $query->where('collection_id', $id))
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where('filename', 'like', '%'.$search.'%'))
            ->orderBy('collection_id')->orderBy('sort_order');

        $photos = $query->paginate(48)->withQueryString();
        $assigned = $playlist->photos()->pluck('photos.id')->all();

        $photos->getCollection()->transform(function (Photo $photo) use ($media) {
            $photo->preview_url = $media->temporary($photo->collection->disk, $photo->web_key ?: $photo->hd_key);

            return $photo;
        });

        return view('admin.playlists.photos', [
            'playlist' => $playlist,
            'photos' => $photos,
            'assigned' => $assigned,
            'collections' => Collection::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function updatePhotos(Request $request, Playlist $playlist): RedirectResponse
    {
        $data = $request->validate([
            'visible_ids' => ['array'],
            'visible_ids.*' => ['integer', 'exists:photos,id'],
            'photo_ids' => ['array'],
            'photo_ids.*' => ['integer', 'exists:photos,id'],
        ]);

        $visible = collect($data['visible_ids'] ?? [])->map(fn ($id) => (int) $id);
        $selected = collect($data['photo_ids'] ?? [])->map(fn ($id) => (int) $id);
        $playlist->photos()->detach($visible->diff($selected));
        $playlist->photos()->syncWithoutDetaching($selected->mapWithKeys(fn ($id) => [$id => ['position' => $id]])->all());

        return back()->with('status', 'Photo selections on this page were saved.');
    }

    private function validated(Request $request, bool $creating): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'pin' => [$creating ? 'required' : 'nullable', 'string', 'min:4', 'max:100'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'allow_sd_download' => ['nullable', 'boolean'],
            'allow_hd_download' => ['nullable', 'boolean'],
            'allow_zip_download' => ['nullable', 'boolean'],
        ]) + [
            'is_active' => $request->boolean('is_active'),
            'allow_sd_download' => $request->boolean('allow_sd_download'),
            'allow_hd_download' => $request->boolean('allow_hd_download'),
            'allow_zip_download' => $request->boolean('allow_zip_download'),
        ];
    }
}
