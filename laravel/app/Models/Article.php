<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category',
        'image_path',
        'author',         // Nouvelle colonne
        'published_at', 
        
    ];
}
