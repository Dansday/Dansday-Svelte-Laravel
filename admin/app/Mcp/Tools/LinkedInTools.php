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
                'description' => 'Check whether a LinkedIn account is connected for posting, who it posts as, and when the token expires. Call this before post_to_linkedin. When it comes back not connected it includes a connect_url — give that URL to the user to open in a browser, then try posting again once they say they have approved it.',
                'inputSchema' => ['type' => 'object', 'properties' => []],
                'handler'     => fn () => LinkedInService::status(),
            ],
            [
                'name'        => 'post_to_linkedin',
                'description' => 'Post to the connected LinkedIn account, with an optional image. Pass article_id to share a published article — its URL is appended to your commentary. Leave article_id out to post something written in this conversation, with no link to the site at all. This tool never rewrites or summarises your commentary, and rejects anything over the character limit so you can shorten it and call again. Requires confirm=true, since the post is public and immediate.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'commentary'  => ['type' => 'string', 'description' => 'The post text, in your own words. When article_id is given the article URL is appended automatically, so do not include it.'],
                        'article_id'  => ['type' => 'integer', 'description' => 'Optional id of a published article to link to. Omit to post standalone content.'],
                        'visibility'  => ['type' => 'string', 'description' => 'PUBLIC (anyone) or CONNECTIONS (first-degree only). Defaults to PUBLIC.'],
                        'image'       => ['type' => 'string', 'description' => 'Optional image to attach: an uploads path from POST /mcp/uploads, or an absolute URL. Pass "article" to reuse the linked article\'s cover image, which needs article_id. Omit for a text-only post.'],
                        'alt_text'    => ['type' => 'string', 'description' => 'Alt text for the image, for screen readers. Keep it under 120 characters. Only used when an image is attached.'],
                        'confirm'     => ['type' => 'boolean', 'description' => 'Must be true. Guards against posting publicly by accident.'],
                    ],
                    'required'   => ['commentary', 'confirm'],
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

        $articleId = isset($args['article_id']) && $args['article_id'] !== '' ? (int) $args['article_id'] : null;
        $article = $articleId ? LinkedInService::articleUrl($articleId) : null;

        $commentary = trim((string) $args['commentary']);
        if ($commentary === '') {
            throw new ContentWriteException('commentary is empty. Write the post text yourself.');
        }

        $body = $article ? $commentary."\n\n".$article['url'] : $commentary;
        $length = mb_strlen($body);

        if ($length > LinkedInService::MAX_COMMENTARY) {
            return [
                'ok'         => false,
                'reason'     => 'too_long',
                'max'        => LinkedInService::MAX_COMMENTARY,
                'actual'     => $length,
                'over_by'    => $length - LinkedInService::MAX_COMMENTARY,
                'url_length' => $article ? mb_strlen($article['url']) + 2 : 0,
                'message'    => 'The commentary'.($article ? ' plus the appended URL' : '').' is over the limit. Shorten it and call this tool again.',
            ];
        }

        $media = null;
        $imageArg = trim((string) ($args['image'] ?? ''));

        if ($imageArg !== '') {
            if ($imageArg === 'article' && ! $article) {
                throw new ContentWriteException('image="article" needs an article_id. Pass one, or give an uploads path or URL instead.');
            }

            $source = $imageArg === 'article' ? trim((string) ($article['image'] ?? '')) : $imageArg;

            if ($source === '') {
                throw new ContentWriteException('That article has no cover image, so image="article" has nothing to attach. Upload one to /mcp/uploads and pass its path instead.');
            }

            $altText = trim((string) ($args['alt_text'] ?? ''));

            if (mb_strlen($altText) > LinkedInService::MAX_ALT_TEXT) {
                throw new ContentWriteException('alt_text is longer than '.LinkedInService::MAX_ALT_TEXT.' characters.');
            }

            $uploaded = LinkedInService::uploadImage($source);

            if (empty($uploaded['ok'])) {
                return $uploaded + ['article_id' => $articleId, 'image_source' => $source];
            }

            $media = ['id' => $uploaded['image']] + ($altText !== '' ? ['altText' => $altText] : []);
        }

        $result = LinkedInService::share($body, $visibility, $media);

        return $result + ($article ? [
            'article_id'    => $articleId,
            'article_title' => $article['title'],
            'article_url'   => $article['url'],
        ] : ['article_id' => null]);
    }
}
