<?php

namespace App\Services;

use App\Exceptions\ContentWriteException;
use App\Models\General;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LinkedInService
{
    public const SCOPES = 'openid profile w_member_social';

    public const MAX_COMMENTARY = 3000;

    private const AUTHORIZE_URL = 'https://www.linkedin.com/oauth/v2/authorization';

    private const TOKEN_URL = 'https://www.linkedin.com/oauth/v2/accessToken';

    private const USERINFO_URL = 'https://api.linkedin.com/v2/userinfo';

    private const POSTS_URL = 'https://api.linkedin.com/rest/posts';

    private const API_VERSION = '202608';

    public static function settings(): ?General
    {
        return General::find(1);
    }

    public static function redirectUri(): string
    {
        return rtrim((string) config('app.url'), '/').'/admin/linkedin/callback';
    }

    public static function connectUrl(): string
    {
        return rtrim((string) config('app.url'), '/').'/admin/linkedin/redirect';
    }

    public static function publicSiteUrl(): string
    {
        $url = rtrim((string) config('app.url'), '/');
        $host = (string) parse_url($url, PHP_URL_HOST);

        if (str_starts_with($host, 'admin.')) {
            return str_replace('//'.$host, '//'.substr($host, 6), $url);
        }

        return $url;
    }

    public static function isConfigured(): bool
    {
        return trim((string) config('services.linkedin.client_id')) !== ''
            && trim((string) config('services.linkedin.client_secret')) !== '';
    }

    public static function authorizeUrl(string $state): string
    {
        return self::AUTHORIZE_URL.'?'.http_build_query([
            'response_type' => 'code',
            'client_id'     => trim((string) config('services.linkedin.client_id')),
            'redirect_uri'  => self::redirectUri(),
            'state'         => $state,
            'scope'         => self::SCOPES,
        ]);
    }

    public static function status(): array
    {
        $general = self::settings();

        if (! self::isConfigured()) {
            return [
                'connected'   => false,
                'reason'      => 'not_configured',
                'message'     => 'LINKEDIN_CLIENT_ID and LINKEDIN_CLIENT_SECRET are not set in the environment.',
            ];
        }

        $token = trim((string) $general->linkedin_access_token);
        $person = trim((string) $general->linkedin_person_urn);

        if ($token === '' || $person === '') {
            return [
                'connected'   => false,
                'reason'      => 'not_connected',
                'message'     => 'No LinkedIn account is connected. Open connect_url in a browser while signed in to the admin panel, approve access, then try again.',
                'connect_url' => self::connectUrl(),
            ];
        }

        $expiresAt = $general->linkedin_token_expires_at ? Carbon::parse($general->linkedin_token_expires_at) : null;

        if ($expiresAt && $expiresAt->isPast()) {
            return [
                'connected'   => false,
                'reason'      => 'expired',
                'message'     => 'The stored LinkedIn token expired on '.$expiresAt->toDateString().'. Open connect_url in a browser to reconnect, then try again.',
                'expires_at'  => $expiresAt->toDateTimeString(),
                'connect_url' => self::connectUrl(),
            ];
        }

        return [
            'connected'  => true,
            'as'         => $person,
            'expires_at' => $expiresAt?->toDateTimeString(),
            'days_left'  => $expiresAt ? max(0, now()->diffInDays($expiresAt, false)) : null,
            'scopes'     => self::SCOPES,
        ];
    }

    public static function exchangeCode(string $code): array
    {
        $response = Http::asForm()->timeout(30)->post(self::TOKEN_URL, [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => self::redirectUri(),
            'client_id'     => trim((string) config('services.linkedin.client_id')),
            'client_secret' => trim((string) config('services.linkedin.client_secret')),
        ]);

        if (! $response->successful()) {
            Log::warning('LinkedIn token exchange failed: HTTP '.$response->status().' '.substr($response->body(), 0, 300));

            return ['ok' => false, 'error' => 'Token exchange failed with HTTP '.$response->status().'.'];
        }

        $accessToken = (string) $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in');

        if ($accessToken === '') {
            return ['ok' => false, 'error' => 'LinkedIn did not return an access token.'];
        }

        $userinfo = Http::withToken($accessToken)->timeout(30)->get(self::USERINFO_URL);

        if (! $userinfo->successful()) {
            Log::warning('LinkedIn userinfo failed: HTTP '.$userinfo->status().' '.substr($userinfo->body(), 0, 300));

            return ['ok' => false, 'error' => 'Signed in, but could not read the member id (HTTP '.$userinfo->status().').'];
        }

        $sub = trim((string) $userinfo->json('sub'));

        if ($sub === '') {
            return ['ok' => false, 'error' => 'Signed in, but LinkedIn returned no member id.'];
        }

        General::where('id', 1)->update([
            'linkedin_access_token'     => $accessToken,
            'linkedin_token_expires_at' => $expiresIn > 0 ? now()->addSeconds($expiresIn) : null,
            'linkedin_person_urn'       => 'urn:li:person:'.$sub,
        ]);

        return [
            'ok'         => true,
            'as'         => 'urn:li:person:'.$sub,
            'name'       => $userinfo->json('name'),
            'expires_in' => $expiresIn,
        ];
    }

    public static function disconnect(): void
    {
        General::where('id', 1)->update([
            'linkedin_access_token'     => null,
            'linkedin_token_expires_at' => null,
            'linkedin_person_urn'       => null,
        ]);
    }

    public static function articleUrl(int $id): array
    {
        $article = DB::table('articles')->where('id', $id)->first();

        if (! $article) {
            throw new ContentWriteException("No article with id {$id}.");
        }

        if ((int) $article->enable !== 1) {
            throw new ContentWriteException("Article {$id} is not published, so its URL would 404. Publish it first.");
        }

        $slug = self::slug((string) $article->title);

        if ($slug === '') {
            throw new ContentWriteException("Article {$id} has a title that produces an empty slug.");
        }

        return [
            'title' => (string) $article->title,
            'url'   => self::publicSiteUrl().'/articles/'.$slug,
        ];
    }

    public static function slug(string $name): string
    {
        $value = mb_strtolower(Str::ascii($name), 'UTF-8');
        $value = preg_replace('/[^a-z0-9\s-]/', '', $value) ?? '';
        $value = preg_replace('/[\s-]+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    public static function share(string $commentary, string $visibility = 'PUBLIC'): array
    {
        $status = self::status();

        if (empty($status['connected'])) {
            return $status;
        }

        $general = self::settings();

        $response = Http::withToken(trim((string) $general->linkedin_access_token))
            ->withHeaders([
                'X-Restli-Protocol-Version' => '2.0.0',
                'LinkedIn-Version'          => self::API_VERSION,
            ])
            ->timeout(30)
            ->post(self::POSTS_URL, [
                'author'                    => trim((string) $general->linkedin_person_urn),
                'commentary'                => $commentary,
                'visibility'                => $visibility,
                'distribution'              => [
                    'feedDistribution'               => 'MAIN_FEED',
                    'targetEntities'                 => [],
                    'thirdPartyDistributionChannels' => [],
                ],
                'lifecycleState'            => 'PUBLISHED',
                'isReshareDisabledByAuthor' => false,
            ]);

        if ($response->status() === 401) {
            return [
                'ok'          => false,
                'reason'      => 'unauthorized',
                'message'     => 'LinkedIn rejected the stored token. Open connect_url in a browser to reconnect, then try again.',
                'connect_url' => self::connectUrl(),
            ];
        }

        if (! $response->successful()) {
            Log::warning('LinkedIn post failed: HTTP '.$response->status().' '.substr($response->body(), 0, 500));

            return [
                'ok'       => false,
                'reason'   => 'post_failed',
                'status'   => $response->status(),
                'message'  => 'LinkedIn returned HTTP '.$response->status().'.',
                'response' => substr($response->body(), 0, 500),
            ];
        }

        $urn = $response->header('x-restli-id');

        return [
            'ok'         => true,
            'post_urn'   => $urn ?: null,
            'post_url'   => $urn ? 'https://www.linkedin.com/feed/update/'.$urn.'/' : null,
            'visibility' => $visibility,
            'characters' => mb_strlen($commentary),
        ];
    }
}
