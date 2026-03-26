<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AiClientFactory
{
    public static function chat(): ?array
    {
        $general = DB::table('page_setting')->where('id', 1)->first();
        if (!$general) return null;

        $url = trim($general->ai_url ?? '');
        $key = trim($general->ai_key ?? '');
        $model = trim($general->ai_model ?? '');

        if (!$url || !$key) return null;

        $client = \OpenAI::factory()
            ->withApiKey($key)
            ->withBaseUri(self::normalizeUrl($url, '/chat/completions'))
            ->withHttpClient(new \GuzzleHttp\Client(['timeout' => 30]))
            ->make();

        return [
            'client' => $client,
            'model' => $model ?: 'default',
            'general' => $general,
        ];
    }

    public static function embedding(): ?array
    {
        $general = DB::table('page_setting')->where('id', 1)->first();
        if (!$general) return null;

        $url = trim($general->embedding_url ?? '');
        $key = trim($general->embedding_key ?? '');
        $model = trim($general->embedding_model ?? '');

        if (!$url || !$key || !$model) return null;

        $client = \OpenAI::factory()
            ->withApiKey($key)
            ->withBaseUri(self::normalizeUrl($url, '/embeddings'))
            ->withHttpClient(new \GuzzleHttp\Client(['timeout' => 60]))
            ->make();

        return [
            'client' => $client,
            'model' => $model,
        ];
    }

    private static function normalizeUrl(string $url, string $suffix): string
    {
        $url = rtrim($url, '/');
        if (str_ends_with($url, $suffix)) {
            $url = substr($url, 0, -strlen($suffix));
        }
        return $url;
    }
}
