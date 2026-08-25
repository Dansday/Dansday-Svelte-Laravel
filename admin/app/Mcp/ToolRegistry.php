<?php

namespace App\Mcp;

use App\Mcp\Tools\AboutTools;
use App\Mcp\Tools\ArticleTools;
use App\Mcp\Tools\CategoryTools;
use App\Mcp\Tools\PageTools;
use App\Mcp\Tools\ProjectTools;
use App\Exceptions\ContentWriteException;

class ToolRegistry
{
    /** @var array<string, array>|null */
    private static ?array $tools = null;

    /**
     * All tool definitions, keyed by tool name.
     */
    public static function all(): array
    {
        if (self::$tools === null) {
            $definitions = array_merge(
                ArticleTools::definitions(),
                ProjectTools::definitions(),
                CategoryTools::definitions(),
                AboutTools::definitions(),
                PageTools::definitions(),
            );

            self::$tools = [];
            foreach ($definitions as $definition) {
                self::$tools[$definition['name']] = $definition;
            }
        }

        return self::$tools;
    }

    /**
     * Tool list in the shape the MCP `tools/list` response expects, i.e. without
     * the PHP handler.
     */
    public static function schema(): array
    {
        return array_values(array_map(fn (array $t) => [
            'name'        => $t['name'],
            'description' => $t['description'],
            'inputSchema' => $t['inputSchema'],
        ], self::all()));
    }

    public static function has(string $name): bool
    {
        return isset(self::all()[$name]);
    }

    /**
     * Invoke a tool. Missing required arguments are reported rather than left to
     * fail as a PHP error deep in a handler.
     */
    public static function call(string $name, array $arguments): array
    {
        $tool = self::all()[$name] ?? null;
        if (! $tool) {
            throw new ContentWriteException("Unknown tool \"{$name}\".");
        }

        foreach ($tool['inputSchema']['required'] ?? [] as $required) {
            if (! array_key_exists($required, $arguments) || $arguments[$required] === null || $arguments[$required] === '') {
                throw new ContentWriteException("Tool \"{$name}\" requires the \"{$required}\" argument.");
            }
        }

        $result = ($tool['handler'])($arguments);

        return is_array($result) ? $result : ['result' => $result];
    }
}
