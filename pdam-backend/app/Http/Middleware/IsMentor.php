<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class IsMentor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try { 
            $user = JWTAuth::parseToken()->authenticate();
            if ($user && $user->role === 'mentor') {
                return $next($request);
            }

            return response()->json(['message' => 'Unauthorized'], 403);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memverifikasi token',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
