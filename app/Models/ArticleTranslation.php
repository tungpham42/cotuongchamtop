<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleTranslation extends Model
{
    protected $fillable = ['article_id', 'locale', 'title', 'slug', 'content'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
