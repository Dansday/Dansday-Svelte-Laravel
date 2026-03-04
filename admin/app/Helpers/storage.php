<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('upload_url')) {
    /**
     * Public URL for a file stored on the public disk (storage/app/public).
     * Uses default image path when $path is empty.
     */
    function upload_url(?string $path): string
    {
        $disk = Storage::disk('public');
        $default = 'uploads/img/image_default.png';
        $path = $path ?: $default;
        return $disk->exists($path) ? $disk->url($path) : $disk->url($default);
    }
}
