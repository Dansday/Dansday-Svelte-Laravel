<?php

namespace App\Http\Middleware;

use App\Models\General;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RunSetupOnFirstVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->runSeedIfNeeded();

        return $next($request);
    }

    /** Seed when no settings row exists. */
    protected function runSeedIfNeeded(): void
    {
        try {
            if (General::find(1)) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $lock = Cache::lock('db_seed_once', 60);

        if (! $lock->get()) {
            return;
        }

        try {
            if (General::find(1)) {
                return;
            }
            Artisan::call('db:seed', ['--force' => true]);
        } finally {
            $lock->release();
        }
    }
}
