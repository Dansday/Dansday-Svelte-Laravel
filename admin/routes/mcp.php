<?php

use App\Http\Controllers\McpServerController;
use App\Http\Controllers\McpUploadController;
use Illuminate\Support\Facades\Route;

Route::post('/mcp', [McpServerController::class, 'handle'])
    ->middleware(['throttle:120,1', 'mcp.auth'])
    ->name('mcp');

Route::post('/mcp/uploads', [McpUploadController::class, 'store'])
    ->middleware(['throttle:30,1', 'mcp.auth'])
    ->name('mcp.uploads');
