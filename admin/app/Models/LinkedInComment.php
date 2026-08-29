<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkedInComment extends Model
{
    protected $table = 'linkedin_comments';

    protected $fillable = [
        'urn',
        'post_urn',
        'linkedin_post_id',
        'parent_comment_urn',
        'text',
        'edited_at',
        'deleted_at',
    ];

    protected $casts = [
        'edited_at'  => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function scopeLive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }

    public function isReply(): bool
    {
        return $this->parent_comment_urn !== null;
    }
}
