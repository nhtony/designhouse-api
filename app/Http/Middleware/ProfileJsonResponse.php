<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProfileJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $response = $next($request);

        // check if debugbar is enabled
        if (!app()->bound('debugbar') || !app('debugbar')->isEnabled()) {
            return $response;
        }

        if ($response instanceof JsonResponse && $request->has('_debug')) {
            $response->setData(array_merge([
                '_debugbar' => Arr::only(app('debugbar')->getData(),'queries')
            ],$response->getData(true)));
        }

        return $response;
    }
}
