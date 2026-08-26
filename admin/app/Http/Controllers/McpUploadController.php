<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class McpUploadController extends Controller
{
    private const KINDS = [
        'article' => ['folder' => 'img/articles', 'prefix' => 'post_image'],
        'project' => ['folder' => 'img/projects', 'prefix' => 'project_image'],
        'inline'  => ['folder' => 'img/temp', 'prefix' => 'img'],
    ];

    private const MAX_KB = 8192;

    public function store(Request $request): JsonResponse
    {
        $kind = (string) $request->input('kind', 'article');

        if (! array_key_exists($kind, self::KINDS)) {
            return response()->json([
                'error' => 'Unknown kind "'.$kind.'". Use one of: '.implode(', ', array_keys(self::KINDS)).'.',
            ], 422);
        }

        $validator = validator($request->all(), [
            'file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:'.self::MAX_KB],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first('file')], 422);
        }

        $file = $request->file('file');
        $ext = strtolower((string) $file->guessExtension());

        if (! in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            return response()->json(['error' => 'Invalid or unsupported image type.'], 422);
        }

        $target = self::KINDS[$kind];
        $name = $target['prefix'].'_'.Str::random(24).'.'.$ext;
        $stored = $file->storeAs($target['folder'], $name, 'uploads');

        if ($stored === false) {
            return response()->json(['error' => 'Could not write the file to the uploads disk.'], 500);
        }

        return response()->json([
            'path'  => 'uploads/'.$stored,
            'url'   => Storage::disk('uploads')->url($stored),
            'kind'  => $kind,
            'bytes' => $file->getSize(),
        ]);
    }
}
