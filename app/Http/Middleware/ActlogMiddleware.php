<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\ActLogService;
use Illuminate\Http\Request;

/**
 * 操作ログを記録するミドルウェア
 */
class ActlogMiddleware
{
    public function __construct(ActLogService $actLogService)
    {
        $this->actLogService = $actLogService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $this->actLogService->create($request, $response->getStatusCode());

        return $response;
    }
}
