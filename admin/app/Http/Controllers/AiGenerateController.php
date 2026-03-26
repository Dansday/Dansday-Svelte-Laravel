<?php

namespace App\Http\Controllers;

use App\Services\AiGenerateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiGenerateController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'topic' => ['nullable', 'string', 'max:15000'],
            'type' => ['nullable', 'string', 'in:article,project'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $result = AiGenerateService::generate(
            $request->input('type', 'article'),
            $request->input('topic', '')
        );

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 502);
        }

        return response()->json(['text' => $result['text']]);
    }
}
