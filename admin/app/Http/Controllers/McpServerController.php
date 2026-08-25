<?php

namespace App\Http\Controllers;

use App\Exceptions\ContentWriteException;
use App\Mcp\ToolRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class McpServerController extends Controller
{
    private const SUPPORTED_PROTOCOL_VERSIONS = ['2025-06-18', '2025-03-26', '2024-11-05'];

    private const LATEST_PROTOCOL_VERSION = '2025-06-18';

    public function handle(Request $request): Response|JsonResponse
    {
        $payload = $request->json()->all();

        if (! is_array($payload) || empty($payload)) {
            return $this->error(null, -32700, 'Parse error: expected a JSON-RPC request object.');
        }

        if (array_is_list($payload)) {
            $responses = [];
            foreach ($payload as $item) {
                $response = is_array($item)
                    ? $this->handleRpc($item)
                    : ['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32600, 'message' => 'Invalid request.']];

                if ($response !== null) {
                    $responses[] = $response;
                }
            }

            return empty($responses)
                ? response()->noContent(202)
                : response()->json($responses);
        }

        $response = $this->handleRpc($payload);

        return $response === null
            ? response()->noContent(202)
            : response()->json($response);
    }

    private function handleRpc(array $request): ?array
    {
        $id     = $request['id'] ?? null;
        $method = (string) ($request['method'] ?? '');
        $params = $request['params'] ?? [];
        $params = is_array($params) ? $params : [];

        $isNotification = ! array_key_exists('id', $request) || $id === null;

        if ($isNotification) {
            return null;
        }

        return match ($method) {
            'initialize'      => $this->result($id, $this->initialize($params)),
            'ping'            => $this->result($id, new \stdClass()),
            'tools/list'      => $this->result($id, ['tools' => ToolRegistry::schema()]),
            'tools/call'      => $this->result($id, $this->callTool($params)),
            'resources/list'  => $this->result($id, ['resources' => []]),
            'prompts/list'    => $this->result($id, ['prompts' => []]),
            default           => $this->errorBody($id, -32601, "Method \"{$method}\" is not supported."),
        };
    }

    private function initialize(array $params): array
    {
        $requested = (string) ($params['protocolVersion'] ?? '');
        $version = in_array($requested, self::SUPPORTED_PROTOCOL_VERSIONS, true)
            ? $requested
            : self::LATEST_PROTOCOL_VERSION;

        return [
            'protocolVersion' => $version,
            'capabilities'    => [
                'tools' => new \stdClass(),
            ],
            'serverInfo' => [
                'name'    => config('app.name', 'dansday') . '-admin',
                'title'   => 'Dansday content',
                'version' => '1.0.0',
            ],
            'instructions' => implode(' ', [
                'Manage the content of this personal site: articles, projects, their categories,',
                'and the about page (skills, experience, services, testimonials).',
                'Call a list_* tool first to discover ids and existing categories before writing.',
                'Article and project bodies are HTML. Cover images are optional.',
                'created_at can be set explicitly to backdate a post.',
                'Deletes are permanent, so confirm the target with a get_* tool first.',
            ]),
        ];
    }

    private function callTool(array $params): array
    {
        $name = (string) ($params['name'] ?? '');
        $arguments = $params['arguments'] ?? [];
        $arguments = is_array($arguments) ? $arguments : [];

        if ($name === '') {
            return $this->toolError('No tool name given.');
        }

        if (! ToolRegistry::has($name)) {
            return $this->toolError("Unknown tool \"{$name}\". Call tools/list to see what is available.");
        }

        try {
            $result = ToolRegistry::call($name, $arguments);

            return [
                'content' => [[
                    'type' => 'text',
                    'text' => $this->encode($result),
                ]],
                'structuredContent' => $result,
                'isError' => false,
            ];
        } catch (ContentWriteException $e) {
            return $this->toolError($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('MCP tool failed', [
                'tool'    => $name,
                'message' => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
            ]);

            return $this->toolError("The \"{$name}\" tool failed unexpectedly: " . $e->getMessage());
        }
    }

    private function toolError(string $message): array
    {
        return [
            'content' => [['type' => 'text', 'text' => $message]],
            'isError' => true,
        ];
    }

    private function encode(mixed $value): string
    {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function result(mixed $id, mixed $result): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    }

    private function errorBody(mixed $id, int $code, string $message): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
    }

    private function error(mixed $id, int $code, string $message, int $status = 400): JsonResponse
    {
        return response()->json($this->errorBody($id, $code, $message), $status);
    }
}
