<?php

namespace App\Mcp\Tools;

use App\Services\ContentWriteService;

class AboutTools
{
    public static function definitions(): array
    {
        return array_merge(
            self::listTools(),
            self::skillTools(),
            self::experienceTools(),
            self::serviceTools(),
            self::testimonialTools(),
            self::sharedTools(),
        );
    }

    private static function listTools(): array
    {
        return [[
            'name' => 'list_abouts',
            'description' => 'List the about-page content: skills, experiences, services and testimonials, each in display order with their ids. Call this before updating, reordering or deleting any about item.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'kind' => [
                        'type' => 'string',
                        'enum' => ['skill', 'experience', 'service', 'testimonial'],
                        'description' => 'Limit to one kind. Omit to return all four.',
                    ],
                ],
            ],
            'handler' => function (array $a) {
                $kinds = ! empty($a['kind']) ? [$a['kind']] : ['skill', 'experience', 'service', 'testimonial'];
                $out = [];
                foreach ($kinds as $kind) {
                    $out[$kind . 's'] = ContentWriteService::listAbout($kind);
                }
                return $out;
            },
        ]];
    }

    private static function skillTools(): array
    {
        return [
            [
                'name' => 'create_skill',
                'description' => 'Add a skill to the about page. Skills are grouped into design and development columns and appended to the end of their group.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => ['type' => 'string', 'description' => 'Skill name, max 55 characters.'],
                        'type'  => ['type' => 'string', 'enum' => ['design', 'development'], 'description' => 'Which column the skill belongs in.'],
                    ],
                    'required' => ['title', 'type'],
                ],
                'handler' => fn (array $a) => ContentWriteService::createSkill($a),
            ],
            [
                'name' => 'update_skill',
                'description' => 'Update a skill. Only the fields you pass are changed.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'id'    => ['type' => 'integer', 'description' => 'Skill id.'],
                        'title' => ['type' => 'string', 'description' => 'New name, max 55 characters.'],
                        'type'  => ['type' => 'string', 'enum' => ['design', 'development'], 'description' => 'Move to this column.'],
                        'order' => ['type' => 'integer', 'description' => 'Position within its column. Prefer reorder_about for this.'],
                    ],
                    'required' => ['id'],
                ],
                'handler' => fn (array $a) => ContentWriteService::updateSkill((int) $a['id'], $a),
            ],
        ];
    }

    private static function experienceTools(): array
    {
        return [
            [
                'name' => 'create_experience',
                'description' => 'Add an education or employment entry to the about page. Appended to the end of its group.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'title'       => ['type' => 'string', 'description' => 'Role or qualification, max 55 characters.'],
                        'period'      => ['type' => 'string', 'description' => 'Free-text period, e.g. "2021 - Present", max 55 characters.'],
                        'description' => ['type' => 'string', 'description' => 'What the role or study involved, max 255 characters.'],
                        'type'        => ['type' => 'string', 'enum' => ['education', 'employment'], 'description' => 'Which group the entry belongs in.'],
                    ],
                    'required' => ['title', 'period', 'description', 'type'],
                ],
                'handler' => fn (array $a) => ContentWriteService::createExperience($a),
            ],
            [
                'name' => 'update_experience',
                'description' => 'Update an education or employment entry. Only the fields you pass are changed.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'id'          => ['type' => 'integer', 'description' => 'Experience id.'],
                        'title'       => ['type' => 'string', 'description' => 'New title, max 55 characters.'],
                        'period'      => ['type' => 'string', 'description' => 'New period, max 55 characters.'],
                        'description' => ['type' => 'string', 'description' => 'New description, max 255 characters.'],
                        'type'        => ['type' => 'string', 'enum' => ['education', 'employment'], 'description' => 'Move to this group.'],
                        'order'       => ['type' => 'integer', 'description' => 'Position within its group. Prefer reorder_about for this.'],
                    ],
                    'required' => ['id'],
                ],
                'handler' => fn (array $a) => ContentWriteService::updateExperience((int) $a['id'], $a),
            ],
        ];
    }

    private static function serviceTools(): array
    {
        return [
            [
                'name' => 'create_service',
                'description' => 'Add a service offering to the about page.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'title'       => ['type' => 'string', 'description' => 'Service name, max 55 characters.'],
                        'description' => ['type' => 'string', 'description' => 'Short pitch, max 255 characters.'],
                        'info'        => ['type' => 'string', 'description' => 'Longer detail, max 510 characters.'],
                    ],
                    'required' => ['title', 'description', 'info'],
                ],
                'handler' => fn (array $a) => ContentWriteService::createService($a),
            ],
            [
                'name' => 'update_service',
                'description' => 'Update a service offering. Only the fields you pass are changed.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'id'          => ['type' => 'integer', 'description' => 'Service id.'],
                        'title'       => ['type' => 'string', 'description' => 'New name, max 55 characters.'],
                        'description' => ['type' => 'string', 'description' => 'New pitch, max 255 characters.'],
                        'info'        => ['type' => 'string', 'description' => 'New detail, max 510 characters.'],
                        'order'       => ['type' => 'integer', 'description' => 'Display position. Prefer reorder_about for this.'],
                    ],
                    'required' => ['id'],
                ],
                'handler' => fn (array $a) => ContentWriteService::updateService((int) $a['id'], $a),
            ],
        ];
    }

    private static function testimonialTools(): array
    {
        return [
            [
                'name' => 'create_testimonial',
                'description' => 'Add a testimonial to the about page.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'name'    => ['type' => 'string', 'description' => 'Who gave the testimonial, max 55 characters.'],
                        'company' => ['type' => 'string', 'description' => 'Their company or role, max 55 characters.'],
                        'text'    => ['type' => 'string', 'description' => 'What they said, max 255 characters.'],
                    ],
                    'required' => ['name', 'company', 'text'],
                ],
                'handler' => fn (array $a) => ContentWriteService::createTestimonial($a),
            ],
            [
                'name' => 'update_testimonial',
                'description' => 'Update a testimonial. Only the fields you pass are changed.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'id'      => ['type' => 'integer', 'description' => 'Testimonial id.'],
                        'name'    => ['type' => 'string', 'description' => 'New name, max 55 characters.'],
                        'company' => ['type' => 'string', 'description' => 'New company or role, max 55 characters.'],
                        'text'    => ['type' => 'string', 'description' => 'New quote, max 255 characters.'],
                        'order'   => ['type' => 'integer', 'description' => 'Display position. Prefer reorder_about for this.'],
                    ],
                    'required' => ['id'],
                ],
                'handler' => fn (array $a) => ContentWriteService::updateTestimonial((int) $a['id'], $a),
            ],
        ];
    }

    private static function sharedTools(): array
    {
        return [
            [
                'name' => 'delete_about',
                'description' => 'Permanently delete one about item — a skill, experience, service or testimonial. Remaining items are renumbered to close the gap. Cannot be undone.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'kind' => [
                            'type' => 'string',
                            'enum' => ['skill', 'experience', 'service', 'testimonial'],
                            'description' => 'Which kind of about item to delete.',
                        ],
                        'id' => ['type' => 'integer', 'description' => 'Id of the item to delete.'],
                    ],
                    'required' => ['kind', 'id'],
                ],
                'handler' => fn (array $a) => ContentWriteService::deleteAbout($a['kind'], (int) $a['id']),
            ],
            [
                'name' => 'reorder_about',
                'description' => 'Move an about item to a specific position in its list (1 is first). Siblings shift to keep the ordering contiguous. For skills and experiences the position is within the item\'s own type.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'kind' => [
                            'type' => 'string',
                            'enum' => ['skill', 'experience', 'service', 'testimonial'],
                            'description' => 'Which kind of about item to move.',
                        ],
                        'id'       => ['type' => 'integer', 'description' => 'Id of the item to move.'],
                        'position' => ['type' => 'integer', 'description' => 'Target position, 1-based.'],
                    ],
                    'required' => ['kind', 'id', 'position'],
                ],
                'handler' => fn (array $a) => ContentWriteService::reorderAbout($a['kind'], (int) $a['id'], (int) $a['position']),
            ],
        ];
    }
}
