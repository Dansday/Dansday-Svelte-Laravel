<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists(__DIR__.'/storage/framework/maintenance.php')) {
    require __DIR__.'/storage/framework/maintenance.php';
}

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
