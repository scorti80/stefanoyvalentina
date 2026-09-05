<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncCollectionMedia;
use App\Models\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function index(): View
    {
        return view('admin.collections.index', [
            'collections' => Collection::query()->withCount(['photos', 'photos as active_photos_count' => fn ($query) => $query->where('is_active', true)])->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.collections.form', ['collection' => new Collection]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        Collection::create($data);

        return redirect()->route('admin.collections.index')->with('status', 'Collection created. You can sync it now.');
    }

    public function edit(Collection $collection): View
    {
        return view('admin.collections.form', compact('collection'));
    }

    public function update(Request $request, Collection $collection): RedirectResponse
    {
        $data = $this->validated($request, $collection);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $collection->update($data);

        return redirect()->route('admin.collections.index')->with('status', 'Collection updated.');
    }

    public function destroy(Collection $collection): RedirectResponse
    {
        $collection->delete();

        return redirect()->route('admin.collections.index')->with('status', 'Collection and its indexed photo records were removed. S3 files were not touched.');
    }

    public function sync(Collection $collection): RedirectResponse
    {
        SyncCollectionMedia::dispatch($collection);

        return back()->with('status', 'The collection sync was queued. Refresh in a moment to see the new total.');
    }

    private function validated(Request $request, ?Collection $collection = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('collections')->ignore($collection)],
            'disk' => ['required', Rule::in(array_keys(config('filesystems.disks')))],
            'web_prefix' => ['required', 'string', 'max:255'],
            'hd_prefix' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
