<?php

namespace App\Mcp;

use App\Mcp\Tools\AboutTools;
use App\Mcp\Tools\ArticleTools;
use App\Mcp\Tools\CategoryTools;
use App\Mcp\Tools\LinkedInTools;
use App\Mcp\Tools\PageTools;
use App\Mcp\Tools\ProjectTools;
use App\Exceptions\ContentWriteException;

class ToolRegistry
{
    private static ?array $tools = null;

    public static function all(): array
    {
        if (self::$tools === null) {
            $definitions = array_merge(
                ArticleTools::definitions(),
                ProjectTools::definitions(),
                CategoryTools::definitions(),
                AboutTools::definitions(),
                PageTools::definitions(),
                LinkedInTools::definitions(),
            );

            self::$tools = [];
            foreach ($definitions as $definition) {
                self::$tools[$definition['name']] = $definition;
            }
        }

        return self::$tools;
    }

    public static function schema(): array
    {
        return array_values(array_map(function (array $t) {
            $inputSchema = $t['inputSchema'];

            if (empty($inputSchema['properties'])) {
                $inputSchema['properties'] = new \stdClass();
            }

            return [
                'name'        => $t['name'],
                'description' => $t['description'],
                'inputSchema' => $inputSchema,
            ];
        }, self::all()));
    }

    public static function has(string $name): bool
    {
        return isset(self::all()[$name]);
    }

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
