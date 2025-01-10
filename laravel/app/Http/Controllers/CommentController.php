<?php

namespace App\Http\Controllers;
use App\Models\Comment; // Assurez-vous que ce chemin est correct

use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index($articleId)
    {
        \Log::info('Headers reçus :', request()->headers->all());
        \Log::info('Utilisateur authentifié :', ['user' => auth()->user()]);
    
        $comments = Comment::where('article_id', $articleId)
            ->whereNull('parent_id')
            ->with('replies.user')
            ->orderBy('created_at', 'desc')
            ->get();
    
        return response()->json($comments);
    }
    

    

    public function store(Request $request)
{
    try {
        \Log::info('Received data:', $request->all()); // Log les données reçues
        $validatedData = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'content' => 'required|string|min:1',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $validatedData['user_id'] = auth()->id();

        $comment = Comment::create($validatedData);

        return response()->json($comment, 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation errors:', $e->errors()); // Log les erreurs de validation
        return response()->json([
            'errors' => $e->errors(),
        ], 422);
    }
}


    // public function getComments($articleId) {
    //     $comments = Comment::where('article_id', $articleId)->get();
    //     return response()->json($comments);
    // }
    
    
}    