<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        $installed = file_exists(storage_path('installed'));
        if ($installed && ! $request->is('install*')) {
            return redirect()->route('dashboard');
        }
        if ($installed && $request->is('install*')) {
            return redirect()->route('dashboard');
        }
        return $next($request);
    }
}
