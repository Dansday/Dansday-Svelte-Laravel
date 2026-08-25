<?php

namespace App\Mcp\Tools;

use App\Services\ContentWriteService;

class PageTools
{
    public static function definitions(): array
    {
        $sectionProperties = [];
        foreach (ContentWriteService::SECTION_FLAGS as $flag) {
            $sectionProperties[$flag] = [
                'type' => 'boolean',
                'description' => "Show or hide the " . str_replace(['_enable', '_'], ['', ' '], $flag) . " section.",
            ];
        }
        foreach (ContentWriteService::SECTION_ORDERS as $order) {
            $sectionProperties[$order] = [
                'type' => 'integer',
                'description' => "Display order of " . str_replace(['about_', '_order', '_'], ['', '', ' '], $order) . " within the about page.",
            ];
        }

        return [
            [
                'name' => 'get_home_page',
                'description' => 'Get the home page headline and intro text.',
                'inputSchema' => ['type' => 'object', 'properties' => []],
                'handler' => fn () => ContentWriteService::showHome(),
            ],
            [
                'name' => 'update_home_page',
                'description' => 'Update the home page headline and/or intro text. Only the fields you pass are changed.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'title'       => ['type' => 'string', 'description' => 'Headline, max 75 characters.'],
                        'description' => ['type' => 'string', 'description' => 'Intro text, max 5000 characters.'],
                    ],
                ],
                'handler' => fn (array $a) => ContentWriteService::updateHome($a),
            ],
            [
                'name' => 'get_sections',
                'description' => 'Get which sections of the public site are enabled, and the ordering of the about-page blocks. Note that disabled sections are excluded from AI content recall.',
                'inputSchema' => ['type' => 'object', 'properties' => []],
                'handler' => fn () => ContentWriteService::showSections(),
            ],
            [
                'name' => 'update_sections',
                'description' => 'Show or hide sections of the public site, and reorder the about-page blocks. Only the fields you pass are changed.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => $sectionProperties,
                ],
                'handler' => fn (array $a) => ContentWriteService::updateSections($a),
            ],
        ];
    }
}
