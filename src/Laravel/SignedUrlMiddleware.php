<?php

namespace Actinity\SignedUrls\Laravel;

use Actinity\SignedUrls\Exceptions\InvalidSignedUrl;
use Actinity\SignedUrls\SignedUrlService;
use Closure;

class SignedUrlMiddleware
{
    private $service;

    public function __construct(SignedUrlService $service)
    {
        $this->service = $service;
    }

    public function handle($request, Closure $next, $keyName = 'default')
    {
        try {
            $this->service->validate($request->fullUrl(), $keyName);
        } catch (InvalidSignedUrl $exception) {
            if (config('app.debug') || $request->expectsJson()) {
                return response()->json($exception->errors(), 401);
            } else {
                abort(401);
            }
        }

        return $next($request);
    }
}
