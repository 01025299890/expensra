<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class MeasureResponseTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $response = $next($request);
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;
        if ($duration > 900) {
            Log::warning('slow page detected', [
                'url' => $request->fullUrl(),
                'duration' => number_format($duration, 2) . ' ms'
            ]);
        }
        error_log('Response time: ' . number_format($duration, 2) . 'ms for URL: ' . $request->fullUrl()); // امسحها بعد التجريب
        $response->headers->set('X-Response-Time', number_format($duration, 2) . ' ms');
        return $response;
    }
}
