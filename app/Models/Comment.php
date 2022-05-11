<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    public const MODERATED = 1;
    public const BLOCKED = 0;
    protected $fillable = ['name', 'email', 'post_id', 'is_moderated', 'user_id'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeModeratedComments(Builder $query): Builder
    {
        return $query->where('is_moderated', Comment::MODERATED);
    }

    public function scopeBlockedComments(Builder $query): Builder
    {
        return $query->where('is_moderated', Comment::BLOCKED);
    }


}
