<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Tindakan ini hanya dapat dilakukan oleh Administrator.',
                ], 403);
            }

            abort(403, 'Akses Ditolak. Anda tidak memiliki izin Administrator untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
