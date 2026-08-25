<?php

namespace App\Mcp\Tools;

use App\Mcp\Args;
use App\Services\ContentWriteService;
use Illuminate\Support\Facades\DB;

class ProjectTools
{
    public static function definitions(): array
    {
        return [
            [
                'name' => 'list_projects',
                'description' => 'List projects, newest first. Use to find a project id before updating or deleting. Optional keyword, category and date filters.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'keyword'   => ['type' => 'string', 'description' => 'Match against title and short description.'],
                        'category'  => ['type' => 'string', 'description' => 'Filter by category name.'],
                        'startDate' => ['type' => 'string', 'description' => 'Only projects created on/after this date (YYYY-MM-DD).'],
                        'endDate'   => ['type' => 'string', 'description' => 'Only projects created on/before this date (YYYY-MM-DD).'],
                        'includeDisabled' => ['type' => 'boolean', 'description' => 'Include hidden projects. Default true.'],
                        'limit'     => ['type' => 'integer', 'description' => 'Max rows to return (1-100). Default 25.'],
                        'offset'    => ['type' => 'integer', 'description' => 'Rows to skip, for paging. Default 0.'],
                    ],
                ],
                'handler' => fn (array $a) => self::list($a),
            ],
            [
                'name' => 'get_project',
                'description' => 'Get one project in full, including its HTML body. Use before editing so you can preserve existing content.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer', 'description' => 'Project id.'],
                    ],
                    'required' => ['id'],
                ],
                'handler' => fn (array $a) => ContentWriteService::showProject((int) $a['id']),
            ],
            [
                'name' => 'create_project',
                'description' => 'Create a project. The body is HTML. An image is optional. A category is required — call list_project_categories first if you do not know which exist. created_at may be backdated.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'title'       => ['type' => 'string', 'description' => 'Project title, max 55 characters.'],
                        'description' => ['type' => 'string', 'description' => 'Project write-up as HTML.'],
                        'short_desc'  => ['type' => 'string', 'description' => 'Summary shown on project cards, max 110 characters.'],
                        'category'    => ['type' => 'string', 'description' => 'Category name. Either this or category_id is required.'],
                        'category_id' => ['type' => 'integer', 'description' => 'Category id. Takes precedence over category.'],
                        'image'       => ['type' => 'string', 'description' => 'Optional cover image: an absolute URL, or a path under uploads/img/. Omit for no image.'],
                        'enable'      => ['type' => 'boolean', 'description' => 'Show on the site immediately. Default true.'],
                        'created_at'  => ['type' => 'string', 'description' => 'Project date as an absolute value: YYYY-MM-DD or YYYY-MM-DD HH:MM:SS. Relative values like "today" are rejected. Defaults to now.'],
                    ],
                    'required' => ['title', 'description'],
                ],
                'handler' => fn (array $a) => ContentWriteService::createProject($a),
            ],
            [
                'name' => 'update_project',
                'description' => 'Update a project. Only the fields you pass are changed; everything else is left alone. Can also change created_at.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'id'          => ['type' => 'integer', 'description' => 'Project id to update.'],
                        'title'       => ['type' => 'string', 'description' => 'New title, max 55 characters.'],
                        'description' => ['type' => 'string', 'description' => 'New HTML body. Replaces the existing body entirely.'],
                        'short_desc'  => ['type' => 'string', 'description' => 'New summary, max 110 characters.'],
                        'category'    => ['type' => 'string', 'description' => 'Move to this category, by name.'],
                        'category_id' => ['type' => 'integer', 'description' => 'Move to this category, by id.'],
                        'image'       => ['type' => 'string', 'description' => 'New cover image URL or uploads/img/ path. Pass an empty string to remove the image.'],
                        'enable'      => ['type' => 'boolean', 'description' => 'Show or hide on the site.'],
                        'created_at'  => ['type' => 'string', 'description' => 'New project date as an absolute value: YYYY-MM-DD or YYYY-MM-DD HH:MM:SS. Relative values like "today" are rejected.'],
                    ],
                    'required' => ['id'],
                ],
                'handler' => fn (array $a) => ContentWriteService::updateProject((int) $a['id'], $a),
            ],
            [
                'name' => 'delete_project',
                'description' => 'Permanently delete a project and its cover image. This cannot be undone — confirm the id with get_project first.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer', 'description' => 'Project id to delete.'],
                    ],
                    'required' => ['id'],
                ],
                'handler' => fn (array $a) => ContentWriteService::deleteProject((int) $a['id']),
            ],
        ];
    }

    private static function list(array $args): array
    {
        $limit  = Args::limit($args);
        $offset = Args::offset($args);

        $query = DB::table('projects as p')
            ->leftJoin('project_categories as c', 'c.id', '=', 'p.category_id')
            ->select('p.id', 'p.title', 'p.short_desc', 'p.image', 'p.enable', 'p.created_at', 'c.name as category');

        if ($keyword = trim($args['keyword'] ?? '')) {
            $like = '%' . $keyword . '%';
            $query->where(fn ($q) => $q->where('p.title', 'like', $like)->orWhere('p.short_desc', 'like', $like));
        }
        if ($category = trim($args['category'] ?? '')) {
            $query->whereRaw('LOWER(c.name) = ?', [mb_strtolower($category)]);
        }
        if (! empty($args['startDate'])) {
            $query->where('p.created_at', '>=', $args['startDate']);
        }
        if (! empty($args['endDate'])) {
            $query->where('p.created_at', '<=', $args['endDate'] . ' 23:59:59');
        }
        if (array_key_exists('includeDisabled', $args) && $args['includeDisabled'] === false) {
            $query->where('p.enable', 1);
        }

        $total = (clone $query)->count();

        $rows = $query->orderByDesc('p.created_at')->limit($limit)->offset($offset)->get()
            ->map(fn ($r) => [
                'id'         => $r->id,
                'title'      => $r->title,
                'short_desc' => $r->short_desc,
                'category'   => $r->category,
                'has_image'  => ! empty($r->image),
                'enable'     => (bool) $r->enable,
                'created_at' => (string) $r->created_at,
            ])->all();

        return ['total' => $total, 'returned' => count($rows), 'offset' => $offset, 'projects' => $rows];
    }
}
