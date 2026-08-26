<?php

namespace App\Mcp\Tools;

use App\Exceptions\ContentWriteException;
use App\Services\LinkedInService;

class LinkedInTools
{
    public static function definitions(): array
    {
        return [
            [
                'name'        => 'get_linkedin_status',
                'description' => 'Check whether a LinkedIn account is connected for posting, who it posts as, and when the token expires. Call this before post_article_to_linkedin. When it comes back not connected it includes a connect_url — give that URL to the user to open in a browser, then try posting again once they say they have approved it.',
                'inputSchema' => ['type' => 'object', 'properties' => []],
                'handler'     => fn () => LinkedInService::status(),
            ],
            [
                'name'        => 'post_article_to_linkedin',
                'description' => 'Post a published article to the connected LinkedIn account as a text post with the article URL appended. You write the commentary yourself — this tool never rewrites or summarises it, and rejects anything over the character limit so you can shorten it and call again. The article must be published, because the URL is derived from its title. Requires confirm=true, since the post is public and immediate.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'article_id'  => ['type' => 'integer', 'description' => 'Id of the published article to link to.'],
                        'commentary'  => ['type' => 'string', 'description' => 'The post text, in your own words. The article URL is appended automatically, so do not include it.'],
                        'visibility'  => ['type' => 'string', 'description' => 'PUBLIC (anyone) or CONNECTIONS (first-degree only). Defaults to PUBLIC.'],
                        'confirm'     => ['type' => 'boolean', 'description' => 'Must be true. Guards against posting publicly by accident.'],
                    ],
                    'required'   => ['article_id', 'commentary', 'confirm'],
                ],
                'handler'     => fn (array $a) => self::post($a),
            ],
        ];
    }

    private static function post(array $args): array
    {
        if (($args['confirm'] ?? false) !== true) {
            throw new ContentWriteException('Refusing to post: pass confirm=true once the user has agreed to publish this to LinkedIn.');
        }

        $visibility = strtoupper(trim((string) ($args['visibility'] ?? 'PUBLIC')));
        if (! in_array($visibility, ['PUBLIC', 'CONNECTIONS'], true)) {
            throw new ContentWriteException('visibility must be PUBLIC or CONNECTIONS.');
        }

        $status = LinkedInService::status();
        if (empty($status['connected'])) {
            return $status;
        }

        $article = LinkedInService::articleUrl((int) $args['article_id']);

        $commentary = trim((string) $args['commentary']);
        if ($commentary === '') {
            throw new ContentWriteException('commentary is empty. Write the post text yourself.');
        }

        $body = $commentary."\n\n".$article['url'];
        $length = mb_strlen($body);

        if ($length > LinkedInService::MAX_COMMENTARY) {
            return [
                'ok'         => false,
                'reason'     => 'too_long',
                'max'        => LinkedInService::MAX_COMMENTARY,
                'actual'     => $length,
                'over_by'    => $length - LinkedInService::MAX_COMMENTARY,
                'url_length' => mb_strlen($article['url']) + 2,
                'message'    => 'The commentary plus the appended URL is over the limit. Shorten the commentary and call this tool again.',
            ];
        }

        $result = LinkedInService::share($body, $visibility);

        return $result + [
            'article_id'    => (int) $args['article_id'],
            'article_title' => $article['title'],
            'article_url'   => $article['url'],
        ];
    }
}
