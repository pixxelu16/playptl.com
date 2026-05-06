<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrimLeadingHtmlWhitespace
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response instanceof Response || ! is_string($content = $response->getContent())) {
            return $response;
        }

        $type = strtolower((string) $response->headers->get('Content-Type', ''));
        if ($type !== '' && ! str_contains($type, 'html')) {
            return $response;
        }

        $trimmed = preg_replace('/^[\x{FEFF}\s]+/u', '', $content);
        if ($trimmed === null || $trimmed === $content) {
            return $response;
        }

        return $response->setContent($trimmed);
    }
}
