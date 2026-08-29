<?php

namespace App\Support;

use App\Exceptions\ContentWriteException;
use Illuminate\Support\Facades\Http;

class SafeUrlFetcher
{
    private const BLOCKED_CIDRS = [
        '100.64.0.0/10',
        '192.0.0.0/24',
        '192.0.2.0/24',
        '198.18.0.0/15',
        '198.51.100.0/24',
        '203.0.113.0/24',
        '224.0.0.0/4',
    ];

    public static function fetch(string $url, int $maxBytes): array
    {
        self::assertReachablePublicUrl($url);

        $response = Http::withOptions([
            'stream'          => true,
            'allow_redirects' => false,
        ])->timeout(30)->get($url);

        if ($response->status() >= 300 && $response->status() < 400) {
            throw new ContentWriteException(
                "Refusing to follow a redirect from {$url}. Give the final URL, or upload the file to /mcp/uploads instead."
            );
        }

        if (! $response->successful()) {
            throw new ContentWriteException("Could not download from {$url} (HTTP {$response->status()}).");
        }

        $declared = (int) $response->header('Content-Length');

        if ($declared > $maxBytes) {
            throw new ContentWriteException(
                "That URL declares ".self::human($declared).", over the ".self::human($maxBytes)." limit."
            );
        }

        $stream = $response->toPsrResponse()->getBody();
        $bytes = '';

        while (! $stream->eof()) {
            $bytes .= $stream->read(65536);

            if (strlen($bytes) > $maxBytes) {
                $stream->close();

                throw new ContentWriteException(
                    "That URL returned more than ".self::human($maxBytes).", so the download was aborted."
                );
            }
        }

        $stream->close();

        if ($bytes === '') {
            throw new ContentWriteException("That URL returned an empty body.");
        }

        return [$bytes, $response->header('Content-Type') ?: 'application/octet-stream'];
    }

    public static function assertReachablePublicUrl(string $url): void
    {
        $parts = parse_url($url);

        if ($parts === false || empty($parts['host'])) {
            throw new ContentWriteException("\"{$url}\" is not a usable URL.");
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new ContentWriteException("Only http and https URLs are allowed. Got \"{$scheme}\".");
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new ContentWriteException('URLs carrying credentials are not allowed.');
        }

        $host = trim($parts['host'], '[]');

        foreach (self::resolve($host) as $ip) {
            if (! self::isPublicIp($ip)) {
                throw new ContentWriteException(
                    "\"{$host}\" resolves to {$ip}, which is a private or reserved address. Refusing to fetch it."
                );
            }
        }
    }

    private static function resolve(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return [$host];
        }

        $ips = [];

        foreach (['A' => DNS_A, 'AAAA' => DNS_AAAA] as $key => $type) {
            $records = @dns_get_record($host, $type) ?: [];

            foreach ($records as $record) {
                $ip = $record['ip'] ?? $record['ipv6'] ?? null;

                if ($ip !== null) {
                    $ips[] = $ip;
                }
            }
        }

        if ($ips === []) {
            throw new ContentWriteException("\"{$host}\" does not resolve to any address.");
        }

        return array_unique($ips);
    }

    private static function isPublicIp(string $ip): bool
    {
        $public = filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        if ($public === false) {
            return false;
        }

        foreach (self::BLOCKED_CIDRS as $cidr) {
            if (self::inCidr($ip, $cidr)) {
                return false;
            }
        }

        return true;
    }

    private static function inCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $mask = -1 << (32 - (int) $bits);

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }

    private static function human(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).'MB';
        }

        return round($bytes / 1024).'KB';
    }
}
