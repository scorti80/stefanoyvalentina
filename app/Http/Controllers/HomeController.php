<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Services\WeddingAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, WeddingAccess $access): View
    {
        if (! $access->hasSiteAccess($request)) {
            return view('access.site');
        }

        return view('home', ['video' => Video::query()->where('is_active', true)->first()]);
    }

    public function unlock(Request $request, WeddingAccess $access): RedirectResponse
    {
        $validated = $request->validate(['pin' => ['required', 'string', 'max:100']]);

        if (! $access->verifySitePin($validated['pin'])) {
            return back()->withErrors(['pin' => 'That PIN is not correct.'])->onlyInput();
        }

        $access->grantSite($request);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function lock(Request $request): RedirectResponse
    {
        $request->session()->forget('wedding');
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
