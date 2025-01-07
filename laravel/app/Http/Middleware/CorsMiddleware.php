<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ajouter les en-têtes CORS
        $response->headers->set('Access-Control-Allow-Origin', '*'); // Permet tout domaine (à ajuster pour la production)
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        // Gérer les requêtes OPTIONS
        if ($request->getMethod() === 'OPTIONS') {
            return response()->json('OK', 200, $response->headers->all());
        }

        return $response;
    }
}
