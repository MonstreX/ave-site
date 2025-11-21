<?php

namespace Monstrex\AveSite\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Monstrex\AveSite\Models\Redirect;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if redirects table doesn't exist
        if (!Schema::hasTable('ave_site_redirects')) {
            return $next($request);
        }

        // Get current path
        $path = '/' . ltrim($request->path(), '/');

        // Try to find a redirect for this path
        $redirect = Redirect::findByPath($path);

        if ($redirect) {
            // Record the hit
            $redirect->recordHit();

            // Build the destination URL
            $destination = $redirect->to_url;

            // If destination is relative, make it absolute
            if (!str_starts_with($destination, 'http')) {
                $destination = url($destination);
            }

            // Perform the redirect
            return redirect($destination, $redirect->status_code);
        }

        return $next($request);
    }
}
