<?php

namespace App\Http\Controllers;

use App\Services\EmbeddingService;
use Illuminate\Http\JsonResponse;

class EmbeddingController extends Controller
{
    public function embedAll(): JsonResponse
    {
        set_time_limit(300);
        $result = EmbeddingService::embedAll();
        return response()->json($result);
    }
}
