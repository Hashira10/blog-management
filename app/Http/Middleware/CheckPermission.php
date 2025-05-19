<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * @param Request $request
     * @param Closure $next
     * @param string $permission - имя разрешения
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = $request->user();

        if (!$user || !$user->hasPermission($permission)) {
            abort(403, 'У вас нет прав для доступа к этому ресурсу.');
        }

        return $next($request);
    }
}
