<?php

namespace App\Mcp\Tools;

use App\Exceptions\ContentWriteException;
use App\Mcp\Args;
use App\Models\LinkedInPost;
use App\Models\LinkedInScheduledPost;
use App\Services\LinkedInService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

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
                'name'        => 'react_to_linkedin_post',
                'description' => 'React to a post or a comment on LinkedIn. Works on anything you can see, not just your own content — pass the urn of someone else\'s post to react to it. Reaction types are LIKE, PRAISE, APPRECIATION, EMPATHY, INTEREST and ENTERTAINMENT; pass "none" to take your reaction back. Setting a new reaction replaces whatever you had before.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'id'       => ['type' => 'integer', 'description' => 'Local record id from list_linkedin_posts, for one of your own posts.'],
                        'urn'      => ['type' => 'string', 'description' => 'Any LinkedIn entity urn — a post, an activity or a comment. Use this to react to content that did not come from this server.'],
                        'reaction' => ['type' => 'string', 'description' => 'LIKE, PRAISE, APPRECIATION, EMPATHY, INTEREST, ENTERTAINMENT, or "none" to remove your reaction.'],
                        'confirm'  => ['type' => 'boolean', 'description' => 'Must be true. Reactions are public and attributed to you.'],
                    ],
                    'required'   => ['reaction', 'confirm'],
                ],
                'handler'     => fn (array $a) => self::react($a),
            ],
            [
                'name'        => 'schedule_linkedin_post',
                'description' => 'Queue a post to go out later instead of now. Takes the same arguments as post_to_linkedin plus publish_at. The arguments are validated immediately — a bad category, an over-long commentary or a missing upload is reported now rather than silently failing at publish time — but images, documents and video are uploaded when the post actually goes out, so the files must still be there. A worker publishes due posts; if it is not running, nothing is sent.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'publish_at'       => ['type' => 'string', 'description' => 'When to publish, as an absolute local datetime: YYYY-MM-DD HH:MM:SS or YYYY-MM-DD HH:MM. Must be in the future. Relative values like "tomorrow" are rejected.'],
                        'commentary'       => ['type' => 'string', 'description' => 'The post text, in your own words.'],
                        'article_id'       => ['type' => 'integer', 'description' => 'Optional id of a published article to link to.'],
                        'link_placement'   => ['type' => 'string', 'description' => 'body, card or none. Same meaning as in post_to_linkedin.'],
                        'link_title'       => ['type' => 'string', 'description' => 'Headline override for link_placement="card".'],
                        'link_description' => ['type' => 'string', 'description' => 'Summary override for link_placement="card".'],
                        'visibility'       => ['type' => 'string', 'description' => 'PUBLIC or CONNECTIONS. Defaults to PUBLIC.'],
                        'image'            => ['type' => 'string', 'description' => 'A single image, as in post_to_linkedin.'],
                        'alt_text'         => ['type' => 'string', 'description' => 'Alt text for the single image.'],
                        'images'           => [
                            'type'        => 'array',
                            'description' => 'Between 2 and 20 images, as in post_to_linkedin.',
                            'items'       => [
                                'type'       => 'object',
                                'properties' => [
                                    'path'     => ['type' => 'string', 'description' => 'An uploads path or absolute URL.'],
                                    'alt_text' => ['type' => 'string', 'description' => 'Alt text for this image.'],
                                ],
                                'required'   => ['path'],
                            ],
                        ],
                        'document'         => ['type' => 'string', 'description' => 'A media path from kind=document.'],
                        'document_title'   => ['type' => 'string', 'description' => 'Title for the document carousel.'],
                        'video'            => ['type' => 'string', 'description' => 'A media path from kind=video.'],
                        'video_title'      => ['type' => 'string', 'description' => 'Optional title for the video.'],
                        'confirm'          => ['type' => 'boolean', 'description' => 'Must be true. The post will publish on its own at the scheduled time.'],
                    ],
                    'required'   => ['publish_at', 'commentary', 'confirm'],
                ],
                'handler'     => fn (array $a) => self::schedule($a),
            ],
            [
                'name'        => 'list_linkedin_scheduled',
                'description' => 'List posts queued to publish later, soonest first. Shows what is still pending, what already went out and what failed, with the reason.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'status' => ['type' => 'string', 'description' => 'Filter by pending, published, failed or cancelled. Omit for everything still pending.'],
                        'limit'  => ['type' => 'integer', 'description' => 'Max rows to return (1-100). Default 25.'],
                        'offset' => ['type' => 'integer', 'description' => 'Rows to skip, for paging. Default 0.'],
                    ],
                ],
                'handler'     => fn (array $a) => self::listScheduled($a),
            ],
            [
                'name'        => 'cancel_linkedin_scheduled',
                'description' => 'Cancel a queued post so it never publishes. Only works while it is still pending — once it has gone out, use delete_linkedin_post instead.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer', 'description' => 'Scheduled post id from list_linkedin_scheduled.'],
                    ],
                    'required'   => ['id'],
                ],
                'handler'     => fn (array $a) => self::cancelScheduled($a),
            ],
            [
                'name'        => 'list_linkedin_posts',
                'description' => 'List the LinkedIn posts made through this server, newest first. LinkedIn does not let this app read your feed, so this is a local record of what it published — anything you posted from the LinkedIn app or website is not here. Use it to find the id or urn that delete_linkedin_post and edit_linkedin_post need.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'article_id'      => ['type' => 'integer', 'description' => 'Only posts that link to this article.'],
                        'include_deleted' => ['type' => 'boolean', 'description' => 'Include posts already deleted from LinkedIn. Default false.'],
                        'limit'           => ['type' => 'integer', 'description' => 'Max rows to return (1-100). Default 25.'],
                        'offset'          => ['type' => 'integer', 'description' => 'Rows to skip, for paging. Default 0.'],
                    ],
                ],
                'handler'     => fn (array $a) => self::listPosts($a),
            ],
            [
                'name'        => 'delete_linkedin_post',
                'description' => 'Permanently delete a post from LinkedIn. Identify it by the id or urn from list_linkedin_posts. This cannot be undone and the post disappears from every feed that showed it, so confirm the target first. Only posts made through this server can be deleted, because LinkedIn does not let this app read the rest of your feed.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'id'      => ['type' => 'integer', 'description' => 'Local record id from list_linkedin_posts.'],
                        'urn'     => ['type' => 'string', 'description' => 'The post URN, if you have that rather than the id.'],
                        'confirm' => ['type' => 'boolean', 'description' => 'Must be true. Guards against deleting a public post by accident.'],
                    ],
                    'required'   => ['confirm'],
                ],
                'handler'     => fn (array $a) => self::delete($a),
            ],
            [
                'name'        => 'edit_linkedin_post',
                'description' => 'Replace the text of a post already on LinkedIn, identified by the id or urn from list_linkedin_posts. Only the commentary can change — the image, document or video attached at publish time is fixed, and LinkedIn marks the post as edited. If the post links to an article, that URL is re-appended automatically, so leave it out of your text.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'id'         => ['type' => 'integer', 'description' => 'Local record id from list_linkedin_posts.'],
                        'urn'        => ['type' => 'string', 'description' => 'The post URN, if you have that rather than the id.'],
                        'commentary' => ['type' => 'string', 'description' => 'The replacement post text, in full. It overwrites the old text rather than being appended to it.'],
                        'confirm'    => ['type' => 'boolean', 'description' => 'Must be true. The change is public and immediate.'],
                    ],
                    'required'   => ['commentary', 'confirm'],
                ],
                'handler'     => fn (array $a) => self::edit($a),
            ],
            [
                'name'        => 'post_to_linkedin',
                'description' => 'Post to the connected LinkedIn account. A post carries at most one kind of media: a single image, several images as a swipeable set, a document (PDF, Word or PowerPoint, rendered by LinkedIn as a carousel), or a video. Documents and videos must be uploaded first to /mcp/uploads with kind=document or kind=video, which returns the media path to pass here. Pass article_id to share a published article — its URL is appended to your commentary. This tool never rewrites or summarises your commentary, and rejects anything over the character limit so you can shorten it and call again. Requires confirm=true, since the post is public and immediate.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'commentary'       => ['type' => 'string', 'description' => 'The post text, in your own words. Do not paste the article URL into it — link_placement decides where that goes.'],
                        'article_id'       => ['type' => 'integer', 'description' => 'Optional id of a published article to link to. Omit to post standalone content.'],
                        'link_placement'   => ['type' => 'string', 'description' => 'Where the article URL goes. "body" (default) appends it to the text. "card" attaches a proper link preview with its own title, description and thumbnail, and keeps the URL out of the text entirely — it cannot be combined with an image, document or video. "none" mentions no URL at all. Ignored without article_id.'],
                        'link_title'       => ['type' => 'string', 'description' => 'Overrides the headline shown on the link card. Defaults to the article title. Only used with link_placement="card".'],
                        'link_description' => ['type' => 'string', 'description' => 'Overrides the summary shown on the link card. Defaults to the article summary. Only used with link_placement="card".'],
                        'visibility'     => ['type' => 'string', 'description' => 'PUBLIC (anyone) or CONNECTIONS (first-degree only). Defaults to PUBLIC.'],
                        'image'          => ['type' => 'string', 'description' => 'A single image: an uploads path from POST /mcp/uploads, or an absolute URL. Pass "article" to reuse the linked article\'s cover image, which needs article_id. Mutually exclusive with images, document and video.'],
                        'alt_text'       => ['type' => 'string', 'description' => 'Alt text for the single image, for screen readers. Keep it under 120 characters.'],
                        'images'         => [
                            'type'        => 'array',
                            'description' => 'Between 2 and 20 images posted as one swipeable set. Each entry is an object with a path and optional alt_text. Use image instead when there is only one. Mutually exclusive with image, document and video.',
                            'items'       => [
                                'type'       => 'object',
                                'properties' => [
                                    'path'     => ['type' => 'string', 'description' => 'An uploads path from POST /mcp/uploads, or an absolute URL.'],
                                    'alt_text' => ['type' => 'string', 'description' => 'Alt text for this image.'],
                                ],
                                'required'   => ['path'],
                            ],
                        ],
                        'document'       => ['type' => 'string', 'description' => 'A media path from POST /mcp/uploads with kind=document (PDF, DOC, DOCX, PPT or PPTX). LinkedIn renders it as a swipeable carousel. Mutually exclusive with image, images and video.'],
                        'document_title' => ['type' => 'string', 'description' => 'Title shown on the document carousel. Required when document is given.'],
                        'video'          => ['type' => 'string', 'description' => 'A media path from POST /mcp/uploads with kind=video (MP4 or MOV). Mutually exclusive with image, images and document.'],
                        'video_title'    => ['type' => 'string', 'description' => 'Optional title shown with the video.'],
                        'confirm'        => ['type' => 'boolean', 'description' => 'Must be true. Guards against posting publicly by accident.'],
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

        return self::publish($args);
    }

    public static function validatePayload(array $args): array
    {
        $visibility = strtoupper(trim((string) ($args['visibility'] ?? 'PUBLIC')));

        if (! in_array($visibility, ['PUBLIC', 'CONNECTIONS'], true)) {
            throw new ContentWriteException('visibility must be PUBLIC or CONNECTIONS.');
        }

        $mode = self::selectMode($args);
        $articleId = isset($args['article_id']) && $args['article_id'] !== '' ? (int) $args['article_id'] : null;
        $placement = self::selectPlacement($args, $mode, $articleId !== null);

        $commentary = trim((string) ($args['commentary'] ?? ''));

        if ($commentary === '') {
            throw new ContentWriteException('commentary is empty. Write the post text yourself.');
        }

        return [
            'visibility'    => $visibility,
            'mode'          => $mode,
            'placement'     => $placement,
            'article_id'    => $articleId,
            'commentary'    => $commentary,
        ];
    }

    public static function publish(array $args): array
    {
        $plan = self::validatePayload($args);

        $status = LinkedInService::status();
        if (empty($status['connected'])) {
            return $status;
        }

        $articleId = $plan['article_id'];
        $article = $articleId ? LinkedInService::articleUrl($articleId) : null;

        $body = $plan['commentary'];

        if ($article && $plan['placement'] === 'body') {
            $body .= "\n\n".$article['url'];
        }

        $length = mb_strlen($body);

        if ($length > LinkedInService::MAX_COMMENTARY) {
            return [
                'ok'         => false,
                'reason'     => 'too_long',
                'max'        => LinkedInService::MAX_COMMENTARY,
                'actual'     => $length,
                'over_by'    => $length - LinkedInService::MAX_COMMENTARY,
                'url_length' => $article && $plan['placement'] === 'body' ? mb_strlen($article['url']) + 2 : 0,
                'message'    => 'The commentary is over the limit. Shorten it and call this tool again.',
            ];
        }

        $resolved = $plan['placement'] === 'card'
            ? self::linkCard($args, $article)
            : self::resolveContent($args, $article, $plan['mode']);

        if (isset($resolved['error'])) {
            return $resolved['error'] + ['article_id' => $articleId];
        }

        $result = LinkedInService::share($body, $plan['visibility'], $resolved['content'], [
            'article_id' => $articleId,
            'media_type' => $resolved['type'],
        ]);

        if (empty($result['ok'])) {
            return $result + ['media_type' => $resolved['type'], 'article_id' => $articleId];
        }

        $result += ['media_type' => $resolved['type'], 'link_placement' => $plan['placement']];

        if ($article) {
            $result += [
                'article_id'    => $articleId,
                'article_title' => $article['title'],
                'article_url'   => $article['url'],
            ];
        } else {
            $result += ['article_id' => null];
        }

        return $result;
    }

    private static function selectPlacement(array $args, ?string $mode, bool $hasArticle): string
    {
        $placement = strtolower(trim((string) ($args['link_placement'] ?? '')));

        if ($placement === '') {
            $placement = 'body';
        }

        if (! in_array($placement, ['body', 'card', 'none'], true)) {
            throw new ContentWriteException('link_placement must be one of: body, card, none.');
        }

        if ($placement === 'card' && ! $hasArticle) {
            throw new ContentWriteException("link_placement=\"{$placement}\" needs an article_id, since there is otherwise no link to place.");
        }

        if ($placement === 'card' && $mode !== null) {
            throw new ContentWriteException(
                'A link card occupies the same slot as media, so link_placement="card" cannot be combined with '.$mode.'. Drop one.'
            );
        }

        return $placement;
    }

    private static function linkCard(array $args, array $article): array
    {
        $title = trim((string) ($args['link_title'] ?? '')) ?: $article['title'];
        $description = trim((string) ($args['link_description'] ?? '')) ?: trim((string) ($article['summary'] ?? ''));

        self::assertTitle($title, 'link_title');

        $card = ['source' => $article['url'], 'title' => $title];

        if ($description !== '') {
            $card['description'] = mb_substr($description, 0, 4086);
        }

        $cover = trim((string) ($article['image'] ?? ''));

        if ($cover !== '') {
            $thumbnail = LinkedInService::thumbnailFor($cover);

            if ($thumbnail !== null) {
                $card['thumbnail'] = $thumbnail;
            }
        }

        return ['content' => ['article' => $card], 'type' => 'link_card'];
    }

    private static function schedule(array $args): array
    {
        if (($args['confirm'] ?? false) !== true) {
            throw new ContentWriteException('Refusing to schedule: pass confirm=true once the user has agreed this should publish on its own.');
        }

        $publishAt = self::publishAt((string) $args['publish_at']);
        $plan = self::validatePayload($args);

        if ($plan['article_id']) {
            LinkedInService::articleUrl($plan['article_id']);
        }

        $payload = Arr::only($args, [
            'commentary', 'article_id', 'visibility', 'link_placement', 'link_title', 'link_description',
            'image', 'alt_text', 'images', 'document', 'document_title', 'video', 'video_title',
        ]);

        $scheduled = LinkedInScheduledPost::create([
            'commentary' => $plan['commentary'],
            'article_id' => $plan['article_id'],
            'status'     => LinkedInScheduledPost::PENDING,
            'publish_at' => $publishAt,
            'payload'    => $payload,
        ]);

        return [
            'ok'             => true,
            'scheduled_id'   => $scheduled->id,
            'publish_at'     => $publishAt->toDateTimeString(),
            'publishes_in'   => now()->diffForHumans($publishAt, ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]),
            'link_placement' => $plan['placement'],
            'media_type'     => $plan['mode'] ?? ($plan['placement'] === 'card' ? 'link_card' : 'text'),
            'note'           => 'Queued. A worker publishes due posts; uploads happen at publish time, so leave the files in place.',
        ];
    }

    private static function publishAt(string $raw): Carbon
    {
        $raw = trim($raw);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}([ T]\d{2}:\d{2}(:\d{2})?)?$/', $raw)) {
            throw new ContentWriteException(
                "Could not read publish_at \"{$raw}\". Use an absolute datetime: YYYY-MM-DD HH:MM or YYYY-MM-DD HH:MM:SS. "
                .'Relative values like "tomorrow" are not accepted — work out the time first.'
            );
        }

        try {
            $when = Carbon::parse(str_replace('T', ' ', $raw));
        } catch (\Throwable $e) {
            throw new ContentWriteException("Could not read publish_at \"{$raw}\" as a real date.");
        }

        if ($when->isPast()) {
            throw new ContentWriteException(
                'publish_at is in the past ('.$when->toDateTimeString().'). Pick a future time, or use post_to_linkedin to publish now.'
            );
        }

        return $when;
    }

    private static function listScheduled(array $args): array
    {
        $status = strtolower(trim((string) ($args['status'] ?? '')));
        $allowed = [
            LinkedInScheduledPost::PENDING,
            LinkedInScheduledPost::PUBLISHED,
            LinkedInScheduledPost::FAILED,
            LinkedInScheduledPost::CANCELLED,
        ];

        if ($status !== '' && ! in_array($status, $allowed, true)) {
            throw new ContentWriteException('status must be one of: '.implode(', ', $allowed).'.');
        }

        $query = LinkedInScheduledPost::query()->orderBy('publish_at');
        $query->where('status', $status !== '' ? $status : LinkedInScheduledPost::PENDING);

        $total = (clone $query)->count();
        $limit = Args::limit($args);
        $offset = Args::offset($args);

        $rows = $query->skip($offset)->take($limit)->get()->map(fn (LinkedInScheduledPost $row) => array_filter([
            'id'               => $row->id,
            'status'           => $row->status,
            'publish_at'       => $row->publish_at?->toDateTimeString(),
            'commentary'       => $row->commentary,
            'article_id'       => $row->article_id,
            'link_placement'   => $row->payload['link_placement'] ?? 'body',
            'attempts'         => $row->attempts,
            'last_error'       => $row->last_error,
            'linkedin_post_id' => $row->linkedin_post_id,
            'published_at'     => $row->published_at?->toDateTimeString(),
            'cancelled_at'     => $row->cancelled_at?->toDateTimeString(),
        ], fn ($value) => $value !== null))->all();

        return [
            'scheduled' => $rows,
            'total'     => $total,
            'limit'     => $limit,
            'offset'    => $offset,
            'status'    => $status !== '' ? $status : LinkedInScheduledPost::PENDING,
        ];
    }

    private static function cancelScheduled(array $args): array
    {
        $row = LinkedInScheduledPost::find((int) $args['id']);

        if (! $row) {
            throw new ContentWriteException('No scheduled post with id '.(int) $args['id'].'.');
        }

        if (! $row->isPending()) {
            return [
                'ok'      => false,
                'reason'  => 'not_pending',
                'status'  => $row->status,
                'message' => 'That post is '.$row->status.', so there is nothing to cancel.'
                    .($row->status === LinkedInScheduledPost::PUBLISHED ? ' Use delete_linkedin_post to remove it from LinkedIn.' : ''),
            ];
        }

        $row->forceFill([
            'status'       => LinkedInScheduledPost::CANCELLED,
            'cancelled_at' => now(),
        ])->save();

        return ['ok' => true, 'cancelled' => true, 'scheduled_id' => $row->id];
    }

    private static function assertUrn(string $urn, string $field): string
    {
        $urn = trim($urn);

        if (! str_starts_with($urn, 'urn:li:')) {
            throw new ContentWriteException("{$field} must be a LinkedIn urn starting with \"urn:li:\". Got \"{$urn}\".");
        }

        return $urn;
    }

    private static function entityUrn(array $args): string
    {
        if (isset($args['id']) && $args['id'] !== '') {
            return LinkedInService::findPost((int) $args['id'], null)->urn;
        }

        $urn = trim((string) ($args['urn'] ?? ''));

        if ($urn === '') {
            throw new ContentWriteException('Pass either id, for one of your own posts, or urn for anything else.');
        }

        return self::assertUrn($urn, 'urn');
    }

    private static function react(array $args): array
    {
        if (($args['confirm'] ?? false) !== true) {
            throw new ContentWriteException('Refusing to react: pass confirm=true once the user has agreed to this public reaction.');
        }

        $reaction = strtoupper(trim((string) $args['reaction']));

        if ($reaction === 'NONE' || $reaction === '') {
            return LinkedInService::react(self::entityUrn($args), null);
        }

        if (! in_array($reaction, LinkedInService::REACTIONS, true)) {
            throw new ContentWriteException(
                'reaction must be one of: '.implode(', ', LinkedInService::REACTIONS).', or "none" to remove it.'
            );
        }

        return LinkedInService::react(self::entityUrn($args), $reaction);
    }

    private static function listPosts(array $args): array
    {
        $query = LinkedInPost::query()->orderByDesc('posted_at')->orderByDesc('id');

        if (empty($args['include_deleted'])) {
            $query->live();
        }

        if (! empty($args['article_id'])) {
            $query->where('article_id', (int) $args['article_id']);
        }

        $total = (clone $query)->count();
        $limit = Args::limit($args);
        $offset = Args::offset($args);

        $posts = $query->skip($offset)->take($limit)->get()->map(fn (LinkedInPost $post) => [
            'id'         => $post->id,
            'urn'        => $post->urn,
            'url'        => $post->url(),
            'article_id' => $post->article_id,
            'media_type' => $post->media_type,
            'visibility' => $post->visibility,
            'commentary' => $post->commentary,
            'posted_at'  => $post->posted_at?->toDateTimeString(),
            'edited_at'  => $post->edited_at?->toDateTimeString(),
            'deleted_at' => $post->deleted_at?->toDateTimeString(),
        ])->all();

        return [
            'posts'  => $posts,
            'total'  => $total,
            'limit'  => $limit,
            'offset' => $offset,
            'note'   => 'This is what this server published. Posts written from the LinkedIn app or website are not visible to it.',
        ];
    }

    private static function delete(array $args): array
    {
        if (($args['confirm'] ?? false) !== true) {
            throw new ContentWriteException('Refusing to delete: pass confirm=true once the user has agreed to remove this post from LinkedIn.');
        }

        $post = LinkedInService::findPost(
            isset($args['id']) && $args['id'] !== '' ? (int) $args['id'] : null,
            $args['urn'] ?? null
        );

        return LinkedInService::deletePost($post);
    }

    private static function edit(array $args): array
    {
        if (($args['confirm'] ?? false) !== true) {
            throw new ContentWriteException('Refusing to edit: pass confirm=true once the user has agreed to change this public post.');
        }

        $post = LinkedInService::findPost(
            isset($args['id']) && $args['id'] !== '' ? (int) $args['id'] : null,
            $args['urn'] ?? null
        );

        $commentary = trim((string) $args['commentary']);

        if ($commentary === '') {
            throw new ContentWriteException('commentary is empty. Write the replacement text yourself.');
        }

        $body = $commentary;
        $article = null;

        if ($post->article_id) {
            $article = LinkedInService::articleUrl((int) $post->article_id);
            $body = $commentary."\n\n".$article['url'];
        }

        $length = mb_strlen($body);

        if ($length > LinkedInService::MAX_COMMENTARY) {
            return [
                'ok'      => false,
                'reason'  => 'too_long',
                'max'     => LinkedInService::MAX_COMMENTARY,
                'actual'  => $length,
                'over_by' => $length - LinkedInService::MAX_COMMENTARY,
                'message' => 'The replacement commentary'.($article ? ' plus the appended URL' : '').' is over the limit. Shorten it and call this tool again.',
            ];
        }

        return LinkedInService::editPost($post, $body) + ($article ? ['article_url' => $article['url']] : []);
    }

    private static function selectMode(array $args): ?string
    {
        $modes = array_keys(array_filter([
            'image'    => trim((string) ($args['image'] ?? '')) !== '',
            'images'   => ! empty($args['images']),
            'document' => trim((string) ($args['document'] ?? '')) !== '',
            'video'    => trim((string) ($args['video'] ?? '')) !== '',
        ]));

        if (count($modes) > 1) {
            throw new ContentWriteException(
                'A LinkedIn post carries one kind of media. Pass only one of image, images, document or video — got '.implode(', ', $modes).'.'
            );
        }

        return $modes[0] ?? null;
    }

    private static function resolveContent(array $args, ?array $article, ?string $mode): array
    {
        return match ($mode) {
            'image'    => self::singleImage($args, $article),
            'images'   => self::multiImage($args),
            'document' => self::document($args),
            'video'    => self::video($args),
            default    => ['content' => null, 'type' => 'text'],
        };
    }

    private static function singleImage(array $args, ?array $article): array
    {
        $imageArg = trim((string) $args['image']);

        if ($imageArg === 'article' && ! $article) {
            throw new ContentWriteException('image="article" needs an article_id. Pass one, or give an uploads path or URL instead.');
        }

        $source = $imageArg === 'article' ? trim((string) ($article['image'] ?? '')) : $imageArg;

        if ($source === '') {
            throw new ContentWriteException('That article has no cover image, so image="article" has nothing to attach. Upload one to /mcp/uploads and pass its path instead.');
        }

        $altText = self::altText($args['alt_text'] ?? '');
        $uploaded = LinkedInService::uploadImage($source);

        if (empty($uploaded['ok'])) {
            return ['error' => $uploaded + ['image_source' => $source]];
        }

        $media = ['id' => $uploaded['image']] + ($altText !== '' ? ['altText' => $altText] : []);

        return ['content' => ['media' => $media], 'type' => 'image'];
    }

    private static function multiImage(array $args): array
    {
        $entries = [];

        foreach ($args['images'] as $index => $item) {
            if (is_string($item)) {
                $entries[] = ['path' => trim($item), 'alt_text' => ''];

                continue;
            }

            if (! is_array($item)) {
                throw new ContentWriteException("images[{$index}] must be a path or an object with a path.");
            }

            $entries[] = [
                'path'     => trim((string) ($item['path'] ?? '')),
                'alt_text' => (string) ($item['alt_text'] ?? ''),
            ];
        }

        $entries = array_values(array_filter($entries, fn (array $e) => $e['path'] !== ''));
        $count = count($entries);

        if ($count < LinkedInService::MIN_MULTI_IMAGES) {
            throw new ContentWriteException(
                'images needs at least '.LinkedInService::MIN_MULTI_IMAGES.' entries, got '.$count.'. Use the image argument for a single picture.'
            );
        }

        if ($count > LinkedInService::MAX_IMAGES) {
            throw new ContentWriteException(
                'LinkedIn accepts at most '.LinkedInService::MAX_IMAGES.' images in one post, got '.$count.'.'
            );
        }

        $images = [];

        foreach ($entries as $index => $entry) {
            $uploaded = LinkedInService::uploadImage($entry['path']);

            if (empty($uploaded['ok'])) {
                return ['error' => $uploaded + ['image_index' => $index, 'image_source' => $entry['path']]];
            }

            $altText = self::altText($entry['alt_text']);
            $images[] = ['id' => $uploaded['image']] + ($altText !== '' ? ['altText' => $altText] : []);
        }

        return ['content' => ['multiImage' => ['images' => $images]], 'type' => 'multi_image'];
    }

    private static function document(array $args): array
    {
        $title = trim((string) ($args['document_title'] ?? ''));

        if ($title === '') {
            throw new ContentWriteException('document_title is required. LinkedIn shows it as the heading on the document carousel.');
        }

        self::assertTitle($title, 'document_title');

        $uploaded = LinkedInService::uploadDocument(trim((string) $args['document']));

        if (empty($uploaded['ok'])) {
            return ['error' => $uploaded + ['document_source' => trim((string) $args['document'])]];
        }

        return [
            'content' => ['media' => ['id' => $uploaded['document'], 'title' => $title]],
            'type'    => 'document',
        ];
    }

    private static function video(array $args): array
    {
        $title = trim((string) ($args['video_title'] ?? ''));

        if ($title !== '') {
            self::assertTitle($title, 'video_title');
        }

        $uploaded = LinkedInService::uploadVideo(trim((string) $args['video']));

        if (empty($uploaded['ok'])) {
            return ['error' => $uploaded + ['video_source' => trim((string) $args['video'])]];
        }

        $media = ['id' => $uploaded['video']] + ($title !== '' ? ['title' => $title] : []);

        return ['content' => ['media' => $media], 'type' => 'video'];
    }

    private static function altText(string|int|float|null $value): string
    {
        $altText = trim((string) $value);

        if (mb_strlen($altText) > LinkedInService::MAX_ALT_TEXT) {
            throw new ContentWriteException('alt_text is longer than '.LinkedInService::MAX_ALT_TEXT.' characters.');
        }

        return $altText;
    }

    private static function assertTitle(string $title, string $field): void
    {
        if (mb_strlen($title) > LinkedInService::MAX_TITLE) {
            throw new ContentWriteException($field.' is longer than '.LinkedInService::MAX_TITLE.' characters.');
        }
    }
}
