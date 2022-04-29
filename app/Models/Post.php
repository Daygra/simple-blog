<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    public const IMAGE_STORAGE_PATH = 'postImages';

    protected $fillable = ['title','slug','preview_text','detail_text','img_path','user_id'];

    public function comments():HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
