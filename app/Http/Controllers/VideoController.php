<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Services\MediaUrl;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function __invoke(MediaUrl $media): View
    {
        $video = Video::query()->where('is_active', true)->first();
        $videoUrl = $video?->storage_key ? $media->temporary($video->disk, $video->storage_key) : null;
        $posterUrl = $video?->poster_key ? $media->temporary($video->disk, $video->poster_key) : null;

        return view('video', compact('video', 'videoUrl', 'posterUrl'));
    }
}
