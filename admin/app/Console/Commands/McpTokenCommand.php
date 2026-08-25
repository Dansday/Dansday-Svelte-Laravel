<?php

namespace App\Console\Commands;

use App\Models\McpToken;
use Illuminate\Console\Command;

class McpTokenCommand extends Command
{
    protected $signature = 'mcp:token
                            {name? : A label for the client this token is for}
                            {--list : List existing tokens}
                            {--revoke= : Revoke the token with this id}';

    protected $description = 'Mint, list, or revoke MCP API tokens';

    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listTokens();
        }

        if ($revokeId = $this->option('revoke')) {
            return $this->revoke((int) $revokeId);
        }

        $name = $this->argument('name');
        if (! $name) {
            $this->error('Provide a name, e.g. php artisan mcp:token "claude-desktop"');
            return self::FAILURE;
        }

        [, $plain] = McpToken::mint($name);

        $this->newLine();
        $this->info("MCP token created for \"{$name}\".");
        $this->line('Copy it now — it is stored hashed and cannot be shown again:');
        $this->newLine();
        $this->line("  {$plain}");
        $this->newLine();

        return self::SUCCESS;
    }

    private function listTokens(): int
    {
        $tokens = McpToken::orderBy('id')->get();

        if ($tokens->isEmpty()) {
            $this->info('No MCP tokens yet. Create one with: php artisan mcp:token "claude-desktop"');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Last used', 'Status'],
            $tokens->map(fn (McpToken $t) => [
                $t->id,
                $t->name,
                $t->last_used_at?->toDateTimeString() ?? 'never',
                $t->revoked_at ? 'revoked' : 'active',
            ])->all()
        );

        return self::SUCCESS;
    }

    private function revoke(int $id): int
    {
        $token = McpToken::find($id);
        if (! $token) {
            $this->error("No MCP token with id {$id}.");
            return self::FAILURE;
        }

        if (! $token->isActive()) {
            $this->info("Token {$id} (\"{$token->name}\") is already revoked.");
            return self::SUCCESS;
        }

        $token->revoke();
        $this->info("Revoked token {$id} (\"{$token->name}\").");

        return self::SUCCESS;
    }
}
