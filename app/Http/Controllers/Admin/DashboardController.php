<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Photo;
use App\Models\Playlist;
use App\Models\Video;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'collections' => Collection::count(),
            'photos' => Photo::where('is_active', true)->count(),
            'playlists' => Playlist::count(),
            'video' => Video::where('is_active', true)->exists(),
        ]);
    }
}
