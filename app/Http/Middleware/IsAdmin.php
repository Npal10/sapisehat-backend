<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        // Jika bukan admin, redirect ke login admin dengan pesan error
        Auth::logout();
        return redirect()->route('admin.login')->with('error', 'Akses ditolak. Halaman ini khusus untuk Admin / Dinas Peternakan.');
    }
}
