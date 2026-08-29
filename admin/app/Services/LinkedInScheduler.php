<?php

namespace App\Services;

use App\Mcp\Tools\LinkedInTools;
use App\Models\LinkedInScheduledPost;
use Illuminate\Support\Facades\Log;

class LinkedInScheduler
{
    public const MAX_ATTEMPTS = 3;

    public static function runDue(int $max = 5): array
    {
        $due = LinkedInScheduledPost::due()->orderBy('publish_at')->take(max(1, $max))->get();

        $published = 0;
        $retrying = 0;
        $failed = 0;
        $errors = [];

        foreach ($due as $row) {
            $row->forceFill(['attempts' => $row->attempts + 1])->save();

            try {
                $result = LinkedInTools::publish($row->payload ?? []);
            } catch (\Throwable $e) {
                $result = ['ok' => false, 'reason' => 'exception', 'message' => $e->getMessage()];
            }

            if (! empty($result['ok'])) {
                $row->forceFill([
                    'status'           => LinkedInScheduledPost::PUBLISHED,
                    'published_at'     => now(),
                    'linkedin_post_id' => $result['post_id'] ?? null,
                    'last_error'       => null,
                ])->save();

                $published++;

                continue;
            }

            $reason = self::describe($result);
            $errors[] = '#'.$row->id.': '.$reason;

            if ($row->attempts >= self::MAX_ATTEMPTS) {
                $row->forceFill([
                    'status'     => LinkedInScheduledPost::FAILED,
                    'last_error' => $reason,
                ])->save();

                $failed++;
                Log::warning('LinkedIn scheduled post #'.$row->id.' failed permanently: '.$reason);

                continue;
            }

            $row->forceFill(['last_error' => $reason])->save();
            $retrying++;
        }

        return [
            'published' => $published,
            'retrying'  => $retrying,
            'failed'    => $failed,
            'errors'    => $errors,
        ];
    }

    private static function describe(array $result): string
    {
        $parts = array_filter([
            $result['reason'] ?? null,
            $result['message'] ?? null,
            isset($result['status']) ? 'HTTP '.$result['status'] : null,
        ]);

        return $parts === [] ? 'unknown error' : implode(' — ', $parts);
    }
}
