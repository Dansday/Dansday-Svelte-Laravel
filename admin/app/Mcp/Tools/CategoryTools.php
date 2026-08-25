<?php

namespace App\Mcp\Tools;

use App\Services\ContentWriteService;

class CategoryTools
{
    public static function definitions(): array
    {
        $tools = [];

        foreach (['article', 'project'] as $kind) {
            $plural = $kind === 'article' ? 'articles' : 'projects';

            $tools[] = [
                'name' => "list_{$kind}_categories",
                'description' => "List all {$kind} categories with how many {$plural} use each. Call this before creating {$plural} so you can pass a category that exists.",
                'inputSchema' => ['type' => 'object', 'properties' => []],
                'handler' => fn () => ['categories' => ContentWriteService::listCategories($kind)],
            ];

            $tools[] = [
                'name' => "create_{$kind}_category",
                'description' => "Create a new {$kind} category. Names are unique, case-insensitively.",
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'name'       => ['type' => 'string', 'description' => 'Category name, max 55 characters.'],
                        'created_at' => ['type' => 'string', 'description' => 'Optional creation date, YYYY-MM-DD.'],
                    ],
                    'required' => ['name'],
                ],
                'handler' => fn (array $a) => ContentWriteService::createCategory($kind, $a),
            ];

            $tools[] = [
                'name' => "update_{$kind}_category",
                'description' => "Rename a {$kind} category. Every {$kind} in it keeps its assignment.",
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'id'   => ['type' => 'integer', 'description' => 'Category id to rename.'],
                        'name' => ['type' => 'string', 'description' => 'New name, max 55 characters.'],
                    ],
                    'required' => ['id', 'name'],
                ],
                'handler' => fn (array $a) => ContentWriteService::updateCategory($kind, (int) $a['id'], $a),
            ];

            $tools[] = [
                'name' => "delete_{$kind}_category",
                'description' => "Delete a {$kind} category. Refused while any {$plural} still belong to it — move or delete those first.",
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer', 'description' => 'Category id to delete.'],
                    ],
                    'required' => ['id'],
                ],
                'handler' => fn (array $a) => ContentWriteService::deleteCategory($kind, (int) $a['id']),
            ];
        }

        return $tools;
    }
}
