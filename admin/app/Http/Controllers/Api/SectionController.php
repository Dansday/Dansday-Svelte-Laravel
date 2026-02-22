<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;

class SectionController extends Controller
{
    public function index()
    {
        $section = Section::find(1);
        return response()->json($section ?? []);
    }
}
