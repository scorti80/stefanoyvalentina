<?php

namespace App\Http\Middleware;

use App\Services\WeddingAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSiteAccess
{
    public function __construct(private WeddingAccess $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->access->hasSiteAccess($request)) {
            return redirect()->route('home')->with('notice', 'Enter the private access PIN to continue.');
        }

        return $next($request);
    }
}
