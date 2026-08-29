<?php

namespace App\Http\Controllers;

use App\Models\LinkedInPost;
use App\Models\LinkedInScheduledPost;
use App\Models\User;
use App\Services\LinkedInService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LinkedInController extends Controller
{
    public function index()
    {
        return view('admin.pages.linkedin')
            ->with('user', User::find(1))
            ->with('status', LinkedInService::status())
            ->with('configured', LinkedInService::isConfigured())
            ->with('scheduled', LinkedInScheduledPost::orderByRaw("status = 'pending' DESC")->orderBy('publish_at')->take(25)->get())
            ->with('posts', LinkedInPost::orderByDesc('posted_at')->orderByDesc('id')->take(25)->get());
    }

    public function disconnect()
    {
        LinkedInService::disconnect();

        return redirect('/admin/linkedin')->with('ok-update', '');
    }

    public function cancelScheduled($id)
    {
        $row = LinkedInScheduledPost::find($id);

        if (! $row || ! $row->isPending()) {
            return redirect('/admin/linkedin')->with('no-delete', '');
        }

        $row->forceFill([
            'status'       => LinkedInScheduledPost::CANCELLED,
            'cancelled_at' => now(),
        ])->save();

        return redirect('/admin/linkedin')->with('ok-update', '');
    }

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
