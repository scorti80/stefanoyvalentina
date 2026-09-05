<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function edit(): View
    {
        return view('admin.video', ['video' => Video::query()->first() ?? new Video]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'disk' => ['required', Rule::in(array_keys(config('filesystems.disks')))],
            'storage_key' => ['nullable', 'string', 'max:500'],
            'poster_key' => ['nullable', 'string', 'max:500'],
            'mime_type' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];

        Video::query()->updateOrCreate(['id' => 1], $data);

        return back()->with('status', 'Video settings saved.');
    }
}
