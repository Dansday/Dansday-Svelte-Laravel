<?php

use Illuminate\Support\Facades\Route;

Route::get('/main/general', [App\Http\Controllers\Api\GeneralController::class, 'index']);
Route::get('/main/home', [App\Http\Controllers\Api\HomeController::class, 'index']);
Route::get('/main/section', [App\Http\Controllers\Api\SectionController::class, 'index']);
Route::get('/main/abouts', [App\Http\Controllers\Api\AboutsController::class, 'index']);
Route::get('/main/articles', [App\Http\Controllers\Api\ArticleController::class, 'index']);
Route::get('/main/article/{slug}', [App\Http\Controllers\Api\ArticleController::class, 'show']);
Route::get('/main/projects', [App\Http\Controllers\Api\ProjectController::class, 'index']);
Route::get('/main/project/{id}', [App\Http\Controllers\Api\ProjectController::class, 'show']);
