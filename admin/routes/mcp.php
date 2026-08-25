<?php

use App\Http\Controllers\McpServerController;
use Illuminate\Support\Facades\Route;

Route::post('/mcp', [McpServerController::class, 'handle'])
    ->middleware(['throttle:120,1', 'mcp.auth'])
    ->name('mcp');
