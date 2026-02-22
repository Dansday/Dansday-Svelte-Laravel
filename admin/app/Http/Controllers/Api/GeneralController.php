<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\General;

class GeneralController extends Controller
{
    public function index()
    {
        $general = General::find(1);
        return response()->json($general ?? []);
    }
}
