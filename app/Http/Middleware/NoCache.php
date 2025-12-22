<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NoCache
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // StreamedResponse does NOT support header() chaining
        if ($response instanceof StreamedResponse) {
            $response->headers->set(
                'Cache-Control',
                'no-cache, no-store, max-age=0, must-revalidate'
            );
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');

            return $response;
        }

        // Normal responses
        return $response
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }
}
