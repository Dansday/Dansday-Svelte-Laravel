<?php

namespace App\Http\Controllers;

use App\Services\LinkedInService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LinkedInController extends Controller
{
    public function connect(Request $request)
    {
        if (! LinkedInService::isConfigured()) {
            return response('LINKEDIN_CLIENT_ID and LINKEDIN_CLIENT_SECRET are not set in the environment.', 500);
        }

        $state = Str::random(40);
        $request->session()->put('linkedin_oauth_state', $state);

        return redirect()->away(LinkedInService::authorizeUrl($state));
    }

    public function callback(Request $request)
    {
        $expected = $request->session()->pull('linkedin_oauth_state');

        if ($request->filled('error')) {
            return response('LinkedIn refused: '.$request->input('error_description', $request->input('error')), 400);
        }

        if (! $expected || $request->input('state') !== $expected) {
            return response('State mismatch. Open '.LinkedInService::connectUrl().' again to restart.', 400);
        }

        $code = (string) $request->input('code');
        if ($code === '') {
            return response('LinkedIn returned no authorization code.', 400);
        }

        $result = LinkedInService::exchangeCode($code);

        if (empty($result['ok'])) {
            return response('Could not complete the connection: '.($result['error'] ?? 'unknown error'), 400);
        }

        return response('Connected as '.($result['name'] ?: $result['as']).'. You can close this tab and post again.');
    }
}
