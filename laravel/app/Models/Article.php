<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'author',
        'image_path',
        'likes',
        'category',
        'comments_count',
        'published_at',
        'user_id', // Champ lié à l'utilisateur
    ];

    // Relation : un article appartient à un utilisateur
    public function user() {
        return $this->belongsTo(User::class);
    }
}
