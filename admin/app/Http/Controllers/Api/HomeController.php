<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Home;

class HomeController extends Controller
{
    public function index()
    {
        $home = Home::find(1);
        return response()->json($home ?? ['title' => null, 'description' => null]);
    }
}
