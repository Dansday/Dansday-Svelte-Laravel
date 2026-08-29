<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkedInScheduledPost extends Model
{
    protected $table = 'linkedin_scheduled_posts';

    public const PENDING = 'pending';

    public const PUBLISHED = 'published';

    public const FAILED = 'failed';

    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'commentary',
        'article_id',
        'status',
        'publish_at',
        'payload',
        'attempts',
        'last_error',
        'linkedin_post_id',
        'published_at',
        'cancelled_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'publish_at'   => 'datetime',
        'published_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function scopePending($query)
    {
        return $query->where('status', self::PENDING);
    }

    public function scopeDue($query)
    {
        return $query->pending()->where('publish_at', '<=', now());
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }
}
