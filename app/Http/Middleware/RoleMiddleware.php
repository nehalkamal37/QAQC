<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // لو المستخدم معندوش رول أصلاً
        if (! $user->role || ! in_array($user->role->name, $roles)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
