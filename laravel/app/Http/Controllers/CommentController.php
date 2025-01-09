<?php

namespace App\Http\Controllers;
use App\Models\Comment; // Assurez-vous que ce chemin est correct

use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index($articleId)
    {
        $comments = Comment::where('article_id', $articleId)
            ->whereNull('parent_id') // Seulement les commentaires principaux
            ->with('replies.user') // Charger les réponses avec leurs utilisateurs
            ->orderBy('created_at', 'desc') // Trier par date
            ->get();
    
        return response()->json($comments);
    }
    

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'content' => 'required|string|min:1',
            'parent_id' => 'nullable|exists:comments,id',
        ]);
    
        $validatedData['user_id'] = auth()->id();
    
        $comment = Comment::create($validatedData);
    
        return response()->json($comment, 201);
    }
    public function getComments($articleId) {
        $comments = Comment::where('article_id', $articleId)->get();
        return response()->json($comments);
    }
    
    
}    