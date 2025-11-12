<?php

namespace App\Http\Middleware;

use App\Models\Price;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class RefreshStalePrice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check on dashboard loads
        if ($request->is('admin') || $request->is('admin/')) {
            $latestPrice = Price::latest('quoted_at')->first();

            // If no prices exist or prices are older than 5 minutes, refresh
            if (!$latestPrice || $latestPrice->quoted_at->diffInMinutes(now()) > 5) {
                // Run in background to not block the request
                dispatch(function () {
                    Artisan::call('prices:fetch', ['--force' => true]);
                    Artisan::call('portfolios:update-values');
                })->afterResponse();
            }
        }

        return $next($request);
    }
}
