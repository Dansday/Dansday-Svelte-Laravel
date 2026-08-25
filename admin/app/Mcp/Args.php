<?php

namespace App\Mcp;

/**
 * Small coercion helpers for tool arguments. LLM clients send numbers as
 * strings often enough that clamping here is cheaper than validating everywhere.
 */
class Args
{
    public static function limit(array $args, int $default = 25, int $max = 100): int
    {
        $limit = isset($args['limit']) ? (int) $args['limit'] : $default;

        return max(1, min($max, $limit));
    }

    public static function offset(array $args): int
    {
        return max(0, isset($args['offset']) ? (int) $args['offset'] : 0);
    }
}
