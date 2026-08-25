<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class McpToken extends Model
{
    use HasFactory;

    protected $table = 'mcp_tokens';

    protected $fillable = ['name', 'token_hash'];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'last_used_at' => 'datetime',
        'revoked_at'   => 'datetime',
    ];

    public const PREFIX = 'mcp_live_';

    public static function mint(string $name): array
    {
        $plain = self::PREFIX . Str::random(48);

        $token = self::create([
            'name'       => $name,
            'token_hash' => self::hash($plain),
        ]);

        return [$token, $plain];
    }

    public static function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }

    public static function findActive(string $plain): ?self
    {
        if ($plain === '') {
            return null;
        }

        return self::where('token_hash', self::hash($plain))
            ->whereNull('revoked_at')
            ->first();
    }

    public function markUsed(): void
    {
        $this->forceFill(['last_used_at' => now()])->saveQuietly();
    }

    public function revoke(): void
    {
        if ($this->revoked_at) {
            return;
        }

        $this->forceFill(['revoked_at' => now()])->save();
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }
}
