<?php

namespace App\Http\Middleware;

use App\Models\General;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SeedOnFirstVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        if (General::find(1)) {
            return $next($request);
        }

        $lock = Cache::lock('db_seed_once', 60);

        if (! $lock->get()) {
            return $next($request);
        }

        try {
            if (General::find(1)) {
                return $next($request);
            }
            Artisan::call('db:seed', ['--force' => true]);
        } finally {
            $lock->release();
        }

        return $next($request);
    }
}
