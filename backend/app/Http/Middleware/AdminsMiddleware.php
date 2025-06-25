<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Admins;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if(!$user || !Admins::where('username', $user->username)->exists()){
            return response()->json([
                'message' => 'unauthorized, admin only'
            ], 403);
        }

        return $next($request);
    }
}
