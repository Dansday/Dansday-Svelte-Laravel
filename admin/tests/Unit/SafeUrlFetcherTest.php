<?php

namespace Tests\Unit;

use App\Exceptions\ContentWriteException;
use App\Support\SafeUrlFetcher;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SafeUrlFetcherTest extends TestCase
{
    public static function blockedUrls(): array
    {
        return [
            'loopback'          => ['http://127.0.0.1/x.png'],
            'loopback alias'    => ['http://127.127.127.127/x.png'],
            'ipv6 loopback'     => ['http://[::1]/x.png'],
            'link local'        => ['http://169.254.169.254/latest/meta-data/'],
            'private 10'        => ['http://10.0.0.5/x.png'],
            'private 172'       => ['http://172.16.4.4/x.png'],
            'private 192'       => ['http://192.168.1.1/x.png'],
            'cgnat'             => ['http://100.64.0.1/x.png'],
            'this network'      => ['http://0.0.0.0/x.png'],
            'multicast'         => ['http://224.0.0.1/x.png'],
            'benchmark'         => ['http://198.18.0.1/x.png'],
            'unique local ipv6' => ['http://[fd00::1]/x.png'],
        ];
    }

    #[DataProvider('blockedUrls')]
    public function test_private_and_reserved_addresses_are_refused(string $url): void
    {
        $this->expectException(ContentWriteException::class);
        SafeUrlFetcher::assertReachablePublicUrl($url);
    }

    public function test_non_http_schemes_are_refused(): void
    {
        foreach (['file:///etc/passwd', 'gopher://8.8.8.8/', 'ftp://8.8.8.8/x.png'] as $url) {
            try {
                SafeUrlFetcher::assertReachablePublicUrl($url);
                $this->fail("Expected \"{$url}\" to be refused.");
            } catch (ContentWriteException $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }
    }

    public function test_credentials_in_the_url_are_refused(): void
    {
        $this->expectException(ContentWriteException::class);
        SafeUrlFetcher::assertReachablePublicUrl('http://user:pass@93.184.216.34/x.png');
    }

    public function test_a_public_address_is_allowed(): void
    {
        SafeUrlFetcher::assertReachablePublicUrl('https://93.184.216.34/x.png');

        $this->addToAssertionCount(1);
    }
}
