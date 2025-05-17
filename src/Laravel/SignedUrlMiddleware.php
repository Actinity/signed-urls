<?php

namespace Actinity\SignedUrls\Laravel;

use Actinity\SignedUrls\Exceptions\InvalidSignedUrl;
use Actinity\SignedUrls\SignedUrlService;
use Closure;

class SignedUrlMiddleware
{
    public function __construct(private SignedUrlService $service) {}

    public function handle($request, Closure $next, ?string $sourceName = null)
    {
        try {
            $this->service->validate($request->fullUrl(), $sourceName);
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
