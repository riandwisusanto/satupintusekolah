<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\RequestLog;
use Illuminate\Support\Facades\Log;

class LogRequestToDB
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            try {
                $payload = $request->except(['password', 'token', 'file']);
                $responseBody = method_exists($response, 'getContent') ? $response->getContent() : null;

                RequestLog::create([
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'payload' => json_encode($payload),
                    'user_id' => optional($request->user())->id,
                    'response_code' => $response->getStatusCode(),
                    'response_body' => $responseBody,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Gagal log request ke DB: ' . $e->getMessage());
            }
        }

        return $response;
    }
}
