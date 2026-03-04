<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SummernoteUploadController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'file'   => ['required', 'file', 'image', 'max:10240'],
            'folder' => ['required', 'string', 'in:uploads/img/temp'],
            'code'   => ['nullable', 'string', 'max:100'],
        ]);

        $file = $request->file('file');
        $code = $request->input('code', 'img');
        $name = $code . '_' . mt_rand(100, 9999) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/img/temp', $name, 'public');

        return response(Storage::disk('public')->url($path), 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
