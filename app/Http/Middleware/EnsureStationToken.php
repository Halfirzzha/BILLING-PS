<?php

namespace App\Http\Middleware;

use App\Models\Station;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStationToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $station = $request->route('station');
        $token = (string) $request->header('X-Station-Token');

        if (! $station instanceof Station || $token === '' || ! hash_equals((string) $station->device_token, $token)) {
            abort(401, 'Invalid station token.');
        }

        return $next($request);
    }
}
