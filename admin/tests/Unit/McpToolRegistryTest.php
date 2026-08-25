<?php

namespace Tests\Unit;

use App\Exceptions\ContentWriteException;
use App\Mcp\Args;
use App\Mcp\ToolRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Structural checks on the MCP tool surface. These need no database: they
 * validate that the registry wiring, names and JSON schemas are coherent, which
 * is what an MCP client reads from tools/list.
 */
class McpToolRegistryTest extends TestCase
{
    public function test_every_expected_tool_is_registered(): void
    {
        $names = array_column(ToolRegistry::schema(), 'name');

        $expected = [
            // articles
            'list_articles', 'get_article', 'create_article', 'update_article', 'delete_article',
            // projects
            'list_projects', 'get_project', 'create_project', 'update_project', 'delete_project',
            // categories
            'list_article_categories', 'create_article_category', 'update_article_category', 'delete_article_category',
            'list_project_categories', 'create_project_category', 'update_project_category', 'delete_project_category',
            // abouts
            'list_abouts', 'create_skill', 'update_skill', 'create_experience', 'update_experience',
            'create_service', 'update_service', 'create_testimonial', 'update_testimonial',
            'delete_about', 'reorder_about',
            // pages
            'get_home_page', 'update_home_page', 'get_sections', 'update_sections',
        ];

        foreach ($expected as $tool) {
            $this->assertContains($tool, $names, "Tool \"{$tool}\" is missing from the registry.");
        }
    }

    public function test_tool_names_are_unique(): void
    {
        $names = array_column(ToolRegistry::schema(), 'name');

        $this->assertSame(
            array_values(array_unique($names)),
            $names,
            'Duplicate tool names would silently shadow each other in the registry.'
        );
    }

    public function test_every_tool_has_a_well_formed_schema(): void
    {
        foreach (ToolRegistry::schema() as $tool) {
            $name = $tool['name'];

            $this->assertMatchesRegularExpression('/^[a-z][a-z0-9_]*$/', $name, "Tool name \"{$name}\" is not snake_case.");
            $this->assertNotEmpty($tool['description'], "Tool \"{$name}\" has no description.");
            $this->assertSame('object', $tool['inputSchema']['type'] ?? null, "Tool \"{$name}\" must take an object.");
            $this->assertArrayHasKey('properties', $tool['inputSchema'], "Tool \"{$name}\" declares no properties.");

            foreach ($tool['inputSchema']['properties'] as $property => $spec) {
                $this->assertArrayHasKey('type', $spec, "Property \"{$property}\" of \"{$name}\" has no type.");
                $this->assertArrayHasKey('description', $spec, "Property \"{$property}\" of \"{$name}\" has no description.");
            }
        }
    }

    public function test_required_arguments_are_declared_as_properties(): void
    {
        foreach (ToolRegistry::schema() as $tool) {
            foreach ($tool['inputSchema']['required'] ?? [] as $required) {
                $this->assertArrayHasKey(
                    $required,
                    $tool['inputSchema']['properties'],
                    "Tool \"{$tool['name']}\" requires \"{$required}\" but never declares it."
                );
            }
        }
    }

    public function test_write_tools_require_an_identifier(): void
    {
        $needsId = [
            'get_article', 'update_article', 'delete_article',
            'get_project', 'update_project', 'delete_project',
            'update_article_category', 'delete_article_category',
            'update_project_category', 'delete_project_category',
            'update_skill', 'update_experience', 'update_service', 'update_testimonial',
            'delete_about', 'reorder_about',
        ];

        $tools = collect(ToolRegistry::schema())->keyBy('name');

        foreach ($needsId as $name) {
            $this->assertContains(
                'id',
                $tools[$name]['inputSchema']['required'] ?? [],
                "Tool \"{$name}\" must require an id so it cannot act on an unspecified row."
            );
        }
    }

    public function test_unknown_tool_is_rejected(): void
    {
        $this->expectException(ContentWriteException::class);
        ToolRegistry::call('no_such_tool', []);
    }

    public function test_missing_required_argument_is_rejected_before_the_handler_runs(): void
    {
        $this->expectException(ContentWriteException::class);
        $this->expectExceptionMessageMatches('/requires the "id" argument/');

        // Reaching the handler would hit the database; the guard must fire first.
        ToolRegistry::call('delete_article', []);
    }

    public function test_registry_reports_tool_presence(): void
    {
        $this->assertTrue(ToolRegistry::has('create_article'));
        $this->assertFalse(ToolRegistry::has('drop_database'));
    }

    public function test_pagination_arguments_are_clamped(): void
    {
        $this->assertSame(25, Args::limit([]));
        $this->assertSame(100, Args::limit(['limit' => 5000]), 'A huge limit must be capped.');
        $this->assertSame(1, Args::limit(['limit' => 0]), 'A zero limit must floor to 1.');
        $this->assertSame(10, Args::limit(['limit' => '10']), 'Numeric strings from LLM clients must be coerced.');

        $this->assertSame(0, Args::offset([]));
        $this->assertSame(0, Args::offset(['offset' => -5]), 'A negative offset must floor to 0.');
        $this->assertSame(30, Args::offset(['offset' => '30']));
    }
}
