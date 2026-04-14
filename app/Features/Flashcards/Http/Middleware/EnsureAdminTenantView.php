<?php

namespace App\Features\Flashcards\Http\Middleware;

use App\Helpers\TenancyHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Staff-only routes must be accessed on the tenant admin host (TenantView code "admin").
 */
class EnsureAdminTenantView
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!TenancyHelper::isAdminView()) {
            abort(403);
        }

        return $next($request);
    }
}
