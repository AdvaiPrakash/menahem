<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $requiredRole = null): Response
    {
        if (! Auth::check()) {
            return redirect()->guest(route('admin.login'))->with('error', 'Please sign in to access the administration panel.');
        }

        $user = Auth::user();

        // Customer accounts cannot access the admin panel
        if ($user->isCustomer() || ! in_array($user->role?->value ?? (string) $user->role, ['admin', 'editor', 'viewer'], true)) {
            abort(403, 'Unauthorized. Staff privileges required.');
        }

        // Check required role if specified
        if ($requiredRole === 'admin' && ! $user->isAdmin()) {
            abort(403, 'Unauthorized. Administrator privileges required.');
        }

        if ($requiredRole === 'editor' && ! $user->canEditContent()) {
            abort(403, 'Unauthorized. Editor or Administrator privileges required.');
        }

        // For non-safe HTTP methods (POST, PUT, PATCH, DELETE), require edit permissions
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            // Logout and personal notification read-state updates are allowed for all authenticated staff
            if (! $request->routeIs('admin.logout', 'admin.notifications.read', 'admin.notifications.read-all') && ! $user->canEditContent()) {
                abort(403, 'Viewers have read-only access. You cannot modify records.');
            }
        }

        return $next($request);
    }
}
