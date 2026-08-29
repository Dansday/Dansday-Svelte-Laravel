<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkedInPost extends Model
{
    protected $table = 'linkedin_posts';

    protected $fillable = [
        'urn',
        'article_id',
        'media_type',
        'visibility',
        'commentary',
        'posted_at',
        'edited_at',
        'deleted_at',
    ];

    protected $casts = [
        'posted_at'  => 'datetime',
        'edited_at'  => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function scopeLive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function url(): string
    {
        return 'https://www.linkedin.com/feed/update/'.$this->urn.'/';
    }

    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }
}
