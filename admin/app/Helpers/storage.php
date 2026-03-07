<?php

use Illuminate\Support\Facades\Storage;

/** Allowed path prefixes for uploads disk (relative to uploads root). Deletes are only allowed under these. */
const UPLOADS_ALLOWED_PREFIXES = [
    'img/articles/',
    'img/projects/',
    'img/profile/',
    'img/general/',
    'img/temp/',
    'img/work/',
];

if (! function_exists('uploads_path_for_disk')) {
    /**
     * Normalize path for the uploads disk: strip leading "uploads/" and disallow path traversal.
     */
    function uploads_path_for_disk(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }
        $path = str_replace(['\\', '../'], ['/', ''], $path);
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, 8);
        }
        return $path;
    }
}

if (! function_exists('uploads_path_safe_to_delete')) {
    /**
     * Return true only if the path is under an allowed uploads prefix (for safe delete).
     */
    function uploads_path_safe_to_delete(?string $path): bool
    {
        $path = uploads_path_for_disk($path);
        if ($path === '') {
            return false;
        }
        foreach (UPLOADS_ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix) || $path === rtrim($prefix, '/')) {
                return true;
            }
        }
        return false;
    }
}

if (! function_exists('upload_url')) {
    /**
     * Public URL for a file on the uploads disk.
     */
    function upload_url(?string $path): string
    {
        $disk = Storage::disk('uploads');
        $default = 'img/image_default.png';
        $path = uploads_path_for_disk($path ?: 'uploads/img/image_default.png');
        $path = $path ?: $default;
        return $disk->exists($path) ? $disk->url($path) : $disk->url($default);
    }
}
