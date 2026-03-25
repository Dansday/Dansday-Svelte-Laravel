<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    /** Content builders per table: returns text to embed from a row */
    private static array $contentBuilders = [
        'articles' => ['fields' => ['title', 'description']],
        'projects' => ['fields' => ['title', 'description']],
        'experience' => ['fields' => ['title', 'period', 'description']],
        'service' => ['fields' => ['title', 'description']],
        'skill' => ['fields' => ['title', 'type']],
        'testimonial' => ['fields' => ['name', 'company', 'description']],
    ];

    /**
     * Embed a single row after create/update.
     * Runs in background-safe way — silently fails if embedding is not configured.
     */
    public static function embedRow(string $table, int $rowId): void
    {
        try {
            $config = self::getConfig();
            if (!$config) return;

            $row = DB::table($table)->where('id', $rowId)->first();
            if (!$row) return;

            $text = self::buildContent($table, (array) $row);
            if (!$text) return;

            $hash = hash('sha256', $text);

            $existing = DB::table('embeddings')
                ->where('table_name', $table)
                ->where('row_id', $rowId)
                ->first();

            if ($existing && $existing->content_hash === $hash) {
                return; // Content unchanged
            }

            $vector = self::callApi($config, $text);
            if (!$vector) return;

            $vectorJson = json_encode($vector);

            if ($existing) {
                DB::table('embeddings')
                    ->where('table_name', $table)
                    ->where('row_id', $rowId)
                    ->update([
                        'vector' => $vectorJson,
                        'content_hash' => $hash,
                        'created_at' => now(),
                    ]);
            } else {
                DB::table('embeddings')->insert([
                    'table_name' => $table,
                    'row_id' => $rowId,
                    'content_hash' => $hash,
                    'vector' => $vectorJson,
                    'created_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Embedding failed for ' . $table . ':' . $rowId . ' - ' . $e->getMessage());
        }
    }

    /**
     * Remove embedding for a deleted row.
     */
    public static function deleteRow(string $table, int $rowId): void
    {
        try {
            DB::table('embeddings')
                ->where('table_name', $table)
                ->where('row_id', $rowId)
                ->delete();
        } catch (\Throwable $e) {
            Log::warning('Embedding delete failed for ' . $table . ':' . $rowId . ' - ' . $e->getMessage());
        }
    }

    private static function getConfig(): ?array
    {
        $general = DB::table('page_setting')->where('id', 1)->first();
        if (!$general) return null;

        $url = trim($general->embedding_url ?? '');
        $key = trim($general->embedding_key ?? '');
        $model = trim($general->embedding_model ?? '');

        if (!$url || !$key || !$model) return null;

        return compact('url', 'key', 'model');
    }

    private static function buildContent(string $table, array $row): string
    {
        $config = self::$contentBuilders[$table] ?? null;
        if (!$config) return '';

        $parts = [];
        foreach ($config['fields'] as $field) {
            $value = $row[$field] ?? '';
            $value = strip_tags($value);
            $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
            $value = preg_replace('/\s+/', ' ', $value);
            $value = trim($value);
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        return implode("\n", $parts);
    }

    private static function callApi(array $config, string $text): ?array
    {
        $baseUrl = rtrim($config['url'], '/');
        if (!str_ends_with($baseUrl, '/embeddings')) {
            $baseUrl .= '/embeddings';
        }

        $ch = curl_init($baseUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $config['key'],
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $config['model'],
                'input' => $text,
            ]),
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            Log::warning('Embedding API error: HTTP ' . $httpCode);
            return null;
        }

        $data = json_decode($response, true);
        return $data['data'][0]['embedding'] ?? null;
    }
}
