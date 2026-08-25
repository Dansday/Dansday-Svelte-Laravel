<?php

use App\Http\Controllers\McpController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MCP routes
|--------------------------------------------------------------------------
|
| Model Context Protocol endpoint. Deliberately outside the "web" middleware
| group: it is stateless (bearer token, no session or CSRF) and must not pass
| through the XSS middleware, which strips tags and would mangle the HTML
| bodies of articles and projects on the way in.
|
*/

Route::post('/mcp', [McpController::class, 'handle'])
    ->middleware(['throttle:120,1', 'mcp.auth'])
    ->name('mcp');
