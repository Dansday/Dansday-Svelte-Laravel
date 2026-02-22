<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class XSS
{
    private const ALLOWED_HTML = '<span><p><a><b><i><u><strong><br><hr><table><tbody><thead><tr><th><td><ul><ol><li><h1><h2><h3><h4><h5><h6><del><ins><sup><sub><pre><address><img><figure><figcaption><video><source>';

    public function handle(Request $request, Closure $next)
    {
        $input = array_filter($request->all());
        array_walk_recursive($input, function (&$value) {
            if (! is_string($value)) {
                return;
            }
            $value = str_replace(['&lt;', '&gt;'], '', $value);
            $value = strip_tags($value, self::ALLOWED_HTML);
            $value = preg_replace('/\s*on\w+\s*=\s*["\']?[^"\'>]*["\']?/i', '', $value);
            $value = preg_replace('/\s*javascript:\s*/i', '', $value);
        });
        $request->merge($input);
        return $next($request);
    }
}
