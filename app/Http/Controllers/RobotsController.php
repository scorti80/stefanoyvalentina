<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        return response("User-agent: *\nDisallow: /\n", 200, ['Content-Type' => 'text/plain']);
    }
}
