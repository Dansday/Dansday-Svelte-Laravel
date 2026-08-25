<?php

namespace Tests\Unit;

use App\Models\McpToken;
use Tests\TestCase;

/**
 * Token handling that can be checked without touching the database. Minting,
 * listing and revoking all hit MySQL, so those are exercised by hand in the
 * panel rather than here.
 */
class McpTokenTest extends TestCase
{
    public function test_tokens_carry_a_recognisable_prefix(): void
    {
        // Makes a leaked token obvious in logs and to secret scanners.
        $this->assertSame('mcp_live_', McpToken::PREFIX);
    }

    public function test_tokens_are_hashed_with_sha256(): void
    {
        $hash = McpToken::hash('abc');

        $this->assertSame(hash('sha256', 'abc'), $hash);
        $this->assertSame(64, strlen($hash));
        $this->assertTrue(ctype_xdigit($hash));
    }

    public function test_hashing_is_deterministic_and_input_sensitive(): void
    {
        $this->assertSame(McpToken::hash('abc'), McpToken::hash('abc'));
        $this->assertNotSame(McpToken::hash('abc'), McpToken::hash('abd'));
    }

    public function test_an_empty_bearer_token_is_rejected_without_a_lookup(): void
    {
        // The middleware passes '' straight through when the header is absent.
        $this->assertNull(McpToken::findActive(''));
    }

    public function test_a_token_is_active_until_it_is_revoked(): void
    {
        $token = new McpToken(['name' => 'claude-code']);
        $this->assertTrue($token->isActive());

        $token->revoked_at = now();
        $this->assertFalse($token->isActive());
    }

    public function test_the_hash_is_never_serialised(): void
    {
        $token = new McpToken(['name' => 'claude-code', 'token_hash' => 'secret']);

        $this->assertArrayNotHasKey('token_hash', $token->toArray());
    }
}
