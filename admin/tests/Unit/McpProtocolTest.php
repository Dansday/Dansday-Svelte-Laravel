<?php

namespace Tests\Unit;

use App\Http\Controllers\McpServerController;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * JSON-RPC 2.0 / MCP transport behaviour. The controller is exercised directly
 * so these run without a database: none of the methods covered here reach a
 * tool handler that queries.
 */
class McpProtocolTest extends TestCase
{
    private function rpc(array|string $body): array
    {
        $request = Request::create(
            '/mcp',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            is_string($body) ? $body : json_encode($body)
        );

        $response = (new McpServerController())->handle($request);

        return [$response->getStatusCode(), json_decode($response->getContent(), true), $response->getContent()];
    }

    public function test_initialize_reports_tools_capability_and_server_info(): void
    {
        [$status, $body] = $this->rpc([
            'jsonrpc' => '2.0',
            'id'      => 1,
            'method'  => 'initialize',
            'params'  => ['protocolVersion' => '2025-06-18'],
        ]);

        $this->assertSame(200, $status);
        $this->assertSame('2.0', $body['jsonrpc']);
        $this->assertSame('2025-06-18', $body['result']['protocolVersion']);
        $this->assertArrayHasKey('tools', $body['result']['capabilities']);
        $this->assertNotEmpty($body['result']['serverInfo']['name']);
        $this->assertNotEmpty($body['result']['instructions']);
    }

    public function test_initialize_falls_back_when_the_client_asks_for_an_unknown_protocol(): void
    {
        [, $body] = $this->rpc([
            'jsonrpc' => '2.0',
            'id'      => 1,
            'method'  => 'initialize',
            'params'  => ['protocolVersion' => '1999-01-01'],
        ]);

        $this->assertSame('2025-06-18', $body['result']['protocolVersion']);
    }

    public function test_tools_list_returns_the_tool_surface_without_leaking_handlers(): void
    {
        [$status, $body] = $this->rpc(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list']);

        $this->assertSame(200, $status);
        $this->assertNotEmpty($body['result']['tools']);

        foreach ($body['result']['tools'] as $tool) {
            $this->assertArrayNotHasKey('handler', $tool, 'A PHP closure must never be serialised to the client.');
            $this->assertArrayHasKey('inputSchema', $tool);
        }
    }

    public function test_ping_is_answered(): void
    {
        [$status, $body] = $this->rpc(['jsonrpc' => '2.0', 'id' => 3, 'method' => 'ping']);

        $this->assertSame(200, $status);
        $this->assertArrayHasKey('result', $body);
    }

    public function test_unsupported_method_returns_method_not_found(): void
    {
        [, $body] = $this->rpc(['jsonrpc' => '2.0', 'id' => 4, 'method' => 'bogus/method']);

        $this->assertSame(-32601, $body['error']['code']);
    }

    public function test_notifications_are_acknowledged_with_no_body(): void
    {
        [$status, , $raw] = $this->rpc(['jsonrpc' => '2.0', 'method' => 'notifications/initialized']);

        $this->assertSame(202, $status);
        $this->assertSame('', $raw, 'A JSON-RPC notification must not receive a response body.');
    }

    public function test_unparseable_body_returns_parse_error(): void
    {
        [, $body] = $this->rpc([]);

        $this->assertSame(-32700, $body['error']['code']);
    }

    public function test_batched_requests_each_get_a_response(): void
    {
        [$status, $body] = $this->rpc([
            ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping'],
            ['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list'],
        ]);

        $this->assertSame(200, $status);
        $this->assertCount(2, $body);
    }

    public function test_a_batch_of_only_notifications_returns_no_content(): void
    {
        [$status] = $this->rpc([
            ['jsonrpc' => '2.0', 'method' => 'notifications/initialized'],
        ]);

        $this->assertSame(202, $status);
    }

    public function test_unknown_tool_is_a_tool_error_not_a_transport_error(): void
    {
        [$status, $body] = $this->rpc([
            'jsonrpc' => '2.0',
            'id'      => 9,
            'method'  => 'tools/call',
            'params'  => ['name' => 'nope', 'arguments' => []],
        ]);

        // The model should be able to read the failure and retry, so this is a
        // successful RPC carrying isError rather than a JSON-RPC error.
        $this->assertSame(200, $status);
        $this->assertTrue($body['result']['isError']);
        $this->assertStringContainsString('tools/list', $body['result']['content'][0]['text']);
    }

    public function test_missing_required_argument_is_reported_before_the_handler_runs(): void
    {
        [, $body] = $this->rpc([
            'jsonrpc' => '2.0',
            'id'      => 10,
            'method'  => 'tools/call',
            'params'  => ['name' => 'delete_article', 'arguments' => []],
        ]);

        $this->assertTrue($body['result']['isError']);
        $this->assertStringContainsString('"id"', $body['result']['content'][0]['text']);
    }

    public function test_tool_call_without_a_name_is_rejected(): void
    {
        [, $body] = $this->rpc([
            'jsonrpc' => '2.0',
            'id'      => 11,
            'method'  => 'tools/call',
            'params'  => [],
        ]);

        $this->assertTrue($body['result']['isError']);
    }
}
